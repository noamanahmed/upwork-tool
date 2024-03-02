<?php

namespace App\Repositories;

use App\Models\UserSetting;

class UserSettingRepository extends BaseRepository{

    public function __construct()
    {
        $this->model = new UserSetting();
        parent::__construct();
    }

    public function createDefaultSettings($user)
    {
        $settings = new UserSetting();
        $settings->user_id = $user->id;
        $settings->timezone = UserSetting::DEFAULT_TIMEZONE;
        $settings->language = UserSetting::DEFAULT_LANGUAGE;
        $settings->save();
    }

}
