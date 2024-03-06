<?php

namespace App\Repositories;

use App\Models\Job;

class JobRepository extends BaseRepository{

    protected Array $filters = ['id'];
    protected Array $searchableFilters = ['id','name'];
    protected Array $sorters = ['id'];
    protected Array $defaultDropdownFields = ['id'];

    public function __construct()
    {
        $this->model = new Job();
        parent::__construct();
    }
}
