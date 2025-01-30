<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Direction: string implements HasLabel
{
    case INCOME = 'income';
    case EXPENSE = 'expense';

    public function getLabel(): ?string
    {
        return $this->value;
    }
}
