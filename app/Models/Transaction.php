<?php

namespace App\Models;

use App\Enums\CurrencyPosition;
use App\Enums\Direction;
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
        'value',
        'date',
        'merchant_id',
        'currency_id',
        'category_id'
    ];

    public $timestamps = false;

    protected $casts = [
        'date' => 'datetime',
        'open_banking_transaction' => 'array',
        'direction' => Direction::class
    ];

    protected static function booted()
    {
        parent::booted();
        parent::deleting(function (Transaction $transaction) {
            if (is_null($transaction->integration_id))
            {
                $transaction->forceDeleting = true;
            }
        });
    }

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

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
            $currency = $this->currency;
            $value = $this->value;
            if ($currency->position === CurrencyPosition::PREFIX)
            {
                return "$currency->symbol $value";
            }
            else
            {
                return "$value $currency->symbol";
            }
        });
    }
}
