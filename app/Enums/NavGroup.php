<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum NavGroup: string implements HasLabel
{
    case TRANSACTIONS = 'Transactions';
    case MANAGEMENT = 'Management';
    case ANALYTICS = 'Analytics';
    case ADMIN = 'Admin';

    public function getLabel(): string|Htmlable|null
    {
        return __($this->value);
    }
}
