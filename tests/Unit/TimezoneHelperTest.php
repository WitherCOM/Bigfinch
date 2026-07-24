<?php

namespace Tests\Unit;

use App\Helpers\TimezoneHelper;
use App\Models\User;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class TimezoneHelperTest extends TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    // test name format test_#format_#appTimezone_#inputTimezone_#userTimezone

    private const DATE_INPUT         = "2025-10-10";
    private const DATETIME_UTC       = "2025-10-10T02:00:00.000000Z";
    private const DATETIME_BUDAPEST  = "2025-10-10T02:00:00.000000+02:00";
    private const DATETIME_HONOLULU  = "2025-10-10T02:00:00.000000-10:00";

    private function userWithTimezone(string $timezone): User
    {
        $user = User::factory()->create();
        $settings = $user->settings;
        $settings['user_timezone'] = $timezone;
        $user->settings = $settings;
        $user->save();
        return $user;
    }

    private static function assertCarbon(Carbon $c, int $y, int $mo, int $d, int $h, int $mi, int $s): void
    {
        self::assertEquals($y,  $c->year);
        self::assertEquals($mo, $c->month);
        self::assertEquals($d,  $c->day);
        self::assertEquals($h,  $c->hour);
        self::assertEquals($mi, $c->minute);
        self::assertEquals($s,  $c->second);
    }

    // test name format test_date_#appTimezone_#userTimezone

    public function test_date_UTC_UTC(): void
    {
        config(['app.timezone' => 'UTC']);
        $user = $this->userWithTimezone('UTC');
        $dbDate = TimezoneHelper::strToSavableFormat("2025-10-10", $user);
        self::assertCarbon($dbDate, 2025, 10, 10, 0, 0, 0);

        $userDate = TimezoneHelper::dateToUserTimezone($dbDate, $user);
        self::assertCarbon($userDate, 2025, 10, 10, 0, 0, 0);
    }

    public function test_date_UTC_Budapest(): void
    {
        config(['app.timezone' => 'UTC']);
        $user = $this->userWithTimezone('Europe/Budapest');
        $dbDate = TimezoneHelper::strToSavableFormat(self::DATE_INPUT, $user);
        self::assertCarbon($dbDate, 2025, 10, 10, 0, 0, 0);
        $userDate = TimezoneHelper::dateToUserTimezone($dbDate, $user);
        self::assertCarbon($userDate, 2025, 10, 10, 0, 0, 0);
    }

    //TODO check big timezone differences

    // test name format test_dateTime_#appTimezone_#inputTimezone_#userTimezone

    public function test_dateTime_UTC_UTC_UTC(): void
    {
        config(['app.timezone' => 'UTC']);
        $user = $this->userWithTimezone('UTC');
        $dbDate = TimezoneHelper::strToSavableFormat(self::DATETIME_UTC, $user);
        self::assertCarbon($dbDate, 2025, 10, 10, 2, 0, 0);

        $userDate = TimezoneHelper::dateToUserTimezone($dbDate, $user);
        self::assertCarbon($userDate, 2025, 10, 10, 2, 0, 0);
    }

    public function test_dateTime_UTC_UTC_Budapest(): void
    {
        config(['app.timezone' => 'UTC']);
        $user = $this->userWithTimezone('Europe/Budapest');
        $dbDate = TimezoneHelper::strToSavableFormat(self::DATETIME_UTC, $user);
        self::assertCarbon($dbDate, 2025, 10, 10, 2, 0, 0);
        $userDate = TimezoneHelper::dateToUserTimezone($dbDate, $user);
        // 02:00 UTC → 04:00 CEST
        self::assertCarbon($userDate, 2025, 10, 10, 4, 0, 0);
    }

    public function test_dateTime_UTC_Budapest_UTC(): void
    {
        config(['app.timezone' => 'UTC']);
        $user = $this->userWithTimezone('UTC');
        $dbDate = TimezoneHelper::strToSavableFormat(self::DATETIME_BUDAPEST, $user);
        self::assertCarbon($dbDate, 2025, 10, 10, 2, 0, 0);
        $userDate = TimezoneHelper::dateToUserTimezone($dbDate, $user);
        // 02:00+02:00 → 00:00 UTC
        self::assertCarbon($userDate, 2025, 10, 10, 0, 0, 0);
    }

    public function test_dateTime_UTC_Budapest_Budapest(): void
    {
        config(['app.timezone' => 'UTC']);
        $user = $this->userWithTimezone('Europe/Budapest');
        $dbDate = TimezoneHelper::strToSavableFormat(self::DATETIME_BUDAPEST, $user);
        self::assertCarbon($dbDate, 2025, 10, 10, 2, 0, 0);
        $userDate = TimezoneHelper::dateToUserTimezone($dbDate, $user);
        // 02:00+02:00 → 00:00 UTC → 02:00 CEST
        self::assertCarbon($userDate, 2025, 10, 10, 2, 0, 0);
    }

    public function test_dateTime_UTC_Budapest_Honolulu(): void
    {
        config(['app.timezone' => 'UTC']);
        $user = $this->userWithTimezone('Pacific/Honolulu');
        $dbDate = TimezoneHelper::strToSavableFormat(self::DATETIME_BUDAPEST, $user);
        self::assertCarbon($dbDate, 2025, 10, 10, 2, 0, 0);
        $userDate = TimezoneHelper::dateToUserTimezone($dbDate, $user);
        // 02:00+02:00 → 00:00 UTC → 14:00 HST prev day
        self::assertCarbon($userDate, 2025, 10, 9, 14, 0, 0);
    }
}
