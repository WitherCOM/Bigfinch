<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\CategoryAverage;
use App\Filament\Widgets\CompleteTransactions;
use App\Filament\Widgets\CurrentMonthCategoryPie;
use App\Filament\Widgets\MonthlyDistribution;
use App\Filament\Widgets\StatOverview;
use App\Filament\Widgets\CurrentOverview;
use App\Filament\Widgets\TendChart;
use Filament\Facades\Filament;
use Filament\Pages\Dashboard;

class MyDashboard extends Dashboard
{
    public function getWidgets(): array
    {
        return [
            StatOverview::class,
            CategoryAverage::class,
            CurrentMonthCategoryPie::class,
            MonthlyDistribution::class,
            TendChart::class,
            CompleteTransactions::class
        ];
    }
}
