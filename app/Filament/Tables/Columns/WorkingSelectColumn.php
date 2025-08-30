<?php

namespace App\Filament\Tables\Columns;

use BackedEnum;
use Filament\Tables\Columns\Column;
use Filament\Tables\Columns\SelectColumn;
use Illuminate\Validation\Rule;

class WorkingSelectColumn extends SelectColumn
{
    public function getRules(): array
    {
        $optionLabel = $this->getOptionLabel(withDefault: false);

        if (blank($optionLabel)) {
            return [
                ...$this->getBaseRules(),
                Rule::in(array_keys($this->getOptions())),
            ];
        }

        $state = $this->getState();

        if ($state instanceof BackedEnum) {
            $state = $state->value;
        }

        if ($this->hasDisabledOptions() && $this->isOptionDisabled($state, $optionLabel)) {
            return [
                ...$this->getBaseRules(),
                Rule::in([]),
            ];
        }

        return $this->getBaseRules();
    }
}
