<?php

namespace App\Repositories;

use App\Enums\UserTypeEnum;
use App\Models\User;

class UserRepository extends BaseRepository{

    protected Array $sorters = ['id','first_name','last_name','email'];
    protected Array $defaultDropdownFields = ['id','first_name','last_name'];
    protected Array $searchableFilters = ['first_name','last_name','email'];
    protected Array $with = ['roles'];

    public function __construct()
    {
        $this->model = new User();
        parent::__construct();
    }
    public function store($validatedRequest)
    {
        $this->model->fill($validatedRequest)->save();
        $this->model->refresh();
        return $this->model;
    }

}
