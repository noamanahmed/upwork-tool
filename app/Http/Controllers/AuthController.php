<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    /**
     * Show login page.
     */
    public function showLogin()
    {
        return view('auth.login');
    }

    /**
     * Redirect to Auth0 for authentication.
     */
    public function redirectToAuth0()
    {
        return Socialite::driver('auth0')
            ->scopes(['openid', 'profile', 'email'])
            ->redirect();
    }

    /**
     * Handle Auth0 callback.
     */
    public function handleAuth0Callback(Request $request)
    {
        try {
            $auth0User = Socialite::driver('auth0')->user();

            // Find or create user
            $user = User::firstOrCreate(
                ['email' => $auth0User->getEmail()],
                [
                    'first_name' => $auth0User->user['first_name']  ?? 'Unknown',
                    'last_name' => $auth0User->user['last_name'] ?? 'Unknown',
                    'password' => bcrypt(Str::random(32)),
                    'is_verified' => true,
                ]
            );

            // Update existing user
            if (!$user->wasRecentlyCreated) {
                $user->update([
                    'first_name' => $auth0User->user['first_name']  ?? $user->first_name,
                    'last_name' => $auth0User->user['last_name'] ?? $user->last_name,
                    'is_verified' => true,
                ]);
            }

            Auth::login($user, true);
            $request->session()->regenerate();

            Log::info('User logged in via Auth0', [
                'user_id' => $user->id,
                'email' => $user->email,
                'auth0_id' => $auth0User->getId(),
            ]);

            return redirect()->intended('/jobs');

        } catch (\Exception $e) {
            Log::error('Auth0 callback error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return redirect('/login')->withErrors([
                'auth0' => 'Failed to authenticate with Auth0. Please try again.',
            ]);
        }
    }

    /**
     * Logout user.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/jobs');
    }
}
