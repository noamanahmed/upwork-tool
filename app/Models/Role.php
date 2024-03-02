<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role as ModelsRole;

class Role extends ModelsRole
{
    public $fillable = ['name','guard','priority'];

    const ADMIN = 'admin';
    const SUPER_ADMIN = 'super_admin';
    const READ_ONLY = 'read_only';
    const EMPLOYEE = 'employee';

    use HasFactory;

    public function getPrettyNameAttribute()
    {
        return str($this->name)->title()->replace('_',' ');
    }

    public function scopeAvailable($query){
        return $query->whereNotIn('name',[
            self::SUPER_ADMIN,
        ]);
    }
}
