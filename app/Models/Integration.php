<?php

namespace App\Models;

use Database\Factories\IntegrationFactory;
use App\Exceptions\GocardlessException;
use App\Models\Scopes\OwnerScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasVersion4Uuids as HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class Integration extends Model
{
    /** @use HasFactory<IntegrationFactory> */
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'name'
    ];

    protected $casts = [
        'accounts' => 'collection',
        'expires_at' => 'datetime',
        'last_synced_at' => 'datetime',
        'can_auto_sync' => 'boolean'
     ];

    public function all_transactions()
    {
        return $this->hasMany(Transaction::class)->withTrashed();
    }

    public function gocardless_token(): BelongsTo {
        return $this->belongsTo(GocardlessToken::class);
    }

    protected static function boot()
    {
        parent::boot();
        static::deleting(function (Integration $integration) {
            $integration->deleteRequisition();
        });
    }

    public static function listBanks(): Collection|null
    {
        return Cache::remember('banks', 86400, function () {
            $base = config('gocardless.base_url');
            $access_token = GocardlessToken::first()->getAccessToken(); // Random token is enough
            $response = Http::withHeader('Authorization', "Bearer $access_token")
                ->get("$base/institutions/");
            throw_if($response->failed(), new GocardlessException($response));

            return collect($response->json())->mapWithKeys(fn($bank) => [
                $bank['id'] => $bank['name'] . ' (' . collect($bank['countries'])->implode(', ') . ')'
            ]);
        });
    }

    public function canAccept(): Attribute
    {
        return Attribute::get(fn () => is_null($this->expires_at));
    }

    public function expired(): Attribute
    {
        return Attribute::get(fn () => !is_null($this->expires_at) && Carbon::now()->gt($this->expires_at));
    }

    public function deleteRequisition()
    {
        $base = config('gocardless.base_url');
        $access_token = $this->gocardless_token->getAccessToken();
        $requisition_id = $this->requisition_id;
        $response = Http::withHeader('Authorization', "Bearer $access_token")
            ->delete("$base/requisitions/$requisition_id/");
        throw_if(!$response->notFound() && $response->failed(), new GocardlessException($response));
    }

    public function fillBasics($institution_id)
    {
        $base = config('gocardless.base_url');
        $access_token = $this->gocardless_token->getAccessToken();
        $response = Http::withHeader('Authorization', "Bearer $access_token")
            ->get("$base/institutions/$institution_id");
        throw_if($response->failed(), new GocardlessException($response));
        $this->institution_id = $institution_id;
        $this->institution_name = $response->json('name');
        $this->institution_logo = $response->json('logo');
    }

    public function createRequisition()
    {
        $base = config('gocardless.base_url');
        $institution_id = $this->institution_id;
        $access_token = $this->gocardless_token->getAccessToken();

        // Get institution
        $response = Http::withHeader('Authorization', "Bearer $access_token")
            ->get("$base/institutions/$institution_id");
        throw_if($response->failed(), new GocardlessException($response));
        $max_access_valid_for_days = $response->json('max_access_valid_for_days');
        $transaction_total_days = $response->json('transaction_total_days');
        // Create custom end user agreement
        $response = Http::withHeader('Authorization', "Bearer $access_token")
            ->post("$base/agreements/enduser/", [
                'institution_id' => $institution_id,
                'max_historical_days' => $transaction_total_days,
                'access_valid_for_days' => $max_access_valid_for_days,
                'access_scope' => [
                    'transactions',
                    'details'
                ]
            ]);
        throw_if($response->failed(), new GocardlessException($response));
        $agreement_id = $response->json('id');
        // Create requisition
        $response = Http::withHeader('Authorization', "Bearer $access_token")
            ->post("$base/requisitions/", [
                'institution_id' => $institution_id,
                'redirect' => route('gocardless.callback'),
                'agreement' => $agreement_id
            ]);
        throw_if($response->failed(), new GocardlessException($response));
        $this->requisition_id = $response->json('id');
        $this->link = $response->json('link');
        $this->expires_at = null;
    }

    public function fillExtra()
    {
        $base = config('gocardless.base_url');
        $access_token = $this->gocardless_token->getAccessToken();
        $requisition_id = $this->requisition_id;
        // Get requisition
        $response = Http::withHeader('Authorization', "Bearer $access_token")
            ->get("$base/requisitions/$requisition_id/");
        throw_if($response->failed(), new GocardlessException($response));
        $data = $response->json();
        $agreement_id = $data['agreement'];
        // Get agreement
        $response = Http::withHeader('Authorization', "Bearer $access_token")
            ->get("$base/agreements/enduser/$agreement_id/");
        throw_if($response->failed(), new GocardlessException($response));
        $this->accounts = $data['accounts'];
        $this->expires_at = Carbon::now()->addDays($response->json('access_valid_for_days'));
    }

    public function getTransactions($start = null): array
    {
        $base = config('gocardless.base_url');
        $query = [];
        if (!is_null($start)) {
            $query['date_from'] = $start->toDateString();
        }
        $access_token = $this->gocardless_token->getAccessToken();
        return collect($this->accounts)->reduce(function ($acc, $account) use ($access_token, $query, $base) {
            $response = Http::withHeader('Authorization', "Bearer $access_token")
                ->get("$base/accounts/$account/transactions", $query);
            throw_if($response->failed(), new GocardlessException($response));
            return [
                'booked' => $acc['booked']->merge(collect($response->json('transactions.booked'))),
                'pending' => $acc['pending']->merge(collect($response->json('transactions.pending')))
            ];
        }, ['booked' => collect(), 'pending' => collect()]);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
