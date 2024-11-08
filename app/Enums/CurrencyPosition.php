<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum CurrencyPosition: string implements HasLabel
{
    case PREFIX = 'prefix';
    case SUFFIX = 'suffix';

    public function getLabel(): ?string
    {
        return $this->value;
    }
}
