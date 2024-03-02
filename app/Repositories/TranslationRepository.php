<?php

namespace App\Repositories;

use App\Models\Translation;

class TranslationRepository extends BaseRepository{

    protected Array $filters = ['id','language_id','language.code','key'];
    protected Array $with = ['language'];

    public function __construct()
    {
        $this->model = new Translation();
        parent::__construct();
    }
    public function index()
    {
        $queryBuilder =  $this->getQueryBuilder();
        return $queryBuilder->get();
    }
}
