<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Exception;

class SocialAuthController extends Controller
{
    // Google
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $socialUser = Socialite::driver('google')->user();
            $user = $this->findOrCreateUser($socialUser, 'google');
            Auth::login($user, true);
            return redirect()->intended('/home');
        } catch (Exception $e) {
            return redirect('/login')->with('error', 'Error autenticando con Google: ' . $e->getMessage());
        }
    }

    // Microsoft
    public function redirectToMicrosoft()
    {
        return Socialite::driver('microsoft')->redirect();
    }

    public function handleMicrosoftCallback()
    {
        try {
            $socialUser = Socialite::driver('microsoft')->user();
            $user = $this->findOrCreateUser($socialUser, 'microsoft');
            Auth::login($user, true);
            return redirect()->intended('/home');
        } catch (Exception $e) {
            return redirect('/login')->with('error', 'Error autenticando con Microsoft: ' . $e->getMessage());
        }
    }

    // Utilidad para encontrar o crear usuario
    protected function findOrCreateUser($socialUser, $provider)
    {
        $user = User::where('email', $socialUser->getEmail())->first();
        if ($user) {
            // Asegurar que tenga el rol 'cliente'
            if (!$user->hasRole('cliente')) {
                $user->syncRoles(['cliente']);
            }
            return $user;
        }
        // Crear usuario nuevo y asignar rol 'cliente'
        $user = User::create([
            'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'Usuario ' . ucfirst($provider),
            'email' => $socialUser->getEmail(),
            'password' => Hash::make(Str::random(16)),
        ]);
        $user->assignRole('cliente');
        return $user;
    }
}
