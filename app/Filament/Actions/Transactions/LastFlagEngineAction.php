<?php

namespace App\Filament\Actions\Transactions;

use App\Engine\FlagEngine;
use Filament\Actions\Action;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class LastFlagEngineAction extends Action
{
    public function setUp(): void
    {
        parent::setUp();
        $this->icon('');
        $this->action(function () {
            $records = Auth::user()->transactions()->where('date','>=', Carbon::now()->subDays(config('app.retro_days')))->get();
            $records = FlagEngine::run($records);
            foreach ($records as $record) {
                if ($record->isDirty())
                {
                    $record->save();
                }
            }
        });
    }
}
