<?php

namespace App\Repositories;

use App\Models\UpworkAccount;

class UpworkAccountRepository extends BaseRepository{

    protected Array $filters = ['id'];
    protected Array $searchableFilters = ['id','name'];
    protected Array $sorters = ['id'];
    protected Array $defaultDropdownFields = ['id'];

    public function __construct()
    {
        $this->model = new UpworkAccount();
        parent::__construct();
    }
}
