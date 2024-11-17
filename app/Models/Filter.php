<?php

namespace App\Models;

use App\Enums\ActionType;
use App\Enums\Direction;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Filter extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $fillable = [
        'min_value',
        'max_value',
        'from_date',
        'to_date',
        'description',
        'direction',
        'merchant',

        'action',
        'action_parameter',
        'user_id'
    ];

    protected $casts = [
        'direction' => Direction::class,
        'action' => ActionType::class
    ];

    public function check(array $transaction): bool
    {
        $isDescription = is_null($this->description) || Str::of($transaction['description'])->contains($this->description);
        $isMerchant = is_null($this->merchant) || Str::of($transaction['merchant']['name'])->contains($this->merchant);
        $isDirection = is_null($this->direction) || $transaction['direction'] == $this->direction->value;
        $isMinValue = is_null($this->min_value) || $transaction['value'] >= $this->min_value;
        $isMaxValue = is_null($this->max_value) || $transaction['value'] <= $this->max_value;
        return $isDescription && $isDirection && $isMerchant && $isMinValue && $isMaxValue;
    }

    public function filterQuery(Builder $query): Builder
    {
        return $query->orWhere(function (Builder $query) {
            return $query
                ->when(!is_null($this->description), function (Builder $query) {
                    $query->whereLike('description', "%$this->description%");
                })
                ->when(!is_null($this->merchant), function (Builder $query) {
                    $query->whereLike('merchant.name',"%$this->merchant%");
                })
                ->when(!is_null($this->direction), function (Builder $query) {
                    $query->where('direction', $this->direction->value);
                })
                ->when(!is_null($this->min_value), function (Builder $query) {
                    $query->where('value', '>=', $this->min_value);
                })
                ->when(!is_null($this->max_value), function (Builder $query) {
                    $query->where('value', '<=', $this->max_value);
                });
        });
    }

    public function priority(): Attribute
    {
        return Attribute::get(fn() => collect($this->attributesToArray())->filter(fn($value) => !is_null($value))->count());
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
