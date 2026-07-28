<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsOwner
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->isOwner()) {
            if ($request->wantsJson() || $request->header('X-Inertia')) {
                return response()->json(['message' => 'Forbidden. Only the brand owner can perform this action.'], 403);
            }

            return redirect()->route('admin.dashboard')->with('error', 'Only the brand owner can access this section.');
        }

        return $next($request);
    }
}
