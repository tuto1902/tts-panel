<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\TextToSpeechService;
use Illuminate\Support\ServiceProvider;

final class TextToSpeechServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(TextToSpeechService::class, function () {
            return new TextToSpeechService();
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
