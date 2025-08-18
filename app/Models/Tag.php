<?php

namespace App\Models;

use App\Enums\Direction;
use Illuminate\Database\Eloquent\Model;
use Sushi\Sushi;

class Tag extends Model
{
    use Sushi;

    public function getRows()
    {
        $displayCurrencies = User::all()->mapWithKeys(fn (User $user) => [$user->id => Currency::find($user->default_currency_id)]);
        $transactions = Transaction::with('currency')
            ->where('direction', Direction::EXPENSE->value)
            ->select(['user_id', 'currency_id', 'value', 'tags', 'date'])
            ->get();
        $tagsPerUserId = $transactions->groupBy('user_id')
            ->map(function ($transactions, $userId) {
                return [
                    'user_id' => $userId,
                    'tags' => $transactions->flatMap(fn (Transaction $transaction) => $transaction->tags)->unique()
                ];
            });

        return $tagsPerUserId->reduce(function ($tags, $item) use ($transactions, $displayCurrencies) {
            foreach ($item['tags'] as $tag) {
                $tags[] = [
                    'user_id' => $item['user_id'],
                    'tag' => $tag,
                    'value' => $transactions
                        ->filter(fn (Transaction $transaction) => collect($transaction->tags)->contains($tag))
                    ->sum(fn (Transaction $transaction) => $transaction->currency->nearestRate($transaction->date) * $transaction->value / $displayCurrencies[$item['user_id']]->nearestRate($transaction->date))
                ];
            }
            return $tags;
        },[]);
    }
}
