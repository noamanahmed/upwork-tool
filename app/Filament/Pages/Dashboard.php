<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    protected static ?string $navigationIcon = 'heroicon-o-home';

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\StatsOverview::class,
            \App\Filament\Widgets\JobsChart::class,
            \App\Filament\Widgets\JobTypesChart::class,
            \App\Filament\Widgets\TopSearchesTable::class,
            \App\Filament\Widgets\RecentJobsTable::class,
        ];
    }
}
