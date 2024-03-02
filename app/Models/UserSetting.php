<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSetting extends Model
{
    public $fillable = ['timezone','language'];
    use HasFactory;

    const LANGUAGE = 'language';
    const TIMEZONE = 'timezone';

    const DEFAULT_LANGUAGE = 'nl';
    const DEFAULT_TIMEZONE = 'UTC';
}
