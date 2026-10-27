<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class DateTimeCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Carbon
    {
        return is_null($value) ? null : Carbon::parse($value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes)
    {
        return is_null($value) ? null : Carbon::parse($value)->setTimezone(config('app.timezone'));
    }
}
