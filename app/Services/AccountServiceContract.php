<?php

namespace App\Services;

interface AccountServiceContract{
    public function profile();
    public function updateProfile($validatedRequest);
}
