<?php

namespace App\Models\Modules;

use App\Enums\Direction;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

class ExcludeInternalTransactions implements ModuleInterface
{
    public function getName(): string
    {
        return __("Exclude internal transactions");
    }
    public function before(Collection $transactions, User $user): Collection
    {
        $dateSorted = $transactions->sortBy('date');
        for ($i = 0; $i < count($dateSorted) - 1; $i++) {
            if ($dateSorted[$i]['date'] - $dateSorted[$i+1]['date'] < 1) {
                $dateSorted[$i]['deleted_at'] = Carbon::now();
                $dateSorted[$i+1]['deleted_at'] = Carbon::now();
            }
        }
        return $dateSorted;
    }

    public function after(User $user): void
    {
        // TODO: Implement after() method.
    }
}
