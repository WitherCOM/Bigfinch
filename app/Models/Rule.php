<?php

namespace App\Models;

use App\Enums\Direction;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Builder;

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

    public function lookup()
    {
        return Transaction::query()
            ->where('user_id',$this->user_id)
            ->when(!is_null($this->description_lookup), function(Builder $query) {
                $query->whereLike('description',"%$this->description_lookup%");
            })
            ->when(!is_null($this->merchant_id_lookup), function(Builder $query) {
                $query->where('merchant_id', $this->merchant_id_lookup);
            })
            ->when(!is_null($this->category_id_lookup), function(Builder $query) {
                $query->where('category_id', $this->category_id_lookup);
            })
            ->when(!is_null($this->currency_id_lookup), function(Builder $query) {
                $query->where('currency_id', $this->currency_id_lookup);
            })
            ->when(!is_null($this->direction_lookup), function(Builder $query) {
                $query->where('direction', $this->direction_lookup);
            })
            ->when(!is_null($this->min_value_lookup), function(Builder $query) {
                $query->where('value','>=', $this->min_value_lookup);
            })
            ->when(!is_null($this->max_value_lookup), function(Builder $query) {
                $query->where('value', '<=',  $this->max_value_lookup);
            })
            ->get(['id']);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
