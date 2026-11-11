<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\File;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\CurrencySeeder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        (new CurrencySeeder)->run();
        User::factory()->create();
        Category::factory()->create();
    }

    public function test_file_can_belong_to_multiple_transactions(): void
    {
        $user = User::factory()->create();
        $file = File::factory()->for($user)->create();
        $transactions = Transaction::factory()->count(2)->create(['user_id' => $user->id]);

        $file->transactions()->attach($transactions->pluck('id'));

        $this->assertCount(2, $file->fresh()->transactions);
        $this->assertTrue($transactions->first()->files->contains($file));
    }

    public function test_transaction_can_have_only_one_file(): void
    {
        $user = User::factory()->create();
        $transaction = Transaction::factory()->create(['user_id' => $user->id]);
        $files = File::factory()->count(2)->for($user)->create();

        $transaction->files()->attach($files[0]->id);

        $this->assertCount(1, $transaction->fresh()->files);
        $this->assertCount(2, $user->files);
        $this->expectException(UniqueConstraintViolationException::class);
        $transaction->files()->attach($files[1]->id);
    }

    public function test_syncing_same_file_twice_does_not_duplicate_pivot_row(): void
    {
        $user = User::factory()->create();
        $file = File::factory()->for($user)->create();
        $transaction = Transaction::factory()->create(['user_id' => $user->id]);

        $file->transactions()->attach($transaction->id);
        $file->transactions()->syncWithoutDetaching([$transaction->id]);

        $this->assertCount(1, $file->transactions);
    }

    public function test_deleting_file_removes_pivot_rows_and_stored_file(): void
    {
        Storage::fake();
        $user = User::factory()->create();
        $file = File::factory()->for($user)->create(['path' => 'bills/bill.pdf']);
        Storage::put('bills/bill.pdf', 'content');
        $transaction = Transaction::factory()->create(['user_id' => $user->id]);
        $file->transactions()->attach($transaction->id);

        $file->delete();

        $this->assertDatabaseCount('file_transaction', 0);
        Storage::assertMissing('bills/bill.pdf');
        $this->assertDatabaseHas('transactions', ['id' => $transaction->id]);
    }

    public function test_deleting_transaction_keeps_file(): void
    {
        $user = User::factory()->create();
        $file = File::factory()->for($user)->create();
        $transaction = Transaction::factory()->create(['user_id' => $user->id]);
        $file->transactions()->attach($transaction->id);

        $transaction->forceDelete();

        $this->assertDatabaseHas('files', ['id' => $file->id]);
        $this->assertDatabaseCount('file_transaction', 0);
    }
}
