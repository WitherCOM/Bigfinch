<?php

namespace App\Models;

use App\Enums\ActionType;
use App\Enums\Direction;
use App\Models\Scopes\OwnerScope;
use Carbon\Carbon;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

#[ScopedBy([OwnerScope::class])]
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

    public function check($transaction): bool
    {
        $isDescription = is_null($this->description) || Str::of($transaction->description)->contains($this->description, true);
        $isMerchant = is_null($this->merchant) || Str::of($transaction->merchant?->name)->contains($this->merchant, true);
        $isDirection = is_null($this->direction) || $transaction->direction == $this->direction;
        $isMinValue = is_null($this->min_value) || $transaction->value >= $this->min_value;
        $isMaxValue = is_null($this->max_value) || $transaction->value <= $this->max_value;
        return $isDescription && $isDirection && $isMerchant && $isMinValue && $isMaxValue;
    }

    public function priority(): Attribute
    {
        return Attribute::get(fn() => collect($this->attributesToArray())->filter(fn($value) => !is_null($value))->count());
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function applyFilter(Builder $query)
    {
        return $query->orWhere(function (Builder $query) {
            return $query
                ->when(!is_null($this->description), fn (Builder $query) => $query->whereLike('description',"%$this->description%"))
                ->when(!is_null($this->merchant), fn (Builder $query) => $query->whereRelation('merchant', fn (Builder $query) => $query->whereLike('name',"%$this->merchant%")))
                ->when(!is_null($this->direction), fn (Builder $query) => $query->where('direction',$this->direction->value))
                ->when(!is_null($this->min_value), fn (Builder $query) => $query->where('value','>=',$this->min_value))
                ->when(!is_null($this->max_value), fn (Builder $query) => $query->where('value',$this->max_value));
        });
    }

    public static function exclude()
    {
        $query = Transaction::query();
        $filters = Filter::where('action',ActionType::EXCLUDE_TRANSACTION->value)->get();
        if ($filters->count() > 0)
        {
            foreach ($filters as $filter)
            {
                $query = $filter->applyFilter($query);
            }
            $query->delete();
        }
    }

    public static function category()
    {
        $filters = Filter::where('action',ActionType::CREATE_CATEGORY->value)->get();
        foreach( Transaction::query()->whereNull('category_id')->get() as $transaction )
        {
            $category_id = $filters->filter(fn($filter) => $filter->check($transaction))->sortByDesc('priority')->first()?->action_parameter;
            if (is_null($category_id))
            {
                if ($transaction->direction === Direction::INCOME)
                {
                    $category_id = $transaction->merchant?->income_category_id;
                }
                else if ($transaction->direction === Direction::EXPENSE)
                {
                    $category_id = $transaction->merchant?->expense_category_id;
                }
            }
            if (!is_null($category_id))
            {
                $transaction->update([
                    'category_id' => $category_id
                ]);
            }
        }
    }
}
