<?php

namespace App\Models;

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

        return $tokens
            ->flatMap(function (GocardlessToken $token) {
                return collect($token->listRequisitions())->map(fn (array $requisition) => [
                    'id' => $requisition['id'],
                    'gocardless_token_id' => $token->id,
                    'status' => $requisition['status'] ?? null,
                    'institution_id' => $requisition['institution_id'] ?? null,
                    'created' => $requisition['created'] ?? null,
                    'accounts_count' => $requisition['accounts_count'] ?? 0,
                    'active' => $requisition['active'] ?? false,
                ]);
            })
            ->toArray();
    }

    public function gocardlessToken(): BelongsTo
    {
        return $this->belongsTo(GocardlessToken::class);
    }
}
