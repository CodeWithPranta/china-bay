<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Every storefront/admin page uses Alpine (x-data drawers, menus,
 * checkout forms). Livewire bundles Alpine, so we force its asset
 * injection on all web responses and load NO second Alpine copy —
 * two Alpine instances fight over x-data and break Livewire filters.
 */
class ForceLivewireAssets
{
    public function handle(Request $request, Closure $next): Response
    {
        app('livewire')->forceAssetInjection();

        return $next($request);
    }
}
