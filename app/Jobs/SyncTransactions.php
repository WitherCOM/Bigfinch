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
use App\Models\RawMerchant;
use App\Models\Scopes\OwnerScope;
use App\Models\Transaction;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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
                    $data = OpenBankingDataParser::parse($transaction);
                    $data['id'] = Str::uuid()->toString();
                    $data['integration_id'] = $this->integration->id;
                    $data['user_id'] = $this->integration->user_id;
                    return $data;
                });
            Transaction::insert($toCreate->toArray());

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
