<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterUserRequest;
use App\Http\Requests\ResendVerificationEmailRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\StoreLeadRequest;
use App\Http\Requests\UpdateLeadRequest;
use App\Http\Requests\VerifyEmailRequest;
use App\Models\Lead;
use App\Services\AuthService;
use App\Services\TranslationService;

class TranslationController extends BaseController
{
    public function __construct(
        private TranslationService $translationService
    ){}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return $this->translationService->index();
    }

}
