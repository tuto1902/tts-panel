<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\TwitchService;
use Illuminate\Support\ServiceProvider;

final class TwitchServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // @codeCoverageIgnoreStart
        $this->app->singleton(TwitchService::class, function () {
            return new TwitchService();
        });
        // @codeCoverageIgnoreEnd
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
