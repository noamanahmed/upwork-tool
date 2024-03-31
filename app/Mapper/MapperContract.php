<?php

namespace App\Mapper;

use Illuminate\Database\Eloquent\Model;

interface MapperContract{
    public function mapFromObject($data,Model &$model);
    public function mapFromArray($data,Model &$model);
}
