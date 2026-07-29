<?php

namespace App\Helpers;

use App\Models\User;
use DateTimeInterface;
use Illuminate\Support\Carbon;

class TimezoneHelper
{
    public static function strToSavableFormat(DateTimeInterface|string $date, User|null $user): Carbon {
        if (is_null($user)) {
            return Carbon::parse($date)->setTimezone(config('app.timezone'));
        } else {
            return Carbon::parse($date, $user->user_timezone)->setTimezone(config('app.timezone'));
        }

    }

    public static function dateToUserTimezone(DateTimeInterface|string $date,User|null $user): Carbon {
        if (!is_null($user)) {
            $userTimezone = $user->user_timezone;
            return Carbon::parse($date,config('app.timezone'))->setTimezone($userTimezone);
        } else {
            return Carbon::parse($date,config('app.timezone'));
        }
    }
}
