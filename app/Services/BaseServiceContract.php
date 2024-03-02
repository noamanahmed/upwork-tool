<?php

namespace App\Services;

interface BaseServiceContract{
    public function index();
    public function dropdown();
    public function dropdownForStatus();
    public function get($id);
    public function store($array);
    public function update($id,$array);
    public function destory($id);
    public function destroyMulti($array);
}
