<?php

namespace Tests\Factories;

use App\Models\User;
use App\Models\UserSetting;

trait UserFactory {
    static $user = null;

    const PASSWORD = '5/I9}!a42YbtUdQT';
    const PASSWORD_UPDATED = '5/I9}!a42YbtUdQT99)';

    function createUser()
    {
        static::$user = User::factory()->create([
            'password' => bcrypt(self::PASSWORD),
        ]);
        static::$user->assignRole('super_admin');
        return static::$user;
    }

    function makeUser()
    {
        static::$user = User::factory()->make([
            'password' => bcrypt(self::PASSWORD),
        ]);
        return static::$user;
    }
    function createUserSettings()
    {
        $userSettings = new UserSetting();
        $userSettings->user_id = static::$user->id;
        $userSettings->timezone = UserSetting::DEFAULT_TIMEZONE;
        $userSettings->language = UserSetting::DEFAULT_LANGUAGE;
        $userSettings->save();
    }
    public function getFirstName(){
        return static::$user->first_name;
    }

    public function getLastName(){
        return static::$user->last_name;
    }

    public function getEmail(){
        return static::$user->email;
    }

    public function getPassword(){
        return static::PASSWORD;
    }

    public function getNewPassword(){
        return static::PASSWORD_UPDATED;
    }

    public function getUserSettingTimezone(){
        return static::$user->settings->timezone;
    }

    public function getUserSettingLanguage(){
        return static::$user->settings->language;
    }

    public function getToken(){
        return self::$user->createToken(USER::DEVICE_NAME)->plainTextToken;
    }
}
