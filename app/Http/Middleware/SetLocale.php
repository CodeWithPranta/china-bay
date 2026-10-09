<?php

namespace App\Http\Middleware;

use App\Models\SiteLocale;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Resolve locale from session, defaulting to the admin-configured
     * default language (locales table), with config as fallback.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $available = SiteLocale::activeCodes()
            ?: config('app.available_locales', ['bn', 'en', 'ar']);

        $fallback = SiteLocale::defaultCode()
            ?? config('app.locale', 'bn');

        $locale = (string) $request->session()->get('locale', $fallback);

        if (! in_array($locale, $available, true)) {
            $locale = config('app.fallback_locale', 'en');
        }

        App::setLocale($locale);

        $direction = SiteLocale::directionOf($locale);

        View::share([
            'appLocale' => $locale,
            'appDirection' => $direction,
            'availableLocales' => $available,
        ]);

        return $next($request);
    }
}
