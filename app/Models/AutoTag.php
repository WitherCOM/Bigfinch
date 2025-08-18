<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class AutoTag extends Model
{
    //
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'from',
        'to',
        'tag',
        'user_id',
    ];

    protected $casts = [
        'from' => 'datetime',
        'to' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive(Builder $query): void {
        $now = Carbon::now();
        $query
            ->where('from','<=', $now)
            ->where('to','>=', $now);
    }
}
