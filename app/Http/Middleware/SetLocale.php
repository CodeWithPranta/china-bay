<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $available = config('app.available_locales', ['bn', 'en', 'ar']);
        $locale = (string) $request->session()->get('locale', config('app.locale', 'bn'));

        if (! in_array($locale, $available, true)) {
            $locale = config('app.fallback_locale', 'en');
        }

        App::setLocale($locale);

        $direction = in_array($locale, config('app.rtl_locales', ['ar']), true) ? 'rtl' : 'ltr';

        View::share([
            'appLocale' => $locale,
            'appDirection' => $direction,
            'availableLocales' => $available,
        ]);

        return $next($request);
    }
}
