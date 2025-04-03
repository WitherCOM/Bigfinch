<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum Flag: string implements HasLabel
{
    case INTERNAL_TRANSACTION = 'internal-transaction';
    case INVESTMENT = 'investment';
    case EXCHANGE = 'exchange';

    public function getLabel(): ?string
    {
        return $this->value;
    }
}
