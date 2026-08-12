<?php

namespace App\Filament\Widgets;

use App\Models\Job;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class JobsChart extends ChartWidget
{
    protected static ?string $heading = 'Jobs Fetched (Last 14 Days)';

    protected static ?int $sort = 1;

    protected function getData(): array
    {
        $data = Job::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as count')
        )
            ->where('created_at', '>=', now()->subDays(14))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Jobs',
                    'data' => $data->pluck('count')->toArray(),
                    'fill' => 'start',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'borderColor' => 'rgb(59, 130, 246)',
                ],
            ],
            'labels' => $data->pluck('date')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
