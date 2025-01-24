<?php

namespace App\Models;

use App\Enums\Direction;
use App\Models\Scopes\OwnerScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
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
        'direction',
        'user_id'
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

    public function scopeIncome(Builder $query)
    {
        return $query->where('direction', Direction::INCOME);
    }

    public function scopeExpense(Builder $query)
    {
        return $query->where('direction', Direction::EXPENSE);
    }

    public function formattedValues(): Attribute
    {
        return Attribute::get(fn() => $this->transactions()->get()
            ->groupBy('currency_id')->map(fn ($transactions) => $transactions[0]->currency->format($transactions->sum('value'))));
    }
}
