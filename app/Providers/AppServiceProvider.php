<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\ClaudeService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ClaudeService::class, function ($app) {
            return new ClaudeService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
