<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Merchant extends Model
{
    /** @use HasFactory<\Database\Factories\MerchantFactory> */
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'name',
        'search_keys'
    ];

    protected $casts = [
        'search_keys' => 'array'
    ];

    public $timestamps = false;

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
}
