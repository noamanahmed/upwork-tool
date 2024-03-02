<?php

namespace App\Repositories;

use App\Models\Role;

class RoleRepository extends BaseRepository{

    protected Array $filters = ['id'];
    protected Array $searchableFilters = ['id','name'];
    protected Array $sorters = ['id','name'];
    protected Array $defaultDropdownFields = ['id','name'];
    protected Array $scopes = ['available'];


    public function __construct()
    {
        $this->model = new Role();
        $this->model->guard_name = 'web';
        parent::__construct();
    }

}
