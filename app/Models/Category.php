<?php

namespace App\Models;

use App\Enums\Direction;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    /** @use HasFactory<\Database\Factories\CategoryFactory> */
    use HasFactory;
    use HasUuids;

    protected $fillable = [
        'name',
        'direction'
    ];

    protected $casts = [
        'direction' => Direction::class
    ];

    public $timestamps = false;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    public static function predict(array $data)
    {
        return null;
    }
}
