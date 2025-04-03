<?php

namespace App\Models;

use App\Exceptions\GocardlessException;
use App\Models\Scopes\OwnerScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class Integration extends Model
{
    /** @use HasFactory<\Database\Factories\IntegrationFactory> */
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
            $access_token = self::getAccessToken();
            $response = Http::withHeader('Authorization', "Bearer $access_token")
                ->get('https://bankaccountdata.gocardless.com/api/v2/institutions/');
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

    /**
     * @throws ConnectionException
     */
    public static function getAccessToken(): string|null
    {
        $access_token = Cache::get('gocardless_access_token', null);
        if (is_null($access_token)) {
            $refresh_token = Cache::get('gocardless_refresh_token', null);
            if (is_null($refresh_token)) {
                // Request new token
                $response = Http::post('https://bankaccountdata.gocardless.com/api/v2/token/new/', [
                    'secret_id' => config('gocardless.secret_id'),
                    'secret_key' => config('gocardless.secret_key')
                ]);
                if ($response->successful()) {
                    Cache::set('gocardless_access_token', $response->json('access'), $response->json('access_expires') - 10);
                    Cache::set('gocardless_refresh_token', $response->json('refresh'), $response->json('refresh_expires') - 10);
                    return $response->json('access');
                } else {
                    Cache::forget('gocardless_access_token');
                    Cache::forget('gocardless_refresh_token');
                    throw new GocardlessException($response);
                }
            } else {
                // Refresh token
                $response = Http::post('https://bankaccountdata.gocardless.com/api/v2/token/refresh/', [
                    'secret_id' => config('gocardless.secret_id'),
                    'secret_key' => config('gocardless.secret_key')
                ]);
                if ($response->successful()) {
                    Cache::set('gocardless_access_token', $response->json('access'), $response->json('access_expires') - 10);
                    return $response->json('access');
                } else {
                    Cache::forget('gocardless_access_token');
                    Cache::forget('gocardless_refresh_token');
                    throw new GocardlessException($response);
                }
            }
        } else {
            return $access_token;
        }
    }

    public function deleteRequisition()
    {
        $access_token = self::getAccessToken();
        $requisition_id = $this->requisition_id;
        // Create requisition
        $response = Http::withHeader('Authorization', "Bearer $access_token")
            ->delete("https://bankaccountdata.gocardless.com/api/v2/requisitions/$requisition_id/");
        throw_if(!$response->notFound() && $response->failed(), new GocardlessException($response));
    }

    public function fillBasics($institution_id)
    {
        $access_token = self::getAccessToken();
        $response = Http::withHeader('Authorization', "Bearer $access_token")
            ->get("https://bankaccountdata.gocardless.com/api/v2/institutions/$institution_id");
        throw_if($response->failed(), new GocardlessException($response));
        $this->institution_id = $institution_id;
        $this->institution_name = $response->json('name');
        $this->institution_logo = $response->json('logo');
    }

    public function createRequisition()
    {
        $institution_id = $this->institution_id;
        $access_token = self::getAccessToken();

        // Get institution
        $response = Http::withHeader('Authorization', "Bearer $access_token")
            ->get("https://bankaccountdata.gocardless.com/api/v2/institutions/$institution_id");
        throw_if($response->failed(), new GocardlessException($response));
        $max_access_valid_for_days = $response->json('max_access_valid_for_days');
        $transaction_total_days = $response->json('transaction_total_days');
        // Create custom end user agreement
        $response = Http::withHeader('Authorization', "Bearer $access_token")
            ->post('https://bankaccountdata.gocardless.com/api/v2/agreements/enduser/', [
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
            ->post('https://bankaccountdata.gocardless.com/api/v2/requisitions/', [
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
        $access_token = self::getAccessToken();
        $requisition_id = $this->requisition_id;
        // Get requisition
        $response = Http::withHeader('Authorization', "Bearer $access_token")
            ->get("https://bankaccountdata.gocardless.com/api/v2/requisitions/$requisition_id/");
        throw_if($response->failed(), new GocardlessException($response));
        $data = $response->json();
        $agreement_id = $data['agreement'];
        // Get agreement
        $response = Http::withHeader('Authorization', "Bearer $access_token")
            ->get("https://bankaccountdata.gocardless.com/api/v2/agreements/enduser/$agreement_id/");
        throw_if($response->failed(), new GocardlessException($response));
        $this->accounts = $data['accounts'];
        $this->expires_at = Carbon::now()->addDays($response->json('access_valid_for_days'));
    }

    public function getTransactions($start = null): Collection
    {
        $query = [];
        if (!is_null($start)) {
            $query['date_from'] = $start->toDateString();
        }
        $access_token = self::getAccessToken();
        $transactions = collect($this->accounts)->flatMap(function ($account) use ($access_token, $query) {
            $response = Http::withHeader('Authorization', "Bearer $access_token")
                ->get("https://bankaccountdata.gocardless.com/api/v2/accounts/$account/transactions",$query);
            throw_if($response->failed(), new GocardlessException($response));
            return $response->json('transactions.booked');
        });
        return $transactions;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

}
