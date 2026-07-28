<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsStaff
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->isStaff()) {
            if ($request->wantsJson() || $request->header('X-Inertia')) {
                return response()->json(['message' => 'Unauthorized. Admin access required.'], 403);
            }

            return redirect()->route('admin.login')->with('error', 'Please log in with admin privileges.');
        }

        return $next($request);
    }
}
