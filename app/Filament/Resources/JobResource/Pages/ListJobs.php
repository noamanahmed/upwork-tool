<?php

namespace App\Filament\Resources\JobResource\Pages;

use App\Filament\Resources\JobResource;
use App\Models\JobSearch;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListJobs extends ListRecords
{
    protected static string $resource = JobResource::class;

    public function getTabs(): array
    {
        $tabs = [
            'all' => Tab::make('All'),
        ];

        $searches = JobSearch::orderBy('name')->get();

        foreach ($searches as $search) {
            $tabs['search_' . $search->id] = Tab::make($search->name)
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('searches', fn ($q) => $q->where('job_searches.id', $search->id)))
                ->badge(fn () => $search->jobs()->count());
        }

        return $tabs;
    }
}
