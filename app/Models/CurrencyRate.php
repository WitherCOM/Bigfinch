<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class CurrencyRate extends Model
{
    use HasUuids;

    protected $fillable = [
        'rate_to_huf'
    ];

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }
}
