<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $supportedLocales = ['en', 'ar'];
        
        // 1. Check for ?lang= query parameter
        $locale = $request->query('lang');

        // 2. Check for Accept-Language header if no query param
        if (!$locale) {
            $locale = $request->getPreferredLanguage($supportedLocales);
        }

        // 3. Fallback to default if not supported or not found
        if (!in_array($locale, $supportedLocales)) {
            $locale = config('app.fallback_locale', 'en');
        }

        App::setLocale($locale);

        return $next($request);
    }
}
