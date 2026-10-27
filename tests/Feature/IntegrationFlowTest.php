<?php

namespace Tests\Feature;

use App\Exceptions\GocardlessException;
use App\Filament\Resources\Integrations\Pages\CreateIntegration;
use App\Filament\Resources\Integrations\Pages\ListIntegrations;
use App\Jobs\RunFlagEngine;
use App\Jobs\SyncTransactions;
use App\Models\Gocardless\GocardlessToken;
use App\Models\Integration;
use App\Models\User;
use Database\Seeders\CurrencySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class IntegrationFlowTest extends TestCase
{
    use RefreshDatabase;

    private const BASE = 'https://bankaccountdata.gocardless.com/api/v2';

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(CurrencySeeder::class);
        $this->user = User::factory()->create();
        $this->user->user_timezone = 'Europe/Budapest';
        $this->user->save();
        $this->actingAs($this->user);
        Livewire::actingAs($this->user);
        Cache::flush();
        Cache::put('banks', collect(['BANK_ID' => 'Test Bank (HU)']), 3600);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    private function fakeGocardless(array $overrides = []): void
    {
        Http::fake($overrides + [
            self::BASE.'/institutions/*' => Http::response([
                'id' => 'BANK_ID',
                'name' => 'Test Bank',
                'logo' => 'https://example.test/logo.png',
                'max_access_valid_for_days' => '90',
                'transaction_total_days' => '730',
            ]),
            self::BASE.'/agreements/enduser/' => Http::response(['id' => 'agreement-1'], 201),
            self::BASE.'/agreements/enduser/*' => Http::response(['access_valid_for_days' => 90]),
            self::BASE.'/requisitions/' => Http::response([
                'id' => 'requisition-new',
                'link' => 'https://ob.gocardless.test/psd2/start/requisition-new',
            ], 201),
            self::BASE.'/requisitions/*' => Http::response([
                'id' => 'requisition-1',
                'agreement' => 'agreement-1',
                'accounts' => ['account-1', 'account-2'],
            ]),
        ]);
    }

    private function createIntegration(array $attributes = []): Integration
    {
        return Integration::factory()->create(array_merge([
            'user_id' => $this->user->id,
            'requisition_id' => 'requisition-1',
            'link' => 'https://ob.gocardless.test/psd2/start/requisition-1',
            'expires_at' => null,
        ], $attributes));
    }

    public function test_integration_can_be_created_through_gocardless_requisition(): void
    {
        GocardlessToken::factory()->create();
        $this->fakeGocardless();

        Livewire::test(CreateIntegration::class)
            ->fillForm([
                'name' => 'My bank',
                'institution_id' => 'BANK_ID',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('integrations', [
            'name' => 'My bank',
            'user_id' => $this->user->id,
            'institution_id' => 'BANK_ID',
            'institution_name' => 'Test Bank',
            'requisition_id' => 'requisition-new',
            'link' => 'https://ob.gocardless.test/psd2/start/requisition-new',
        ]);
        $integration = Integration::firstWhere('name', 'My bank');
        $this->assertNull($integration->getRawOriginal('expires_at'));

        Http::assertSent(fn ($request) => $request->method() === 'POST'
            && $request->url() === self::BASE.'/agreements/enduser/'
            && $request['institution_id'] === 'BANK_ID'
            && $request['access_scope'] === ['transactions', 'details']);
        Http::assertSent(fn ($request) => $request->method() === 'POST'
            && $request->url() === self::BASE.'/requisitions/'
            && $request['agreement'] === 'agreement-1'
            && $request['redirect'] === route('gocardless.callback'));
    }

    public function test_integration_creation_fails_when_gocardless_errors(): void
    {
        GocardlessToken::factory()->create();
        $this->fakeGocardless([
            self::BASE.'/institutions/BANK_ID' => Http::response(['detail' => 'boom'], 500),
        ]);

        $this->expectException(GocardlessException::class);

        try {
            Livewire::test(CreateIntegration::class)
                ->fillForm([
                    'name' => 'My bank',
                    'institution_id' => 'BANK_ID',
                ])
                ->call('create');
        } finally {
            $this->assertDatabaseCount('integrations', 0);
        }
    }

    public function test_callback_accepts_requisition_and_stores_accounts_and_expiry(): void
    {
        $integration = $this->createIntegration();
        $this->fakeGocardless();

        $response = $this->get(route('gocardless.callback', ['ref' => 'requisition-1']));

        $response->assertRedirect(route('filament.app.resources.transactions.index'));
        $integration->refresh();
        $this->assertEquals(['account-1', 'account-2'], $integration->accounts->all());
        $this->assertNotNull($integration->expires_at);
        $this->assertTrue($integration->expires_at->isSameDay(now()->addDays(90)));
    }

    public function test_callback_with_unknown_reference_returns_not_found(): void
    {
        $this->fakeGocardless();

        $this->get(route('gocardless.callback', ['ref' => 'does-not-exist']))->assertNotFound();
        $this->get(route('gocardless.callback'))->assertNotFound();

        Http::assertNothingSent();
    }

    public function test_callback_does_not_refetch_already_accepted_integration(): void
    {
        $integration = $this->createIntegration(['expires_at' => now()->addDays(10)]);
        $this->fakeGocardless();

        $this->get(route('gocardless.callback', ['ref' => $integration->requisition_id]))
            ->assertRedirect(route('filament.app.resources.transactions.index'));

        Http::assertNothingSent();
    }

    public function test_expired_integration_can_be_renewed(): void
    {
        $integration = $this->createIntegration(['expires_at' => now()->subDay()]);
        $this->fakeGocardless();

        Livewire::test(ListIntegrations::class)
            ->assertTableActionVisible('renew', $integration)
            ->callTableAction('renew', $integration)
            ->assertRedirect('https://ob.gocardless.test/psd2/start/requisition-new');

        $integration->refresh();
        $this->assertEquals('requisition-new', $integration->requisition_id);
        $this->assertTrue($integration->can_accept);
        Http::assertSent(fn ($request) => $request->method() === 'DELETE'
            && $request->url() === self::BASE.'/requisitions/requisition-1/');
    }

    public function test_renew_is_hidden_for_active_integration(): void
    {
        $integration = $this->createIntegration(['expires_at' => now()->addDays(30)]);

        Livewire::test(ListIntegrations::class)
            ->assertTableActionHidden('renew', $integration);
    }

    public function test_renew_tolerates_requisition_already_gone_at_gocardless(): void
    {
        $integration = $this->createIntegration(['expires_at' => now()->subDay()]);
        $this->fakeGocardless([
            self::BASE.'/requisitions/requisition-1/' => Http::response([], 404),
        ]);

        Livewire::test(ListIntegrations::class)
            ->callTableAction('renew', $integration);

        $this->assertEquals('requisition-new', $integration->refresh()->requisition_id);
    }

    public function test_deleting_integration_removes_requisition_at_gocardless(): void
    {
        $integration = $this->createIntegration();
        $this->fakeGocardless([
            self::BASE.'/requisitions/requisition-1/' => Http::response([], 200),
        ]);

        Livewire::test(ListIntegrations::class)
            ->callTableAction('delete', $integration);

        $this->assertDatabaseMissing('integrations', ['id' => $integration->id]);
        Http::assertSent(fn ($request) => $request->method() === 'DELETE'
            && $request->url() === self::BASE.'/requisitions/requisition-1/');
    }

    public function test_deleting_integration_succeeds_when_requisition_not_found(): void
    {
        $integration = $this->createIntegration();
        $this->fakeGocardless([
            self::BASE.'/requisitions/requisition-1/' => Http::response([], 404),
        ]);

        $integration->delete();

        $this->assertDatabaseMissing('integrations', ['id' => $integration->id]);
    }

    public function test_integration_is_kept_when_gocardless_delete_fails(): void
    {
        $integration = $this->createIntegration();
        $this->fakeGocardless([
            self::BASE.'/requisitions/requisition-1/' => Http::response(['detail' => 'boom'], 500),
        ]);

        try {
            $integration->delete();
            $this->fail('Expected GocardlessException');
        } catch (GocardlessException) {
            // expected
        }

        $this->assertDatabaseHas('integrations', ['id' => $integration->id]);
    }

    public function test_sync_action_dispatches_sync_job_chained_with_flag_engine(): void
    {
        Bus::fake();
        $integration = $this->createIntegration(['expires_at' => now()->addDays(30)]);

        Livewire::test(ListIntegrations::class)
            ->callTableAction('sync', $integration);

        Bus::assertChained([
            SyncTransactions::class,
            RunFlagEngine::class,
        ]);
    }

    public function test_sync_all_dispatches_batch_with_a_job_per_integration(): void
    {
        Bus::fake();
        $this->createIntegration(['requisition_id' => 'r1']);
        $this->createIntegration(['requisition_id' => 'r2']);
        $this->createIntegration(['requisition_id' => 'r3']);

        Livewire::test(ListIntegrations::class)
            ->callAction('sync_all');

        Bus::assertBatched(fn ($batch) => $batch->jobs->count() === 3
            && $batch->jobs->every(fn ($job) => $job instanceof SyncTransactions));
    }
}
