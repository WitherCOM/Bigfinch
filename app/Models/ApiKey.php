<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiKey extends Model
{
    /** @use HasFactory<\Database\Factories\ApiKeyFactory> */
    use HasFactory,HasUuids;

    function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }
}
