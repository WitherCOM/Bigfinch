<?php

namespace App\Filament\Infolists\Components;

use App\Filament\Forms\Components\Traits\Copyable;
use Filament\Infolists\Components\ViewEntry;
use Illuminate\Contracts\Support\Jsonable;
use stdClass;

class PrettyJsonEntry extends ViewEntry
{
    use Copyable;

    protected string $view = 'filament.infolists.components.pretty-json-entry';

    public function getState(): mixed
    {
        $state = parent::getState();

        if ($state === 'null') {
            return null;
        }

        if ($state instanceof Jsonable) {
            return $state->toJson();
        }

        if (is_array($state) || $state instanceof StdClass) {
            return json_encode($state);
        }

        return $state;
    }
}
