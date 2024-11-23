<?php

namespace Tests\Unit;

use App\Jobs\SyncTransactions;
use App\Models\Integration;
use App\Models\Merchant;
use App\Models\Scopes\OwnerScope;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\CurrencySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Tests\TestCase;

class SyncJobTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CurrencySeeder::class);
        Cache::set('gocardless_access_token', Str::uuid(), 3600);
        Http::fake([
            'https://bankaccountdata.gocardless.com/api/v2/*' => Http::response([
                'transactions' => [
                    'booked' => [
                        [
                            "transactionId" => "id1",
                            "bookingDate" => "2024-11-11",
                            "bookingDateTime" => "2024-11-11T10:43:16.685Z",
                            "transactionAmount" => [
                                "amount" => "90000.00",
                                "currency" => "HUF"
                            ],
                            "debtorName" => "John Doe",
                            "debtorAccount" => [],
                            "remittanceInformationUnstructured" => "John Doe",
                            "proprietaryBankTransactionCode" => "TRANSFER",
                        ],
                        [
                            "transactionId" => "id2",
                            "bookingDate" => "2024-11-11",
                            "bookingDateTime" => "2024-11-11T10:43:16.685Z",
                            "transactionAmount" => [
                                "amount" => "10000.00",
                                "currency" => "HUF"
                            ],
                            "debtorName" => "John Doe2",
                            "debtorAccount" => [],
                            "remittanceInformationUnstructured" => "John Doe2",
                            "proprietaryBankTransactionCode" => "TRANSFER",
                        ],
                        [
                            "transactionId" => "id4",
                            "bookingDate" => "2024-11-11",
                            "bookingDateTime" => "2024-11-11T10:43:16.685Z",
                            "transactionAmount" => [
                                "amount" => "10000.00",
                                "currency" => "HUF"
                            ],
                            "debtorName" => "Ékezet ebben Ááá",
                            "debtorAccount" => [],
                            "remittanceInformationUnstructured" => "John Doe2",
                            "proprietaryBankTransactionCode" => "TRANSFER",
                        ],
                        [
                            "transactionId" => "id6",
                            "bookingDate" => "2024-11-11",
                            "bookingDateTime" => "2024-11-11T10:43:16.685Z",
                            "transactionAmount" => [
                                "amount" => "10000.00",
                                "currency" => "HUF"
                            ],
                            "debtorName" => "Ékezet ebben Ááá",
                            "debtorAccount" => [],
                            "remittanceInformationUnstructured" => "John Doe2",
                            "proprietaryBankTransactionCode" => "TRANSFER",
                        ],
                        [
                            "transactionId" => "id3",
                            "bookingDate" => "2024-10-20",
                            "valueDate" => "2024-10-20",
                            "bookingDateTime" => "2024-10-20T22:10:41.022712Z",
                            "valueDateTime" => "2024-10-20T22:10:41.022978Z",
                            "transactionAmount" => [
                                "amount" => "2.58",
                                "currency" => "USD"
                            ],
                            "creditorName" => "John Doe",
                            "creditorAccount" => [
                                "iban" => "LT000000000000"
                            ],
                            "debtorName" => "John Doe 2",
                            "debtorAccount" => [
                                "iban" => "LT00000000000000000001"
                            ],
                            "remittanceInformationUnstructuredArray" => [
                                "From John Doe",
                                "Ehhez: To John Doe2"
                            ],
                            "proprietaryBankTransactionCode" => "TRANSFER",
                            "internalTransactionId" => "0b929ea87e3c60f93b9e512ddb10be79"
                        ],
                    ]
                ]
            ])
        ]);
    }

    public function test_sync_job(): void
    {
        $user = User::factory()->create();
        $integration = Integration::insert([
            'id' => Str::uuid(),
            'name' => 'asd',
            'user_id' => $user->id,
            'accounts' => json_encode([Str::uuid()]),
            'institution_name' => 'name',
            'institution_logo' => 'logo',
            'requisition_id' => Str::uuid()
        ]);
        $job = new SyncTransactions(Integration::query()->withoutGlobalScope(OwnerScope::class)->first());
        $job->handle();
        $this->assertDatabaseCount(Transaction::class,5);
        $this->assertDatabaseCount(Merchant::class,3);
    }
}
