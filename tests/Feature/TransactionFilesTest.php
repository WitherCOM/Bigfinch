<?php

namespace Tests\Feature;

use App\Filament\Resources\Transactions\Pages\EditTransaction;
use App\Filament\Resources\Transactions\RelationManagers\FilesRelationManager;
use App\Models\Category;
use App\Models\File;
use App\Models\Transaction;
use App\Models\User;
use Database\Seeders\CurrencySeeder;
use Filament\Actions\Testing\TestAction;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class TransactionFilesTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Transaction $transaction;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake();
        (new CurrencySeeder)->run();
        $this->user = User::factory()->create();
        foreach (['Transaction', 'File'] as $model) {
            foreach (['ViewAny', 'View', 'Create', 'Update', 'Delete'] as $ability) {
                $this->user->givePermissionTo(Permission::findOrCreate($ability.':'.$model, 'web'));
            }
        }
        Category::factory()->create();
        $this->transaction = Transaction::factory()->create(['user_id' => $this->user->id]);
        $this->actingAs($this->user);
        Livewire::actingAs($this->user);
    }

    private function relationManager(?Transaction $transaction = null)
    {
        return Livewire::test(FilesRelationManager::class, [
            'ownerRecord' => $transaction ?? $this->transaction,
            'pageClass' => EditTransaction::class,
        ]);
    }

    public function test_pdf_can_be_uploaded_and_is_attached_to_transaction(): void
    {
        $upload = UploadedFile::fake()->create('bill.pdf', 100, 'application/pdf');

        $this->relationManager()
            ->callAction(TestAction::make('create')->table(), ['path' => $upload])
            ->assertHasNoFormErrors();

        $file = $this->transaction->files()->first();
        $this->assertNotNull($file);
        $this->assertSame($this->user->id, $file->user_id);
        $this->assertSame('application/pdf', $file->mime_type);
        $this->assertStringStartsWith('bills/', $file->path);
        Storage::assertExists($file->path);
    }

    public function test_image_can_be_uploaded(): void
    {
        $upload = UploadedFile::fake()->image('receipt.png');

        $this->relationManager()
            ->callAction(TestAction::make('create')->table(), ['path' => $upload])
            ->assertHasNoFormErrors();

        $this->assertSame('image/png', $this->transaction->files()->first()->mime_type);
    }

    public function test_other_file_types_are_rejected(): void
    {
        $upload = UploadedFile::fake()->create('malware.exe', 10, 'application/x-msdownload');

        $this->relationManager()
            ->callAction(TestAction::make('create')->table(), ['path' => $upload])
            ->assertHasFormErrors(['path']);

        $this->assertDatabaseCount('files', 0);
    }

    public function test_too_large_file_is_rejected(): void
    {
        $upload = UploadedFile::fake()->create('big.pdf', 10241, 'application/pdf');

        $this->relationManager()
            ->callAction(TestAction::make('create')->table(), ['path' => $upload])
            ->assertHasFormErrors(['path']);

        $this->assertDatabaseCount('files', 0);
    }

    public function test_existing_file_can_be_attached_to_another_transaction(): void
    {
        $file = File::factory()->for($this->user)->create();
        $file->transactions()->attach($this->transaction->id);
        $other = Transaction::factory()->create(['user_id' => $this->user->id]);

        $this->relationManager($other)
            ->callAction(TestAction::make('attach')->table(), ['recordId' => $file->id])
            ->assertHasNoFormErrors();

        $this->assertCount(2, $file->fresh()->transactions);
    }

    public function test_only_own_files_are_listed_for_attaching(): void
    {
        $foreign = File::factory()->create();
        $this->relationManager()
            ->mountAction(TestAction::make('attach')->table())
            ->assertDontSee($foreign->name);
    }

    public function test_file_can_be_detached_without_deleting_it(): void
    {
        $file = File::factory()->for($this->user)->create();
        $file->transactions()->attach($this->transaction->id);

        $this->relationManager()
            ->callAction(TestAction::make('detach')->table($file));

        $this->assertCount(0, $this->transaction->fresh()->files);
        $this->assertDatabaseHas('files', ['id' => $file->id]);
    }

    public function test_user_without_permission_cannot_see_files(): void
    {
        $other = User::factory()->create();
        $other->givePermissionTo(Permission::findOrCreate('View:Transaction', 'web'));
        $file = File::factory()->for($this->user)->create();
        $file->transactions()->attach($this->transaction->id);
        $this->actingAs($other);
        Livewire::actingAs($other);

        $this->assertFalse($other->can('view', $file));
        $this->assertFalse($other->can('viewAny', File::class));
    }

    public function test_image_file_can_be_previewed(): void
    {
        $file = File::factory()->image()->for($this->user)->create();
        Storage::put($file->path, 'image-bytes');
        $file->transactions()->attach($this->transaction->id);

        $this->relationManager()
            ->mountAction(TestAction::make('preview')->table($file))
            ->assertSee($file->name)
            ->assertActionMounted(TestAction::make('preview')->table($file));
    }

    public function test_preview_view_renders_image_from_temporary_url(): void
    {
        $file = File::factory()->image()->for($this->user)->create();
        Storage::put($file->path, 'image-bytes');
        $url = Storage::temporaryUrl($file->path, now()->addMinutes(5));

        $html = view('filament.resources.transaction-resource.relation-managers.file-preview', [
            'file' => $file,
            'url' => $url,
        ])->render();

        $this->assertStringContainsString('<img src="'.e($url).'"', $html);
        $this->assertStringContainsString($file->name, $html);
    }

    public function test_pdf_file_cannot_be_previewed(): void
    {
        $file = File::factory()->for($this->user)->create();
        $file->transactions()->attach($this->transaction->id);

        $this->relationManager()
            ->assertActionHidden(TestAction::make('preview')->table($file));
    }

    private function redPng(): UploadedFile
    {
        $image = imagecreatetruecolor(4, 4);
        imagefill($image, 0, 0, imagecolorallocate($image, 255, 0, 0));
        ob_start();
        imagepng($image);

        return UploadedFile::fake()->createWithContent('red.png', ob_get_clean());
    }

    private function storedPixelIsGray(string $path): bool
    {
        $image = imagecreatefromstring(Storage::get($path));
        $color = imagecolorsforindex($image, imagecolorat($image, 0, 0));

        return $color['red'] === $color['green'] && $color['green'] === $color['blue'];
    }

    public function test_uploaded_image_is_converted_to_grayscale(): void
    {
        $this->relationManager()
            ->callAction(TestAction::make('create')->table(), ['path' => $this->redPng()])
            ->assertHasNoFormErrors();

        $file = $this->transaction->files()->first();
        $this->assertTrue($this->storedPixelIsGray($file->path));
        $this->assertSame(Storage::size($file->path), $file->size);
    }

    public function test_pdf_is_not_altered_by_grayscale_conversion(): void
    {
        $upload = UploadedFile::fake()->create('bill.pdf', 10, 'application/pdf');

        $this->relationManager()
            ->callAction(TestAction::make('create')->table(), ['path' => $upload])
            ->assertHasNoFormErrors();

        $file = $this->transaction->files()->first();
        $this->assertSame('application/pdf', $file->mime_type);
        $this->assertSame(Storage::size($file->path), $file->size);
    }

    public function test_upload_and_attach_are_hidden_once_transaction_has_a_file(): void
    {
        $file = File::factory()->for($this->user)->create();
        $file->transactions()->attach($this->transaction->id);

        $this->relationManager()
            ->assertActionHidden(TestAction::make('create')->table())
            ->assertActionHidden(TestAction::make('attach')->table());
    }

    public function test_upload_and_attach_are_visible_while_transaction_has_no_file(): void
    {
        $this->relationManager()
            ->assertActionVisible(TestAction::make('create')->table())
            ->assertActionVisible(TestAction::make('attach')->table());
    }

    public function test_transaction_cannot_get_a_second_file_in_the_database(): void
    {
        $files = File::factory()->count(2)->for($this->user)->create();
        $files[0]->transactions()->attach($this->transaction->id);

        $this->expectException(QueryException::class);
        $files[1]->transactions()->attach($this->transaction->id);
    }

    public function test_same_file_can_still_be_shared_by_multiple_transactions(): void
    {
        $file = File::factory()->for($this->user)->create();
        $other = Transaction::factory()->create(['user_id' => $this->user->id]);

        $file->transactions()->attach([$this->transaction->id, $other->id]);

        $this->assertCount(2, $file->transactions);
    }
}
