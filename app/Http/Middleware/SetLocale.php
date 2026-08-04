<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Set locale from session on every request so __() translations work.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $allowed = ['en', 'am'];
        $locale  = session('locale', 'en');

        if (! in_array($locale, $allowed, true)) {
            $locale = 'en';
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
