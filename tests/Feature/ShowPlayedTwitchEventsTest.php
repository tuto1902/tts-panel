<?php

declare(strict_types=1);

use App\Livewire\Pages\ShowPlayedTwitchEvents;
use App\Models\TwitchEvent;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\withoutExceptionHandling;

beforeEach(function (): void {
    $this->user = User::factory()->create();
});

it('renders the component', function (): void {
    Livewire::test(ShowPlayedTwitchEvents::class)
        ->assertOk();
});

it('shows all played twitch event records', function (): void {
    TwitchEvent::factory(3)->create(['type' => 'reward', 'played_at' => Carbon::now()]);

    Livewire::actingAs($this->user)->test(ShowPlayedTwitchEvents::class)
        ->assertCount('events', 3);
});

it('only shows events that have being played', function (): void {
    TwitchEvent::factory(3)->create(['type' => 'reward', 'played_at' => Carbon::now()]);
    TwitchEvent::factory()->create(['type' => 'reward']);

    Livewire::actingAs($this->user)->test(ShowPlayedTwitchEvents::class)
        ->assertCount('events', 3);
});

it('contains the twitch event card component', function (): void {
    TwitchEvent::factory(3)->create(['type' => 'reward']);
    withoutExceptionHandling();
    actingAs($this->user)->get('/events/played')
        ->assertSeeLivewire(ShowPlayedTwitchEvents::class);
});
