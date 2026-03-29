<?php

namespace App\Models\Gocardless;

use App\Exceptions\GocardlessException;
use App\Models\Integration;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class GocardlessToken extends Model
{
    use HasUuids;
    use HasFactory;

    protected $fillable = [
        'secret_id',
        'secret_key',
        'max_connections',
        'access_token',
        'access_token_expires_at',
        'refresh_token_expires_at',
        'refresh_token',
    ];

    protected $casts = [
        'access_token_expires_at' => 'datetime',
        'refresh_token_expires_at' => 'datetime',
    ];

    protected $hidden = [
        'access_token',
        'refresh_token',
    ];

    public function integrations(): HasMany
    {
        return $this->hasMany(Integration::class);
    }

    public function activeIntegrationsCount(): Attribute
    {
        return Attribute::get(fn () => $this->integrations
            ->filter(fn (Integration $i) => $i->expires_at !== null && $i->expires_at->isFuture())->count());
    }

    public static function available(): static
    {
        return static::all()
            ->first(fn (self $token) => $token->active_integrations->count() < $token->max_connections)
            ?? throw new GocardlessException('No GoCardless tokens with available connections.');
    }

    /**
     * @return RequisitionDto[]
     */
    public function getRequisitions(): array
    {
        $integrations = Integration::withoutGlobalScopes()
            ->with('user')
            ->whereNotNull('requisition_id')
            ->get()
            ->keyBy('requisition_id');

        return collect($this->listRequisitions())->map(function (array $requisition) use ($integrations) {
            $integration = $integrations->get($requisition['id']);

            return new RequisitionDto(
                id: $requisition['id'],
                gocardlessTokenId: $this->id,
                status: $requisition['status'] ?? null,
                institutionId: $requisition['institution_id'] ?? null,
                created: $requisition['created'] ?? null,
                accountsCount: $requisition['accounts_count'] ?? 0,
                active: $requisition['active'] ?? false,
                integrationName: $integration?->name,
                userName: $integration?->user?->name,
            );
        })->all();
    }

    /**
     * @throws ConnectionException
     */
    public function getAccessToken(): string|null
    {
        $base = config('gocardless.base_url');

        if ($this->access_token && $this->access_token_expires_at?->isFuture()) {
            return $this->access_token;
        }

        if ($this->refresh_token && $this->refresh_token_expires_at?->isFuture()) {
            $response = Http::post("$base/token/refresh/", [
                'refresh' => $this->refresh_token,
            ]);

            if ($response->successful()) {
                $this->update([
                    'access_token' => $response->json('access'),
                    'access_token_expires_at' => now()->addSeconds($response->json('access_expires') - 10),
                ]);
                return $response->json('access');
            }

            $this->update([
                'access_token' => null,
                'refresh_token' => null,
                'access_token_expires_at' => null,
                'refresh_token_expires_at' => null,
            ]);
            throw new GocardlessException($response);
        }

        $response = Http::post("$base/token/new/", [
            'secret_id' => $this->secret_id,
            'secret_key' => $this->secret_key,
        ]);

        if ($response->successful()) {
            $this->update([
                'access_token' => $response->json('access'),
                'refresh_token' => $response->json('refresh'),
                'access_token_expires_at' => now()->addSeconds($response->json('access_expires') - 10),
                'refresh_token_expires_at' => now()->addSeconds($response->json('refresh_expires') - 10),
            ]);
            return $response->json('access');
        }

        $this->update([
            'access_token' => null,
            'refresh_token' => null,
            'access_token_expires_at' => null,
            'refresh_token_expires_at' => null,
        ]);
        throw new GocardlessException($response);
    }

    public function listAgreements(): array
    {
        $base = config('gocardless.base_url');
        $accessToken = $this->getAccessToken();

        $response = Http::withHeader('Authorization', "Bearer $accessToken")
            ->get("$base/agreements/enduser/");

        throw_if($response->failed(), new GocardlessException($response));

        return $response->json('results', []);
    }

    public function listRequisitions(): array
    {
        $base = config('gocardless.base_url');
        $accessToken = $this->getAccessToken();

        $response = Http::withHeader('Authorization', "Bearer $accessToken")
            ->get("$base/requisitions/");

        throw_if($response->failed(), new GocardlessException($response));

        $agreements = collect($this->listAgreements())->keyBy('id');


        $result = $response->json('results', []);
        foreach ($result as &$requisition) {
            $requisition['accounts_count'] = count($requisition['accounts'] ?? []);
            $requisition['active'] = $this->isRequisitionActive($requisition, $agreements);
        }
        return $result;
    }

    protected function isRequisitionActive(array $requisition, $agreements): bool
    {
        if (($requisition['status'] ?? '') !== 'LN') {
            return false;
        }

        if (str_starts_with($requisition['institution_id'] ?? '', 'SANDBOXFINANCE_SFIN000')) {
            return false;
        }

        $agreement = $agreements->get($requisition['agreement'] ?? '');
        if (!$agreement || empty($agreement['accepted'])) {
            return false;
        }

        $accepted = Carbon::parse($agreement['accepted']);
        $expires = $accepted->copy()->addDays($agreement['access_valid_for_days'] ?? 0);
        $monthStart = Carbon::now('UTC')->startOfMonth();
        if ($expires->isBefore($monthStart)) {
            return false;
        }

        return true;
    }

    public function deleteRequisition(string $requisitionId): void
    {
        $base = config('gocardless.base_url');
        $accessToken = $this->getAccessToken();

        $response = Http::withHeader('Authorization', "Bearer $accessToken")
            ->delete("$base/requisitions/$requisitionId/");

        throw_if(!$response->notFound() && $response->failed(), new GocardlessException($response));
    }
}
