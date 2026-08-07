<?php

namespace App\Http\Controllers\Storefront\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Storefront\PhoneLoginRequest;
use App\Http\Requests\Storefront\PhoneRegisterRequest;
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

    public function login(PhoneLoginRequest $request)
    {
        $credentials = $request->validated();
        
        $phone = $this->normalizePhone($credentials['phone']);
        
        $user = User::where('phone', $phone)->first();

        if ($user && Auth::attempt(['phone' => $phone, 'password' => $credentials['password']], $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended(route('storefront.home'))
                ->with('success', 'Welcome back to LULU!');
        }

        return back()->withErrors([
            'phone' => 'The provided credentials do not match our records.',
        ])->onlyInput('phone');
    }

    public function showRegisterForm()
    {
        if (Auth::check()) {
            return redirect()->route('storefront.home');
        }

        return view('storefront.auth.register');
    }

    public function register(PhoneRegisterRequest $request)
    {
        $validated = $request->validated();
        
        $phone = $this->normalizePhone($validated['phone']);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone' => $phone,
            'role' => 'customer',
            'password' => Hash::make($validated['password']),
        ]);
        
        // Link any past guest orders
        \App\Models\Order::whereNull('user_id')
            ->where('customer_phone', $phone)
            ->update(['user_id' => $user->id]);

        Auth::login($user);

        return redirect()->route('storefront.home')
            ->with('success', 'Your account has been created successfully!');
    }
    
    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9\+]/', '', $phone);
        
        if (str_starts_with($phone, '0')) {
            $phone = '+251' . substr($phone, 1);
        } elseif (str_starts_with($phone, '9') || str_starts_with($phone, '7')) {
            $phone = '+251' . $phone;
        } elseif (str_starts_with($phone, '251')) {
            $phone = '+' . $phone;
        }
        
        return $phone;
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
