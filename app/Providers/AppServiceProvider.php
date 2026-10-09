<?php

namespace App\Providers;

use App\Models\Setting;
use App\Translation\DatabaseLoader;
use Illuminate\Support\ServiceProvider;
use Illuminate\Translation\FileLoader;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->extend('translation.loader', fn (FileLoader $loader) => new DatabaseLoader($loader));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Admin-configured timezone (Settings > General). Falls back to the
        // config value when the table does not exist yet (fresh install).
        try {
            if ($tz = Setting::get('site.timezone')) {
                date_default_timezone_set($tz);
            }
        } catch (\Throwable) {
            // Ignore: installer / migrate context.
        }
    }
}
