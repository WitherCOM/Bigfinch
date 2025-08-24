<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum NavGroup: string implements HasLabel
{
    case TRANSACTIONS = 'Transactions';
    case SETTINGS = 'Settings';
    case STATISTICS = 'Statistics';

    public function getLabel(): string|Htmlable|null
    {
        return __($this->value);
    }
}
