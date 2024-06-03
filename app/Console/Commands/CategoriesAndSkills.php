<?php

namespace App\Console\Commands;

use App\Services\CategoryService;
use App\Services\UpWorkService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CategoriesAndSkills extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upwork:categories-skills';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $categories= app(UpWorkService::class)->categories();
        app(CategoryService::class)->insertCategoriesFromApiResponse($categories);

        $maxRecords = 20000;
        $start = 0;
        $offset = 100;
        $skills = [];
        while($start < $maxRecords)
        {
            $this->line('Fetching Skills from '.$start);
            $cacheKey = 'upwork_skills_offset_'.$offset.'_start_'.$start;
            if(Cache::has($cacheKey))
            {
                $data = Cache::get($cacheKey);
            }else{
                $data = json_decode(app(UpWorkService::class)->skills($offset,$start)->getContent(),true);
                sleep(1);
                if(empty($data)) break;
                Cache::set($cacheKey,$data,3600);
            }

            if(empty($data)) break;
            $skills = [...$skills,...$data[0]];
            $start += $offset;
        }

        app(CategoryService::class)->insertSkillsFromApiResponse($skills);
    }
}
