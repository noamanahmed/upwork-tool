<?php

namespace App\Repositories;

use App\Models\Region;

class RegionRepository extends BaseRepository{

    protected Array $filters = ['id'];
    protected Array $searchableFilters = ['id','name'];
    protected Array $sorters = ['id'];
    protected Array $defaultDropdownFields = ['id'];

    public function __construct()
    {
        $this->model = new Region();
        parent::__construct();
    }
}
