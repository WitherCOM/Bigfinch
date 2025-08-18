<?php

namespace App\Filament\Forms\Components\Traits;
use Filament\Support\Concerns\CanBeCopied;

trait Copyable
{
    use CanBeCopied;

    public function getCopyableState(): ?string
    {
        $state = $this->getState();

        return $state == 'null' ? null : $state;
    }
}
