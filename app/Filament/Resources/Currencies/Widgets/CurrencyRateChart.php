<?php

namespace App\Filament\Resources\Currencies\Widgets;

use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Database\Eloquent\Model;

class CurrencyRateChart extends ChartWidget
{
    protected ?string $heading = 'Rate';

    public ?Model $record = null;

    protected ?array $options = [
        'scales' => [
            'x' => [
                'ticks' => [
                    'minRotation' => 45,
                    'maxRotation' => 90
                ]
            ]
        ]
    ];

    protected function getData(): array
    {
        $rates = $this->record->rates->sortBy('created_at');
        return [
            'datasets' => [
                [
                    'label' => $this->record->name,
                    'data' => $rates->pluck('rate_to_huf')
                ]
            ],
            'labels' => $rates->pluck('created_at')->map(fn($date) => $date->format('Y-m-d'))
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
