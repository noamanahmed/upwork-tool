<?php

namespace App\Repositories;

use App\Models\Language;

class LanguageRepository extends BaseRepository{

    protected Array $filters = ['id'];
    protected Array $searchableFilters = ['id'];
    protected Array $sorters = ['id'];
    protected Array $defaultDropdownFields = ['id'];
    protected Array $scopes = ['active'];

    public function __construct()
    {
        $this->model = new Language();
        parent::__construct();
    }
}
