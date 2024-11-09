<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum RuleType: string implements HasLabel
{
    case CATEGORY = 'category';
    case EXCLUDE = 'exclude';

    public function getLabel(): ?string
    {
        return $this->value;
    }
}
