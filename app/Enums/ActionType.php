<?php

namespace App\Enums;

use Filament\Support\Contracts\HasLabel;

enum ActionType: string implements HasLabel
{
    case CREATE_CATEGORY = 'create-category';
    case EXCLUDE_TRANSACTION = 'exclude-transaction';

    public function getLabel(): ?string
    {
        return $this->value;
    }
}
