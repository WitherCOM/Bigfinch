<?php

namespace App\Models;

use App\Enums\CurrencyPosition;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    /** @use HasFactory<\Database\Factories\CurrencyFactory> */
    use HasFactory;
    use HasUuids;

    public $timestamps = false;
    protected $fillable = [
        'name',
        'iso_format',
        'position',
        'symbol',
        'rate_to_huf',
    ];

    protected $casts = [
        'position' => CurrencyPosition::class
    ];
}
