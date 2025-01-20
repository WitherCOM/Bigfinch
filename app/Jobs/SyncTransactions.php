<?php

namespace App\Jobs;

use App\Enums\ActionType;
use App\Enums\Direction;
use App\Exceptions\GocardlessException;
use App\Models\Category;
use App\Models\Currency;
use App\Models\Filter;
use App\Models\Integration;
use App\Models\Merchant;
use App\Models\OpenBankingDataParser;
use App\Models\Scopes\OwnerScope;
use App\Models\Transaction;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SyncTransactions implements ShouldQueue
{
    use Queueable;

    private Integration $integration;

    /**
     * Create a new job instance.
     */
    public function __construct(Integration $integration)
    {
        $this->integration = $integration;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Auth::login($this->integration->user);
        $transactionCommonIds = $this->integration->all_transactions()->pluck('common_id');
        try {
            $toCreate = $this->integration->getTransactions()->whereNotIn('transactionId', $transactionCommonIds)
                ->map(function ($transaction) {
                    return OpenBankingDataParser::parse($this->integration, $transaction);
                });

            // Run pre modules here
            foreach ($this->integration->user->modules as $module) {
                $toCreate = $module->before($toCreate, $this->integration->user);
            }
            Transaction::insert($toCreate->toArray());
            // Run after modules here
            foreach ($this->integration->user->modules as $module) {
                $module->after($this->integration->user);
            }
            // Apply filters here
            Notification::make()
                ->title('Synced '.$this->integration->name)
                ->success()
                ->sendToDatabase($this->integration->user);

            $this->integration->last_synced_at = Carbon::now();
            $this->integration->save();
        } catch (GocardlessException $e)
        {
            Notification::make()
                ->title('Gocardless error')
                ->body($e->getMessage())
                ->danger()
                ->sendToDatabase($this->integration->user);
        }
    }
}
