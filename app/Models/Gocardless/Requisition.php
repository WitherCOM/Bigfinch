<?php

namespace App\Models\Gocardless;

use App\Models\Integration;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Sushi\Sushi;

class Requisition extends Model
{
    use Sushi;

    public $incrementing = false;

    protected $keyType = 'string';

    protected static ?GocardlessToken $token = null;

    protected $schema = [
        'id' => 'string',
        'gocardless_token_id' => 'string',
        'status' => 'string',
        'institution_id' => 'string',
        'created' => 'datetime',
        'accounts_count' => 'integer',
        'active' => 'boolean',
        'integration_name' => 'string',
        'user_name' => 'string',
    ];

    public static function forToken(GocardlessToken $token): void
    {
        static::$token = $token;
    }

    public function getRows(): array
    {
        $tokens = static::$token
            ? collect([static::$token])
            : GocardlessToken::all();

        $integrations = Integration::withoutGlobalScopes()
            ->with('user')
            ->whereNotNull('requisition_id')
            ->get()
            ->keyBy('requisition_id');

        return $tokens
            ->flatMap(function (GocardlessToken $token) use ($integrations) {
                return collect($token->listRequisitions())->map(function (array $requisition) use ($token, $integrations) {
                    $integration = $integrations->get($requisition['id']);

                    return [
                        'id' => $requisition['id'],
                        'gocardless_token_id' => $token->id,
                        'status' => $requisition['status'] ?? null,
                        'institution_id' => $requisition['institution_id'] ?? null,
                        'created' => $requisition['created'] ?? null,
                        'accounts_count' => $requisition['accounts_count'] ?? 0,
                        'active' => $requisition['active'] ?? false,
                        'integration_name' => $integration?->name,
                        'user_name' => $integration?->user?->name,
                    ];
                });
            })
            ->toArray();
    }

    public function gocardlessToken(): BelongsTo
    {
        return $this->belongsTo(GocardlessToken::class);
    }
}
