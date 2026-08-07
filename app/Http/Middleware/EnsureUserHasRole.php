<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($role === 'owner' && $user->isOwner()) {
            return $next($request);
        }

        if ($role === 'staff' && $user->isStaff()) {
            return $next($request);
        }
        
        if ($role === 'cashier' && $user->isCashier()) {
            return $next($request);
        }

        if ($request->wantsJson() || $request->header('X-Inertia')) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        return redirect()->route('storefront.home')->with('error', 'Unauthorized action.');
    }
}
