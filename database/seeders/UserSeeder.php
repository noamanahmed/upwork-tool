<?php

namespace Database\Seeders;

use App\Enums\UserTypeEnum;
use App\Models\Role;
use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = new User();
        $user->first_name = 'Noaman';
        $user->last_name = 'Ahmed';
        $user->email = 'admin@noamanahmed.com';
        $user->email_verified_at = now();
        $user->password = bcrypt('password');
        $user->remember_token = Str::random(10);
        $user->type = UserTypeEnum::ADMIN;
        $user->save();
        $user->assignRole('admin');
        $userSettings = new UserSetting();
        $userSettings->user_id = $user->id;
        $userSettings->timezone = UserSetting::DEFAULT_TIMEZONE;
        $userSettings->language = UserSetting::DEFAULT_LANGUAGE;
        $userSettings->save();

        $user = new User();
        $user->first_name = 'Noaman';
        $user->last_name = 'Ahmed';
        $user->email = 'superadmin@noamanahmed.com';
        $user->email_verified_at = now();
        $user->password = bcrypt('password');
        $user->remember_token = Str::random(10);
        $user->type = UserTypeEnum::SUPER_ADMIN;
        $user->save();
        $user->assignRole('super_admin');
        $userSettings = new UserSetting();
        $userSettings->user_id = $user->id;
        $userSettings->timezone = UserSetting::DEFAULT_TIMEZONE;
        $userSettings->language = UserSetting::DEFAULT_LANGUAGE;
        $userSettings->save();

        $user = new User();
        $user->first_name = 'Noaman';
        $user->email = 'readonly@noamanahmed.com';
        $user->email_verified_at = now();
        $user->password = bcrypt('password');
        $user->remember_token = Str::random(10);
        $user->type = UserTypeEnum::READ_ONLY;
        $user->save();
        $user->assignRole('read_only');

        $userSettings = new UserSetting();
        $userSettings->user_id = $user->id;
        $userSettings->timezone = UserSetting::DEFAULT_TIMEZONE;
        $userSettings->language = UserSetting::DEFAULT_LANGUAGE;
        $userSettings->save();

        $user = new User();
        $user->first_name = 'Noaman';
        $user->last_name = 'Ahmed';
        $user->email = 'employee@noamanahmed.com';
        $user->email_verified_at = now();
        $user->password = bcrypt('password');
        $user->remember_token = Str::random(10);
        $user->type = UserTypeEnum::EMPLOYEE;
        $user->save();
        $user->assignRole('employee');
        $userSettings = new UserSetting();
        $userSettings->user_id = $user->id;
        $userSettings->timezone = UserSetting::DEFAULT_TIMEZONE;
        $userSettings->language = UserSetting::DEFAULT_LANGUAGE;
        $userSettings->save();

    }
}
