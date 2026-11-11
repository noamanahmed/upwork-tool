<?php

namespace App\Filament\Resources\JobResource\Pages;

use App\Filament\Resources\JobResource;
use App\Models\JobSearch;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ListJobs extends ListRecords
{
    protected static string $resource = JobResource::class;

    public function getTabs(): array
    {
        $tabs = [
            'all' => Tab::make('All'),
        ];

        $searches = JobSearch::orderBy('name')->get();

        $counts = DB::table('job_searches_jobs_pivot')
            ->select('job_search_id', DB::raw('count(*) as aggregate'))
            ->groupBy('job_search_id')
            ->pluck('aggregate', 'job_search_id');

        foreach ($searches as $search) {
            $count = (int) ($counts[$search->id] ?? 0);

            $tabs['search_' . $search->id] = Tab::make($search->name)
                ->modifyQueryUsing(fn (Builder $query) => $query->whereHas('searches', fn ($q) => $q->where('job_searches.id', $search->id)))
                ->badge($count);
        }

        return $tabs;
    }
}
