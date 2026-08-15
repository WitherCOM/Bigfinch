<?php

namespace App\Filament\Forms\Components;

use App\Models\Transaction;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Auth;

class ContextTextInput extends TextInput
{
    protected function setUp(): void
    {
        parent::setUp();
        $user = Auth::user();
        if (! is_null($user)) {
            $dataList = Transaction::query()
                ->where('user_id', $user->id)
                ->whereNull('deleted_at')
                ->distinct()
                ->pluck($this->getName());
            $this->datalist($dataList);
        }
    }
}
