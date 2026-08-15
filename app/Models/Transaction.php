<?php

namespace App\Models;

use App\Casts\DateTimeCast;
use App\Enums\Direction;
use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasVersion4Uuids as HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Transaction extends Model
{
    /** @use HasFactory<TransactionFactory> */
    use HasFactory;

    use HasUuids;
    use SoftDeletes;

    protected $fillable = [
        'description',
        'direction',
        'value',
        'date',
        'merchant',
        'currency_id',
        'category_id',
        'tags',
        'deleted_at',
    ];

    protected $casts = [
        'date' => DateTimeCast::class,
        'open_banking_transaction' => 'array',
        'tags' => 'array',
        'direction' => Direction::class,
        'is_pending' => 'boolean',
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

    public function integration(): BelongsTo
    {
        return $this->belongsTo(Integration::class);
    }

    public function mergedTransactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'merge_id', 'merge_id')
            ->onlyTrashed()
            ->whereNotNull('transactions.merge_id')
            ->with(['currency', 'integration']);
    }

    public function formattedValue(): Attribute
    {
        return Attribute::get(function () {
            $value = $this->currency->format($this->value);
            if ($this->direction === Direction::EXPENSE || $this->direction === Direction::INTERNAL_FROM) {
                return "- $value";
            } else {
                return $value;
            }
        });
    }
}
