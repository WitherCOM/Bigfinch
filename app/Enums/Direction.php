<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Direction: string implements HasLabel
{
    case INCOME = 'income';
    case EXPENSE = 'expense';
    case INTERNAL = 'internal';
    case INVEST = 'invest';

    public function getLabel(): ?string
    {
        return $this->value;
    }
}
