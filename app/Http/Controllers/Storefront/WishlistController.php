<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function toggle(Product $product)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'Login required'], 401);
        }
        
        $existing = Wishlist::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();
        
        if ($existing) {
            $existing->delete();
            return response()->json([
                'success' => true,
                'wishlisted' => false,
                'count' => Wishlist::where('user_id', $user->id)->count(),
                'message' => 'Removed from wishlist'
            ]);
        }
        
        Wishlist::create([
            'user_id' => $user->id,
            'product_id' => $product->id,
        ]);
        
        return response()->json([
            'success' => true,
            'wishlisted' => true,
            'count' => Wishlist::where('user_id', $user->id)->count(),
            'message' => 'Added to wishlist'
        ]);
    }
    
    public function status()
    {
        if (!Auth::check()) {
            return response()->json(['ids' => []]);
        }
        return response()->json([
            'ids' => Wishlist::where('user_id', Auth::id())->pluck('product_id'),
            'count' => Wishlist::where('user_id', Auth::id())->count(),
        ]);
    }
}
