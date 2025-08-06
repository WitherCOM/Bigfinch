<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Direction: string implements HasLabel
{
    case INCOME = 'income';
    case EXPENSE = 'expense';
    case INTERNAL_FROM = 'internal_from';
    case INTERNAL_TO = 'internal_to';
    case EXCHANGE_FROM = 'exchange_from';
    case EXCHANGE_TO = 'exchange_to';
    case INVESTMENT = 'investment';

    public function getLabel(): ?string
    {
        return $this->value;
    }
}
