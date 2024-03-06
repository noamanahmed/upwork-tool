<?php

namespace App\Models;


class Setting extends BaseModel
{
    protected $fillable = [
        'name',
        'description',
    ];

    public static function insertOrUpdate($name,$value)
    {
        $setting = static::where('name',$name)->first();
        if(is_null($setting))
        {
            $setting = new static;
        }

        $setting->name = $name;
        $setting->value = $value;
        $setting->save();
        return $setting;
    }

}
