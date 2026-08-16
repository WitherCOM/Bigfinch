<?php

namespace App\Models;

use App\Enums\Direction;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Sushi\Sushi;

class Tag extends Model
{
    use Sushi;

    public function getRows()
    {
        $user = Auth::user();
        if (is_null($user)) {
            return [];
        }
        $displayCurrency = Currency::find($user->default_currency_id);
        $transactions = Transaction::with('currency')
            ->where('user_id', $user->id)
            ->where('direction', Direction::EXPENSE->value)
            ->select(['user_id', 'currency_id', 'value', 'tags', 'date'])
            ->get();
        return $transactions
            ->flatMap(fn (Transaction $transaction) => collect($transaction->tags)->map(fn ($tag) => ['tag' => $tag, 'transaction' => $transaction]))
            ->groupBy('tag')
            ->map(fn (Collection $entries, $tag) => [
                'tag' => $tag,
                'last_seen' => $entries->pluck('transaction')->max('date'),
                'value' => $entries->pluck('transaction')->sum(
                    fn (Transaction $transaction) => $transaction->currency->nearestRate($transaction->date) * $transaction->value / $displayCurrency->nearestRate($transaction->date)
                ),
            ])
            ->values()
            ->toArray();
    }
}
