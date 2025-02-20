<?php

namespace App\Providers;

declare(strict_types=1);

use App\Services\TwitchService;
use Illuminate\Support\ServiceProvider;

final class TwitchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(TwitchService::class, function () {
            return new TwitchService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
