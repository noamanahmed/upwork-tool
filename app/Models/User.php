<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enums\UserTypeEnum;
use App\Notifications\ResetPassword;
use App\Notifications\VerifyEmail;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use phpDocumentor\Reflection\Types\Boolean;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmail,CanResetPassword
{
    protected $guard = 'api';

    const DEVICE_NAME = 'web';

    use HasApiTokens,HasRoles, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'type',
        'job_type',
        'email',
        'password',
        'phone',
        'address',
        'city',
        'state',
        'country',
        'working_hours',
        'email_verification_token',
        'email_verification_sent_at',
        'employer_id',
        'description',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'working_hours' => 'array'
    ];

    public function settings()
    {
        return $this->hasOne(UserSetting::class);
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class,'employees_skills');
    }
    public function hasPermissionToAccessRoute($routeName)
    {
        $permissions = $this->getPermissionsViaRoles()->pluck('name')->map(function($default) {
            $underscoreIndex = strripos($default, '_');
            $action = substr($default, $underscoreIndex + 1);
            $subject = substr($default, 0, $underscoreIndex);
            return $action . '.' . $subject;

        });
        return $permissions->contains($routeName) || $this->hasPermissionToAccessRouteWithAlternativeName($routeName,$permissions);
    }
    public function hasPermissionToAccessRouteWithAlternativeName($routeName,$permissions) : bool
    {
        if(strrpos($routeName,'store'))
        {
            $routeName = str_replace('store','create',$routeName);
            if($permissions->contains($routeName)) return true;
        }
        if(strrpos($routeName,'destroy'))
        {
            $routeName = str_replace('destroy','delete',$routeName);
            if($permissions->contains($routeName)) return true;
        }
        if(strrpos($routeName,'show'))
        {
            $routeName = str_replace('show','get',$routeName);
            if($permissions->contains($routeName)) return true;
        }
        if(strrpos($routeName,'dropdown'))
        {
            $routeName = str_replace('dropdown','index',$routeName);
            if($permissions->contains($routeName)) return true;
        }
        if(strrpos($routeName,'indexForStatus'))
        {
            $routeName = str_replace('indexForStatus','index',$routeName);
            if($permissions->contains($routeName)) return true;
        }
        return false;
    }
    public function getRole()
    {
        $roles = $this->getRoleNames(); // Get all roles assigned to the user
        return $roles->isNotEmpty() ? $roles[0] : null; // Return the first role or null if no role is assigned
    }

    public function getRoleId()
    {
        $roles = $this->getRoleNames(); // Get all roles assigned to the user
        return $roles->isNotEmpty() ? $this->roles->first()->id : null; // Return the ID of the first role or null if no role is assigned
    }
    public function getPriority()
    {
        $maxPriority = -1;
        foreach($this->roles as $role)
        {
            $maxPriority = max($role->priority,$maxPriority);
        }

        return $maxPriority;
    }

    public function scopeIsEmployee($query)
    {
        return $query->where('type',UserTypeEnum::EMPLOYEE);
    }
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmail());
    }
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token,$this->email));
    }
}
