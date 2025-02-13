<?php

declare(strict_types=1);

use App\Livewire\Pages\ShowOverlay;
use App\Models\TwitchEvent;

it('renders the ShowOverlay page', function (): void {
    Livewire::test(ShowOverlay::class, ['overlaySecret' => config('services.twitch.overlay_secret')])
        ->assertStatus(200);
});

it('return an unauthorized response when the overlay secret is invalid', function (): void {
    Livewire::test(ShowOverlay::class, ['overlaySecret' => 'invalid'])
        ->assertStatus(403);
});

it('loads an event record when an event_id is set', function (): void {
    $newEvent = TwitchEvent::factory()->create()->fresh();
    Livewire::test(ShowOverlay::class, ['overlaySecret' => config('services.twitch.overlay_secret')])
        ->set('event_id', $newEvent->id)
        ->assertSet('event', $newEvent);
});
