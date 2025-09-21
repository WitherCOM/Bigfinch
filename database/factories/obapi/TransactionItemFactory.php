<?php

namespace Database\Factories\obapi;

use App\Models\Currency;
use Faker\Generator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class TransactionItemFactory
{
    protected $isBooked = true;
    public function __construct(protected Generator $faker)
    {

    }

    public static function new($isBooked = true): self
    {
        $instance = app(self::class);
        $instance->isBooked = $isBooked;
        return $instance;
    }

    public function make(array $overrides = []): array
    {
        $shouldDateTime = $this->faker->boolean;
        $isExpense = $this->faker->boolean;
        $isUnstructuredAnArray = $this->faker->boolean;
        $hasInternalTransaction = $this->faker->boolean;
        return array_merge([
            "transactionId" => Str::uuid()->toString(),
            ...($this->isBooked && !$shouldDateTime ? ["bookingDate" => $this->faker->date] : []),
            ...($this->isBooked && $shouldDateTime ? ["bookingDateTime" =>  Carbon::instance($this->faker->dateTime)->toISOString()] : []),
            ...(!$shouldDateTime ? ["valueDate" => $this->faker->date] : []),
            ...($shouldDateTime ? ["valueDateTime" => Carbon::instance($this->faker->dateTime)->toISOString()] : []),
            "transactionAmount" => [
                "amount" => ($isExpense ? -1 : 1) * $this->faker->randomNumber(),
                "currency" => Currency::all()->random()->iso_code,
            ],
            "debtorName" => $this->faker->company,
            "debtorAccount" => [],
            ...(!$isUnstructuredAnArray ? ["remittanceInformationUnstructured" => $this->faker->text(50)] : []),
            ...($isUnstructuredAnArray ? ["remittanceInformationUnstructuredArray" => collect(range(1, $this->faker->randomNumber(1)))->map(fn ($i) => $this->faker->text(50))] : []),
            "proprietaryBankTransactionCode" => collect(["TRANSFER"])->random(),
            ...($hasInternalTransaction ? ["internalTransactionId" => Str::uuid()->toString()] : []),
        ], $overrides);
    }
    public function many(int $count, array $overrides = []): array
    {
        return collect(range(1, $count))
            ->map(fn() => $this->make($overrides))
            ->all();
    }
}
