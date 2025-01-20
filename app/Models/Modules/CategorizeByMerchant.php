<?php

namespace App\Models\Modules;

use App\Enums\Direction;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Collection;

class CategorizeByMerchant implements ModuleInterface
{
    public function getName(): string
    {
        return __("Categorize by merchant");
    }

    public function before(Collection $transactions, User $user): Collection
    {
        $merchants = $user->merchants;
        return $transactions->map(function (array $transaction) use ($merchants) {
            $merchant = $merchants->where('id', $transaction['merchant_id'])->first();
            $transaction['category_id'] = $transaction['direction'] === Direction::EXPENSE->value ? $merchant->expense_category_id : $merchant->income_category_id;
            return $transaction;
        });
    }

    public function after(User $user): void
    {
        // TODO: Implement after() method.
    }
}
