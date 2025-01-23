<?php

declare(strict_types=1);

use App\Livewire\TwitchEventCard;
use App\Models\TwitchEvent;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function (): void {
    $this->user = User::factory()->create();
});

it('renders succesfully', function (): void {
    $event = TwitchEvent::factory()->create();
    Livewire::test(TwitchEventCard::class, ['event' => $event])
        ->assertOk();
});

it('shows the event message', function (): void {
    $event = TwitchEvent::factory()->create();

    Livewire::actingAs($this->user)->test(TwitchEventCard::class, ['event' => $event])
        ->assertSee($event->message)
        ->assertSee($event->nickname)
        ->assertSee($event->avatar);
});

it('updates the event played at column', function (): void {
    $event = TwitchEvent::factory()->create();
    Livewire::actingAs($this->user)->test(TwitchEventCard::class, ['event' => $event])
        ->call('markAsPlayed');

    $event->refresh();

    expect($event->played_at)->not()->toBe(null);
});
