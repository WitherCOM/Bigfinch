<?php

namespace App\Jobs;

use App\Engine\DynamicEngine;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Collection;

class RunDynamicEngine implements ShouldQueue
{
    use Queueable;

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
        $records = DynamicEngine::run($this->transactions);
        foreach ($records as $record) {
            $record->save();
        }
    }
}
