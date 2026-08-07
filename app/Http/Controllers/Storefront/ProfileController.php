<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Wishlist;
use App\Models\LoyaltyPoint;
use App\Services\LoyaltyService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $loyaltyService = app(LoyaltyService::class);
        $balance = $loyaltyService->getBalance($user);
        $recentOrders = Order::where('user_id', $user->id)->latest()->take(3)->get();
        $wishlistCount = Wishlist::where('user_id', $user->id)->count();
        $settings = $loyaltyService->getSettings();
        
        return view('storefront.account.profile', compact('user', 'balance', 'recentOrders', 'wishlistCount', 'settings'));
    }
    
    public function orders()
    {
        $orders = Order::where('user_id', Auth::id())
            ->with('items')
            ->latest()
            ->paginate(10);
        return view('storefront.account.orders', compact('orders'));
    }
    
    public function wishlist()
    {
        $wishlistItems = Wishlist::where('user_id', Auth::id())
            ->with(['product.primaryImage', 'product.variants', 'product.category'])
            ->latest()
            ->paginate(12);
        return view('storefront.account.wishlist', compact('wishlistItems'));
    }
    
    public function points()
    {
        $loyaltyService = app(LoyaltyService::class);
        $user = Auth::user();
        $balance = $loyaltyService->getBalance($user);
        $loyaltyPoint = LoyaltyPoint::where('user_id', $user->id)->first();
        $history = $loyaltyService->getHistory($user);
        $settings = $loyaltyService->getSettings();
        
        return view('storefront.account.points', compact('balance', 'loyaltyPoint', 'history', 'settings'));
    }
    
    public function update(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:users,email,' . Auth::id(),
        ]);
        
        Auth::user()->update($request->only('name', 'email'));
        
        return back()->with('success', 'Profile updated successfully.');
    }
}
