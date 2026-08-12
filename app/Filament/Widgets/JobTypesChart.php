<?php

namespace App\Filament\Widgets;

use App\Models\Job;
use Filament\Widgets\ChartWidget;

class JobTypesChart extends ChartWidget
{
    protected static ?string $heading = 'Job Types Distribution';

    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $hourly = Job::where('is_hourly', true)->count();
        $fixed = Job::where('is_hourly', false)->count();

        return [
            'datasets' => [
                [
                    'label' => 'Job Types',
                    'data' => [$hourly, $fixed],
                    'backgroundColor' => [
                        'rgb(14, 165, 233)',
                        'rgb(34, 197, 94)',
                    ],
                    'borderColor' => [
                        'rgb(14, 165, 233)',
                        'rgb(34, 197, 94)',
                    ],
                ],
            ],
            'labels' => ['Hourly', 'Fixed Rate'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }
}
