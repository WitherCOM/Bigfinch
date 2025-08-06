<?php

namespace App\Models;

use App\Casts\FlagArray;
use App\Engine\OpenBankingEngine;
use App\Enums\ActionType;
use App\Enums\CurrencyPosition;
use App\Enums\Direction;
use App\Models\Scopes\OwnerScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    /** @use HasFactory<\Database\Factories\TransactionFactory> */
    use HasFactory;
    use HasUuids;
    use SoftDeletes;

    protected $fillable = [
        'description',
        'direction',
        'flags',
        'value',
        'date',
        'merchant',
        'currency_id',
        'category_id',
        'tags',
        'deleted_at'
    ];

    protected $casts = [
        'date' => 'datetime',
        'open_banking_transaction' => 'array',
        'flags' => FlagArray::class,
        'tags' => 'array',
        'direction' => Direction::class
    ];

    protected $with = ['category'];

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function formattedValue(): Attribute
    {
        return Attribute::get(function () {
            $value = $this->currency->format($this->value);
            if ($this->direction === Direction::EXPENSE || $this->direction === Direction::INTERNAL_FROM)
            {
                return "- $value";
            }
            else
            {
                return $value;
            }
        });
    }

}
