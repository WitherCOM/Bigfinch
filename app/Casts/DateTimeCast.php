<?php

namespace App\Casts;

use App\Helpers\TimezoneHelper;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class DateTimeCast implements CastsAttributes
{

    public function get(Model $model, string $key, mixed $value, array $attributes)
    {
        return TimezoneHelper::dateToUserTimezone($value, Auth::user());
    }

    public function set(Model $model, string $key, mixed $value, array $attributes)
    {
        return TimezoneHelper::strToSavableFormat($value, Auth::user());
    }
}
