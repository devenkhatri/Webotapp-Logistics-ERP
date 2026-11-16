<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();
            if ($user->isDeliveryAgent()) {
                return redirect()->intended(route('agent.portal'));
            }
            if ($user->isCustomer()) {
                return redirect()->intended(route('merchant.portal'));
            }

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function quickLogin($role)
    {
        $email = match ($role) {
            'admin' => 'admin@courierx.com',
            'operations' => 'operations@courierx.com',
            'rider' => 'rider1@courierx.com',
            'accountant' => 'finance@courierx.com',
            'merchant' => 'merchant@fashionnova.demo',
            default => 'admin@courierx.com',
        };

        $user = User::where('email', $email)->first();
        if ($user) {
            Auth::login($user);
            if ($user->isDeliveryAgent()) {
                return redirect()->route('agent.portal');
            }
            if ($user->isCustomer()) {
                return redirect()->route('merchant.portal');
            }
            return redirect()->route('dashboard');
        }

        return redirect()->route('login');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
