<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class UserRegistrationsChart extends ChartWidget
{
    protected static ?string $heading = 'User Registrations';

    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = [
        'xl' => 12,
    ];

    protected static ?string $maxHeight = '320px';

    protected function getOptions(): array
    {
        return [
            'maintainAspectRatio' => false,
            'plugins' => [
                'legend' => [
                    'position' => 'bottom',
                ],
            ],
            'scales' => [
                'x' => [
                    'grid' => [ 'display' => false ],
                ],
                'y' => [
                    'grid' => [ 'display' => true ],
                    'ticks' => [ 'precision' => 0 ],
                ],
            ],
        ];
    }

    protected function getData(): array
    {
        $data = Trend::model(User::class)
            ->between(
                start: now()->subMonths(6),
                end: now(),
            )
            ->perMonth()
            ->count();

        return [
            'datasets' => [
                [
                    'label' => 'User registrations',
                    'data' => $data->map(fn (TrendValue $value) => $value->aggregate),
                    'backgroundColor' => '#10b981',
                    'borderColor' => '#059669',
                ],
            ],
            'labels' => $data->map(fn (TrendValue $value) => $value->date),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
