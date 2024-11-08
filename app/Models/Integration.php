<?php

namespace App\Models;

use Carbon\Carbon;
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
        'expires_at' => 'datetime'
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
        return Cache::remember('banks',86400, function () {
            $access_token = self::getAccessToken();
            $response = Http::withHeader('Authorization', "Bearer $access_token")
                ->get('https://bankaccountdata.gocardless.com/api/v2/institutions/');
            throw_if($response->failed(), new \Exception("Gocard error!"));

            return collect($response->json())->mapWithKeys(fn ($bank) => [
                $bank['id'] => $bank['name'] . ' (' . collect($bank['countries'])->implode(', ') . ')'
            ]);
        });
    }

    /**
     * @throws ConnectionException
     */
    public static function getAccessToken(): string|null
    {
        $access_token = Cache::get('gocardless_access_token',null);
        if (is_null($access_token))
        {
            $refresh_token = Cache::get('gocardless_refresh_token',null);
            if (is_null($refresh_token))
            {
                // Request new token
                $response = Http::post('https://bankaccountdata.gocardless.com/api/v2/token/new/',[
                    'secret_id' => config('gocardless.secret_id'),
                    'secret_key' => config('gocardless.secret_key')
                ]);
                if ($response->successful())
                {
                    Cache::set('gocardless_access_token',$response->json('access'), $response->json('access_expires'));
                    Cache::set('gocardless_refresh_token',$response->json('refresh'), $response->json('refresh_expires'));
                    return $response->json('access');
                }
                else
                {
                    Cache::forget('gocardless_access_token');
                    Cache::forget('gocardless_refresh_token');
                    return null;
                }
            }
            else
            {
                // Refresh token
                $response = Http::post('https://bankaccountdata.gocardless.com/api/v2/token/refresh/',[
                    'secret_id' => config('gocardless.secret_id'),
                    'secret_key' => config('gocardless.secret_key')
                ]);
                if ($response->successful())
                {
                    Cache::set('gocardless_access_token',$response->json('access'), $response->json('access_expires'));
                    return $response->json('access');
                }
                else
                {
                    Cache::forget('gocardless_access_token');
                    Cache::forget('gocardless_refresh_token');
                    return null;
                }
            }
        }
        else
        {
            return $access_token;
        }
    }

    public function getRequisition(): array|null
    {
        $access_token = self::getAccessToken();
        $requisition_id = $this->requisition_id;
        // Create requisition
        $response = Http::withHeader('Authorization', "Bearer $access_token")
            ->get("https://bankaccountdata.gocardless.com/api/v2/requisitions/$requisition_id/");
        throw_if($response->failed(), new \Exception("Gocard error!"));

        return $response->json();
    }

    public function deleteRequisition()
    {
        $access_token = self::getAccessToken();
        $requisition_id = $this->requisition_id;
        // Create requisition
        $response = Http::withHeader('Authorization', "Bearer $access_token")
            ->delete("https://bankaccountdata.gocardless.com/api/v2/requisitions/$requisition_id/");
        throw_if($response->failed(), new \Exception("Gocard error!"));
    }


    public function fillBasics($institution_id)
    {
        $access_token = self::getAccessToken();

        // Get institution
        $response = Http::withHeader('Authorization', "Bearer $access_token")
            ->get("https://bankaccountdata.gocardless.com/api/v2/institutions/$institution_id");
        throw_if($response->failed(), new \Exception("Gocard error!"));
        $this->institution_name = $response->json('name');
        $this->institution_logo = $response->json('logo');

        // Create requisition
        $response = Http::withHeader('Authorization', "Bearer $access_token")
            ->post('https://bankaccountdata.gocardless.com/api/v2/requisitions/', [
                'institution_id' => $institution_id,
                'redirect' => route('gocardless.callback')
            ]);
        throw_if($response->failed(), new \Exception("Gocard error!"));
        $this->requisition_id = $response->json('id');
        $this->link = $response->json('link');
    }

    public function fillExtra()
    {
        $data = $this->getRequisition();
        $this->accounts = $data['accounts'];
        $this->expires_at = Carbon::now()->addDays(90);
    }

    public function getTransactions(): Collection
    {
        $access_token = self::getAccessToken();

        $transactions = collect($this->accounts)->flatMap(function ($account) use ($access_token) {
            $response = Http::withHeader('Authorization', "Bearer $access_token")
                ->get("https://bankaccountdata.gocardless.com/api/v2/accounts/$account/transactions");
            throw_if($response->failed(), new \Exception("Gocard error!"));
            return $response->json('transactions.booked');
        });
        return $transactions;
    }

}
