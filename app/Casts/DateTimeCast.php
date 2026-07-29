<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

class DateTimeCast implements CastsAttributes
{
    public function get(Model $model, string $key, mixed $value, array $attributes): ?Carbon
    {
        return Carbon::parse($value);
    }

    public function set(Model $model, string $key, mixed $value, array $attributes)
    {
        return Carbon::parse($value)->setTimezone(config('app.timezone'));
    }
}
