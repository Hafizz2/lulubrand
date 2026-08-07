<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminLoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class LoginController extends Controller
{
    public function showLoginForm(): Response
    {
        if (Auth::check() && Auth::user()->isStaff()) {
            return Inertia::render('Dashboard');
        }

        return Inertia::render('Auth/Login');
    }

    public function login(AdminLoginRequest $request)
    {
        $credentials = $request->validated();

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            if (! $user->isStaff()) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Access denied. You do not have admin/staff privileges.',
                ]);
            }

            $request->session()->regenerate();

            if ($user->isCashier()) {
                return redirect()->intended(route('admin.loyalty.index'))
                    ->with('success', "Welcome back, {$user->name}!");
            }

            return redirect()->intended(route('admin.dashboard'))
                ->with('success', "Welcome back, {$user->name}!");
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')
            ->with('success', 'Logged out successfully.');
    }
}
