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

class AuthController extends BaseController
{
    public function __construct(
        private AuthService $authService
    ){}

    /**
     * Display a listing of the resource.
     */
    public function login(LoginRequest $request)
    {
        return $this->authService->login($request->validated());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function register(RegisterUserRequest $request)
    {
        return $this->authService->register($request->validated());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function verifyEmail(VerifyEmailRequest $request)
    {
        return $this->authService->verifyEmail($request->validated());
    }

    public function resendVerificationEmail(ResendVerificationEmailRequest $request)
    {
        return $this->authService->resendVerificationEmail($request->validated());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function forgotPassword(ForgotPasswordRequest $request)
    {
        return $this->authService->forgotPassword($request->validated());
    }

    /**
     * Show the form for creating a new resource.
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        return $this->authService->resetPassword($request->validated());
    }

}
