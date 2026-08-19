<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Указываем Inertia использовать кастомный layout
        Inertia::share([
            'appName' => config('app.name', 'Каталог'),
        ]);

        // Указываем layout для всех страниц
        Inertia::setRootView('inertia-layout');
    }
}
