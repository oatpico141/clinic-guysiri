<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS when behind proxy (localtunnel, ngrok, etc.)
        if (request()->header('X-Forwarded-Proto') === 'https' ||
            str_contains(request()->header('Host', ''), 'loca.lt') ||
            str_contains(request()->header('Host', ''), 'ngrok')) {
            URL::forceScheme('https');
        }
    }
}
