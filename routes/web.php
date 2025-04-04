<?php

declare(strict_types=1);

use App\Http\Controllers\TwitchController;
use App\Livewire\Pages\ShowOverlay;
use App\Livewire\Pages\ShowPlayedTwitchEvents;
use App\Livewire\Pages\ShowTwitchEvents;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function (): void {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/auth/redirect', [TwitchController::class, 'redirect'])->name('twitch.redirect');

    Route::get('/auth/callback', [TwitchController::class, 'callback'])->name('twitch.callback');

    Route::get('/events', ShowTwitchEvents::class)->name('events');

    Route::get('/events/played', ShowPlayedTwitchEvents::class)->name('events.played');

    Route::get('/clip', [TwitchController::class, 'clip']);

    Route::get('/overlay', ShowOverlay::class);

    Route::get('/auth/token', [TwitchController::class, 'token']);

    Route::get('/auth/token/refresh', [TwitchController::class, 'refresh']);
});
