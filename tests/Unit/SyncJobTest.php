<?php


use App\Enums\ActionType;
use App\Jobs\SyncTransactions;
use App\Models\Integration;
use App\Models\Merchant;
use App\Models\Scopes\OwnerScope;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\CurrencySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class SyncJobTest extends TestCase
{
    use RefreshDatabase;

    public User $user;
    public Integration $integration;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CurrencySeeder::class);
        Cache::set('gocardless_access_token', Str::uuid(), 3600);
        $this->user = User::factory()->create();
    }

    public function test_allTransactionAreCreatedAndNotDuplicated(): void {
        $integration = Integration::factory()->withAccountCount(1)->create();
        $booked = \Database\Factories\obapi\TransactionItemFactory::new()->many(30);
        $pending = \Database\Factories\obapi\TransactionItemFactory::new(false)->many(10);
        Http::fake([
            'https://bankaccountdata.gocardless.com/api/v2/*' => Http::sequence()
                ->push([
                    'transactions' => [
                        'booked' => $booked,
                        'pending' => $pending
                    ]])
                ->push([
                    'transactions' => [
                        'booked' => $booked,
                        'pending' => $pending
                    ]])
                ->push([
                    'transactions' => [
                        'booked' => array_merge($booked, \Database\Factories\obapi\TransactionItemFactory::new()->many(10)),
                        'pending' => $pending
                    ]])
        ]);
        $job = new SyncTransactions($integration);
        $job->handle();
        $this->assertDatabaseCount(Transaction::class, 40);

        $job = new SyncTransactions($integration);
        $job->handle();
        $this->assertDatabaseCount(Transaction::class, 40);

        $job = new SyncTransactions($integration);
        $job->handle();
        $this->assertDatabaseCount(Transaction::class, 50);
    }

    public function test_allTransactionAreCreatedWithMultipleAccount(): void {
        $integration = Integration::factory()->withAccountCount(3)->create();
        Http::fake([
            'https://bankaccountdata.gocardless.com/api/v2/*' => Http::sequence()
                ->push([
                    'transactions' => [
                        'booked' => \Database\Factories\obapi\TransactionItemFactory::new()->many(10),
                        'pending' => \Database\Factories\obapi\TransactionItemFactory::new(false)->many(5)
                    ]])
                ->push([
                    'transactions' => [
                        'booked' => \Database\Factories\obapi\TransactionItemFactory::new()->many(10),
                        'pending' => []
                    ]])
                ->push([
                    'transactions' => [
                        'booked' => \Database\Factories\obapi\TransactionItemFactory::new()->many(10),
                        'pending' =>  \Database\Factories\obapi\TransactionItemFactory::new(false)->many(5)
                    ]])
        ]);

        $job = new SyncTransactions($integration);
        $job->handle();
        $this->assertDatabaseCount(Transaction::class, 40);
    }

    public function test_removingPendingTransactionsIfNotPendingAnymore(): void {
        $integration = Integration::factory()->withAccountCount(1)->create();
        $booked = \Database\Factories\obapi\TransactionItemFactory::new()->many(20);
        $pending = \Database\Factories\obapi\TransactionItemFactory::new(false)->many(5);
        Http::fake([
            'https://bankaccountdata.gocardless.com/api/v2/*' => Http::sequence()
                ->push([
                    'transactions' => [
                        'booked' => $booked,
                        'pending' => $pending
                    ]])
                ->push([
                    'transactions' => [
                        'booked' => $booked,
                        'pending' => []
                    ]])
        ]);

        $job = new SyncTransactions($integration);
        $job->handle();
        $this->assertDatabaseCount(Transaction::class, 25);

        $job = new SyncTransactions($integration);
        $job->handle();
        $this->assertDatabaseCount(Transaction::class, 20);
    }

    public function test_switchPendingTransactionsToBooked(): void {
        $integration = Integration::factory()->withAccountCount(1)->create();
        $booked = \Database\Factories\obapi\TransactionItemFactory::new()->many(20);
        $pending = \Database\Factories\obapi\TransactionItemFactory::new(false)->many(5);
        $bookedPending = array_map(function ($item) {
            $item['bookingDateTime'] = Carbon::now()->toISOString();
            return $item;
        }, $pending);
        Http::fake([
            'https://bankaccountdata.gocardless.com/api/v2/*' => Http::sequence()
                ->push([
                    'transactions' => [
                        'booked' => $booked,
                        'pending' => $pending
                    ]])
                ->push([
                    'transactions' => [
                        'booked' => array_merge($booked,$bookedPending),
                        'pending' => []
                    ]])
        ]);

        $job = new SyncTransactions($integration);
        $job->handle();
        $this->assertDatabaseCount(Transaction::class, 25);

        $job = new SyncTransactions($integration);
        $job->handle();
        $this->assertDatabaseCount(Transaction::class, 25);
    }

    public function test_switchPendingTransactionsToBookedCheckParameterChanged() {
        $integration = Integration::factory()->withAccountCount(1)->create();
        $pending = \Database\Factories\obapi\TransactionItemFactory::new(false)->many(1);
        $bookedPending = array_map(function ($item) {
            $item['bookingDateTime'] = Carbon::now()->toISOString();
            return $item;
        }, $pending);
        Http::fake([
            'https://bankaccountdata.gocardless.com/api/v2/*' => Http::sequence()
                ->push([
                    'transactions' => [
                        'booked' => [],
                        'pending' => $pending
                    ]])
                ->push([
                    'transactions' => [
                        'booked' => $bookedPending,
                        'pending' => []
                    ]])
        ]);

        $job = new SyncTransactions($integration);
        $job->handle();
        $this->assertDatabaseCount(Transaction::class, 1);
        $transactionFirst = Transaction::first();
        $this->assertTrue($transactionFirst->is_pending);

        $job = new SyncTransactions($integration);
        $job->handle();
        $this->assertDatabaseCount(Transaction::class, 1);
        $transactionSecond = Transaction::first();
        $this->assertEquals($transactionFirst->description, $transactionSecond->description);
        $this->assertEquals(collect($transactionFirst->getAttributes())->except(['is_pending','date','updated_at']),
            collect($transactionSecond->getAttributes())->except(['is_pending','date','updated_at']));
    }

}
