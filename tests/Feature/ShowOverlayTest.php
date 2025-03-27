<?php

declare(strict_types=1);

use App\Livewire\Pages\ShowOverlay;
use App\Models\TwitchEvent;

it('renders the ShowOverlay page', function (): void {
    Livewire::test(ShowOverlay::class)
        ->assertStatus(200);
});

it('loads an event record when an event_id is set', function (): void {
    $newEvent = TwitchEvent::factory()->state(['type' => 'reward'])->create()->fresh();
    Livewire::test(ShowOverlay::class)
        ->set('event_id', $newEvent->id)
        ->assertSet('event', $newEvent);
});
