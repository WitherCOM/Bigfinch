<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\CompleteTransactions;
use Filament\Pages\Dashboard;

class MyDashboard extends Dashboard
{
    public function getWidgets(): array
    {
        return [
            CompleteTransactions::class
        ];
    }
}
