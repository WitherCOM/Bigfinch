<?php

namespace App\Jobs;

use App\Engine\FlagInternalTransactions;
use App\Engine\OpenBankingEngine;
use App\Enums\ActionType;
use App\Exceptions\GocardlessException;
use App\Models\Integration;
use App\Models\Merchant;
use App\Models\RawMerchant;
use App\Models\Transaction;
use Filament\Notifications\Notification;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SyncTransactions implements ShouldQueue
{
    use Batchable, Queueable;

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
        try {
            Auth::login($this->integration->user);
            $transactionCommonIds = $this->integration->all_transactions()->select(['id','common_id','is_pending'])->get();
            $allTransactions = $this->integration->getTransactions(Carbon::now()->startOfMonth()->subMonths());
            $pendingIds = $transactionCommonIds->where('is_pending',true);
            if (!is_null($allTransactions['booked'])) {
                // Create bookings that does not exists
                $toCreate = $allTransactions['booked']->whereNotIn('transactionId', $transactionCommonIds->pluck('common_id'))
                    ->map(function ($transaction) {
                        $data = OpenBankingEngine::parse($transaction);
                        $data['id'] = Str::uuid()->toString();
                        $data['integration_id'] = $this->integration->id;
                        $data['user_id'] = $this->integration->user_id;
                        $data['is_pending'] = false;
                        $data['created_at'] = Carbon::now();
                        $data['updated_at'] = Carbon::now();
                        return $data;
                    });
                Transaction::insert($toCreate->toArray());
            }
            if (!is_null($allTransactions['pending'])) {
                // Create pendings that does not exists
                $toCreate = $allTransactions['pending']->whereNotIn('transactionId', $transactionCommonIds->pluck('common_id'))
                ->map(function ($transaction) {
                    $data = OpenBankingEngine::parse($transaction);
                    $data['id'] = Str::uuid()->toString();
                    $data['integration_id'] = $this->integration->id;
                    $data['user_id'] = $this->integration->user_id;
                    $data['is_pending'] = true;
                    return $data;
                });
                Transaction::insert($toCreate->toArray());
            }

            // Update date and value and status of booked transactions
            // If contained by $pendingIds and it is in $allTransactions['booked']
            $toUpdate = collect([]);
            foreach ($pendingIds as $pendingId) {
                foreach($allTransactions['booked'] as $transaction) {
                    if ($transaction['transactionId'] == $pendingId->common_id) {
                        $data = OpenBankingEngine::parse($transaction);

                        $toUpdate->push([
                            'id' => $pendingId->id,
                            'is_pending' => false,
                            'user_id' => Auth::id(),
                            ...$data
                        ]);
                    }
                }
            }
            Transaction::upsert($toUpdate->toArray(),uniqueBy: ['id'], update: ['date','value','is_pending']);

            // Delete declined transactions
            // If contained by $pendingIds and not in $allTransactions['pending'] neither in $allTransactions['booked']
            $toDelete = collect([]);
            foreach ($pendingIds as $pendingId) {
                if (!$allTransactions['booked']->pluck('transactionId')->contains($pendingId->common_id) &&
                    !$allTransactions['pending']->pluck('transactionId')->contains($pendingId->common_id) &&
                    !$toUpdate->pluck('common_id')->contains($pendingId->common_id)) {
                        $toDelete->push($pendingId->id);
                }
            }


            Transaction::forceDestroy($toDelete);

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
