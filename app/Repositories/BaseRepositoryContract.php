<?php

namespace App\Repositories;

interface BaseRepositoryContract{
    public function index();
    public function get($id);
    public function getQueryBuilder();
    public function store($array);
    public function update($id,$array);
    public function destory($id);
    public function destroyMulti($array);
}
