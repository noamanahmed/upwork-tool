<?php

namespace App\Repositories;

use App\Models\Permission;

class PermissionRepository extends BaseRepository{

    protected Array $filters = ['id'];
    protected Array $searchableFilters = ['id'];
    protected Array $sorters = ['id'];
    protected Array $defaultDropdownFields = ['id'];


    public function __construct()
    {
        $this->model = new Permission();
        parent::__construct();
    }

    public function index()
    {
        $queryBuilder =  $this->getQueryBuilder();
        // All DB data is needed
        return $queryBuilder->get();
    }
}
