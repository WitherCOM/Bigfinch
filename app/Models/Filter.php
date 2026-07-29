<?php

namespace App\Models;

use App\Casts\DateTimeCast;
use App\Enums\Direction;
use App\Enums\FilterAction;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasVersion4Uuids as HasUuids;
use Illuminate\Database\Eloquent\Model;

class Filter extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'from',
        'to',
        'tag',
        'merchant',
        'direction',
        'min_value',
        'max_value',
        'currency',
        'description',
        'action',
        'action_parameters',
        'user_id',
    ];

    protected $casts = [
        'from' => DateTimeCast::class,
        'to' => DateTimeCast::class,
        'direction' => Direction::class,
        'action' => FilterAction::class,
        'action_parameters' => 'array',
    ];

    public function filterHighlight(): Attribute
    {
        return Attribute::get(function () {
            $highlight = '';
            foreach (['from', 'to', 'tag', 'merchant', 'direction',
                'min_value', 'max_value', 'currency', 'description'] as $key) {
                if ($this->{$key}) {
                    $highlight .= "$key=".$this->{$key}.',';
                }
            }

            return $highlight;
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
