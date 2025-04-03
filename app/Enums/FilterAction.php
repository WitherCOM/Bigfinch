<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum FilterAction: string implements HasLabel
{
    case EXCLUDE = 'exclude';
    case CATEGORIZE = 'categorize';

    public function getLabel(): ?string
    {
        return $this->value;
    }
}
