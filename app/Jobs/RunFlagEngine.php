<?php

namespace App\Jobs;

use App\Engine\FlagEngine;
use Illuminate\Bus\Batchable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;

class RunFlagEngine implements ShouldQueue
{
    use Batchable, Queueable;

    private Collection $transactions;

    /**
     * Create a new job instance.
     */
    public function __construct(Collection $transactions)
    {
        $this->transactions = $transactions;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $records = FlagEngine::run($this->transactions);
        foreach ($records as $record) {
            $record->save();
        }
    }
}
