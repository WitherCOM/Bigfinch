<?php

use App\Jobs\SyncCurrencies;

class SchedulingTest extends \Tests\TestCase
{
    use \Illuminate\Foundation\Testing\RefreshDatabase;

    public function test_dailyCurrencySync(): void {
        \Illuminate\Support\Facades\Queue::fake();
        \Illuminate\Support\Carbon::setTestNow(\Illuminate\Support\Carbon::now()->setHour(6)->setMinute(0));
        \Illuminate\Support\Facades\Artisan::call('schedule:run');
        \Illuminate\Support\Facades\Queue::assertPushed(SyncCurrencies::class);

        \Illuminate\Support\Facades\Queue::fake();
        \Illuminate\Support\Carbon::setTestNow(\Illuminate\Support\Carbon::now()->setHour(7)->setMinute(5));
        \Illuminate\Support\Facades\Artisan::call('schedule:run');
        \Illuminate\Support\Facades\Queue::assertNotPushed(SyncCurrencies::class);
    }

    public function test_dailyTransactionSync(): void {
        \Illuminate\Support\Facades\Queue::fake();
        \Illuminate\Support\Carbon::setTestNow(\Illuminate\Support\Carbon::now()->setHour(7)->setMinute(0));

        \Illuminate\Support\Facades\Artisan::call('schedule:run');
        $this->assertEquals(0, \App\Models\User::all()->count());
        \Illuminate\Support\Facades\Queue::assertNothingPushed();

        \App\Models\User::factory()->create();
        \Illuminate\Support\Facades\Artisan::call('schedule:run');
        \Illuminate\Support\Facades\Queue::assertCount(1);
    }
}
