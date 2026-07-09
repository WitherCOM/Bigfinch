<?php

namespace App\Helpers;

use App\Models\User;
use Illuminate\Support\Carbon;

class TimezoneHelper
{
    public static function strToSavableFormat(DateTimeInterface|string $date, User $user): Carbon {
        return Carbon::parse($date, $user->user_timezone);
    }

    public static function dateToUserTimezone(Carbon $date,User $user): Carbon {
        $userTimezone = $user->user_timezone;
        if ($date->getTimezone()->getName() !== config('app.timezone')) {
            $date->setTimezone($userTimezone);
        }
        return $date;
    }
}
