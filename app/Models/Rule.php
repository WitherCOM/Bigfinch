<?php

namespace App\Models;

use App\Enums\Direction;
use App\Enums\RuleType;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Rule extends Model
{
    use HasUuids;

    protected $fillable = [
        'type',
        'description_lookup',
        'merchant_id_lookup',
        'category_id_lookup',
        'currency_id_lookup',
        'direction_lookup',
        'min_value_lookup',
        'max_value_lookup',
        'target_id'
    ];

    protected $casts = [
        'direction_lookup' => Direction::class
    ];

    public function categoryFilter($data): bool
    {
        return (is_null($this->description_lookup) || Str::of($data['description'])->contains($this->description_lookup)) &&
            (is_null($this->merchant_id_lookup) || $data['merchant_id'] === $this->merchant_id_lookup) &&
            (is_null($this->category_id_lookup) || $data['category_id'] === $this->category_id_lookup) &&
            (is_null($this->currency_id_lookup) || $data['currency_id'] === $this->currency_id_lookup) &&
            (is_null($this->direction_lookup) || $data['direction'] === $this->direction_lookup) &&
            (is_null($this->min_value_lookup) || $data['value'] >= $this->min_value_lookup) &&
            (is_null($this->max_value_lookup) || $data['value'] <= $this->max_value_lookup);
    }

    public function excludeFilter(Builder $query): Builder
    {
        return $query->whereNot(function (Builder $query) {
            return $query
                ->when(!is_null($this->description_lookup), function (Builder $query) {
                    $query->whereLike('description', "%$this->description_lookup%");
                })
                ->when(!is_null($this->merchant_id_lookup), function (Builder $query) {
                    $query->where('merchant_id', $this->merchant_id_lookup);
                })
                ->when(!is_null($this->category_id_lookup), function (Builder $query) {
                    $query->where('category_id', $this->category_id_lookup);
                })
                ->when(!is_null($this->currency_id_lookup), function (Builder $query) {
                    $query->where('currency_id', $this->currency_id_lookup);
                })
                ->when(!is_null($this->direction_lookup), function (Builder $query) {
                    $query->where('direction', $this->direction_lookup);
                })
                ->when(!is_null($this->min_value_lookup), function (Builder $query) {
                    $query->where('value', '>=', $this->min_value_lookup);
                })
                ->when(!is_null($this->max_value_lookup), function (Builder $query) {
                    $query->where('value', '<=', $this->max_value_lookup);
                });
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeCategory(Builder $query)
    {
        return $query->where('type', RuleType::CATEGORY);
    }

    public function scopeExclude(Builder $query)
    {
        return $query->where('type', RuleType::EXCLUDE);
    }

    public function priority(): Attribute
    {
        return Attribute::get(fn() => collect($this->attributesToArray())->filter(fn($value) => !is_null($value))->count()
        );
    }
}