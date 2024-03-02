<?php

namespace App\Services;

use App\Enums\UserStatusEnum;
use App\Enums\UserTypeEnum;
use App\Models\Role;
use App\Models\User;
use App\Notifications\ResetPassword;
use App\Repositories\UserRepository;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthService extends BaseService{

    public function __construct(){
        $this->repository = new UserRepository();
    }

    public function login($validatedResponseArray)
    {
        $email = $validatedResponseArray['email'] ?? null;
        $password = $validatedResponseArray['password'] ?? null;
        if(is_null($email) || is_null($password)) return $this->apiResponseWithAuthenticationFailedError([]);

        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            return $this->apiResponseWithAuthenticationFailedError([
                'message' => 'The provided credentials are incorrect.',
                'errors' => [
                    'email' => 'The provided credentials are incorrect.'
                ],
            ]);
        }

        if($user->status === UserStatusEnum::BLOCKED->value) return $this->apiResponseWithAuthenticationFailedError([
            'message' => 'This user is banned. Please contact support.',
            'errors' => [
                'email' => 'This user is banned. Please contact support'
            ]
        ]);

        $response = [
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'email' => $user->email,
            'token' =>  $user->createToken(USER::DEVICE_NAME)->plainTextToken,
            'roles' => $user->roles->pluck('name'),
            'type' => UserTypeEnum::getPrettyKeyfromValue($user->type),
            'permissions' => $user->getPermissionsViaRoles()->pluck('name'),
            'settings' => $user->settings,
            'is_email_verified' => $user->hasVerifiedEmail()
        ];

        return $this->successfullApiResponse($response);
    }
    public function register($validatedResponseArray)
    {
        $email = $validatedResponseArray['email'] ?? null;
        $password = $validatedResponseArray['password'] ?? null;
        if(is_null($email) || is_null($password)) return $this->apiResponseWithValidationErrors([]);

        $first_name = $validatedResponseArray['first_name'] ?? null;
        $last_name = $validatedResponseArray['last_name'] ?? null;

        $user = User::create([
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'password' => Hash::make($password),
            'email_verification_token' => Hash::make(Str::random(32)),
            'email_verification_sent_at' => now()
        ]);

        $user->refresh();
        // Assign read only role by default.
        $user->assignRole(Role::READ_ONLY);

        app(UserSettingService::class)->createDefaultSettings($user);

        $response = [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $email,
            'token' =>  $user->createToken(USER::DEVICE_NAME)->plainTextToken,
            'type' => UserTypeEnum::getPrettyKeyfromValue($user->type),
            'roles' => $user->roles->pluck('name'),
            'permissions' => $user->getPermissionsViaRoles()->pluck('name'),
            'settings' => $user->settings,
            'is_email_verified' => $user->hasVerifiedEmail()
        ];

        // Dispatch all events
        event(new Registered($user));

        return $this->successfullApiResponse($response);

    }
    public function resendVerificationEmail($validatedResponseArray)
    {
        $user = Auth::user();
        if($user->hasVerifiedEmail()) return $this->apiResponseWithValidationErrors([
            'email' => 'This email address is already verified'
        ]);
        if(empty($user->email_verification_token))
        {
            $user->email_verification_token = Hash::make(Str::random(32));
            $user->save();
        }

        $user->sendEmailVerificationNotification();
        return $this->successfullApiResponse([]);
    }
    public function verifyEmail($validatedResponseArray)
    {
        $token = $validatedResponseArray['token'] ?? null;
        if(is_null($token)) return $this->apiResponseWithValidationErrors([]);
        $user = User::where('email_verification_token',$token)->first();
        $authenticatedUser = Auth::user();
        if(is_null($user) || $authenticatedUser->id !== $user->id) return $this->apiResponseWithAuthenticationFailedError([
            'token' => 'We cannot verify the email with this token!'
        ]);
        if(!is_null($user->email_verified_at)) return $this->apiResponseWithValidationErrors([
            'token' => 'This user has already verified it\'s email'
        ]);

        if($user->status !== UserStatusEnum::EMAIL_UNVERIFIED->value) return $this->apiResponseWithValidationErrors([
            'email' => 'This user status is NOT email unverifed so it cannot be set to active.'
        ]);

        $user->email_verified_at = now();
        $user->email_verification_token = null;
        $user->status = UserStatusEnum::ACTIVE;
        $user->save();

        return $this->successfullApiResponse([]);

    }
    public function forgotPassword($validatedResponseArray)
    {
        $email = $validatedResponseArray['email'] ?? null;
        if(is_null($email)) return $this->apiResponseWithValidationErrors([
            'email' => 'Please provide a valid email'
        ]);

        $user = User::where('email',$email)->first();
        // Send 200 response even if we didn't find the email address to prevent hackers to find emails
        if(is_null($user)) return $this->successfullApiResponse([]);

        Password::sendResetLink(['email' => $user->email]);

        return $this->successfullApiResponse([]);
    }
    public function resetPassword($validatedResponseArray)
    {
        $token = $validatedResponseArray['token'] ?? null;
        $email = $validatedResponseArray['email'] ?? null;
        if(is_null($token) || is_null($email)) return $this->apiResponseWithValidationErrors([
            'password' => 'Please provide a valid token to reset your password',
            'email' => 'Please provide a valid email to reset your password',
        ]);

        $status = Password::reset(
            $validatedResponseArray,
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));
                $user->save();
                event(new PasswordReset($user));
            }
        );

        if($status === Password::PASSWORD_RESET) return $this->successfullApiResponse([]);

        return $this->apiResponseWithValidationErrors([
            'password' => __($status)
        ]);
    }
}
