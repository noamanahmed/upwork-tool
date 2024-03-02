<?php

namespace App\Services;

interface UserSettingServiceContract{
    public function settings();
    public function updateSettings($validatedRequest);
}
