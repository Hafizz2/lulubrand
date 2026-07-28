<?php

namespace App\Http\Controllers\Storefront\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\CustomerLoginRequest;
use App\Http\Requests\Storefront\CustomerRegisterRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CustomerAuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return redirect()->route('storefront.home');
        }

        return view('storefront.auth.login');
    }

    public function login(CustomerLoginRequest $request)
    {
        $credentials = $request->validated();

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('storefront.home'))
                ->with('success', 'Welcome back to LULU!');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function showRegisterForm()
    {
        if (Auth::check()) {
            return redirect()->route('storefront.home');
        }

        return view('storefront.auth.register');
    }

    public function register(CustomerRegisterRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'role' => 'customer',
            'password' => Hash::make($validated['password']),
        ]);

        Auth::login($user);

        return redirect()->route('storefront.home')
            ->with('success', 'Your account has been created successfully!');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('storefront.home')
            ->with('success', 'Signed out successfully.');
    }
}
