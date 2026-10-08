<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, string $locale): RedirectResponse
    {
        $available = config('app.available_locales', ['en']);

        if (! in_array($locale, $available, true)) {
            abort(404);
        }

        $request->session()->put('locale', $locale);

        return redirect()->back(fallback: '/');
    }
}
