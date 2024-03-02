<?php

namespace App\Transformers;

interface BaseTransformerContract{
    public function toArray();   
    public function toJson();   
}