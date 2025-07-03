<?php

namespace App\Engine;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Nette\Utils\Json;
use OpenAI\Laravel\Facades\OpenAI;

class AICategorizeEngine
{
    private static function getAvailableCategoriesJsonString(User $user): string {
        return Json::encode($user->categories->map(function($category) {
            return [
                'id' => $category->id,
                'name' => $category->name
            ];
        })->toArray());
    }

    private static function transactionJsonString(Transaction $transaction): string {
        return Json::encode($transaction->toArray());
    }

    private static function composeAIPrompt(Transaction $transaction): string {
        $categoriesJson = self::getAvailableCategoriesJsonString($transaction->user);
        $transactionJson = self::transactionJsonString($transaction);
          return <<<EOD
            System: You are an expert categorizer algorithm.
            You take a json array that contains category names and ids. It looks like this:
            [ {"id": "f81d4fae-7dec-11d0-a765-00a0c91e6bf6", "name": "Category name" } ]
            And you take a json object that contains information about the transaction.
            You have to choose exactly one category from the categories array, that matches the best
            the given transaction. Is it possible that it does not match any categories, than return empty string.
            Return the id of the category only.
            Example response: f81d4fae-7dec-11d0-a765-00a0c91e6bf6
            Human:
                Categories: $categoriesJson
                Transaction: $transactionJson
          EOD;
    }

    public static function getCategory(Transaction $transaction): string {
        $completion = OpenAI::completions()->create([
            'model' => 'gpt-4o-mini',
            'prompt' => self::composeAIPrompt($transaction),
        ]);
        return $completion['choices'][0]['name'];
    }


}
