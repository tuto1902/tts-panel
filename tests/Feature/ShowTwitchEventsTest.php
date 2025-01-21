<?php

declare(strict_types=1);

use App\Livewire\Pages\ShowTwitchEvents;
use App\Models\TwitchEvent;
use App\Models\User;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\withoutExceptionHandling;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    // $this->account = TwitchAccount::factory()->for($this->user)->create();
});

it('renders successfully', function (): void {
    Livewire::actingAs($this->user)->test(ShowTwitchEvents::class)
        ->assertOk();
});

it('shows all twitch event records', function (): void {
    TwitchEvent::factory(3)->create();

    Livewire::actingAs($this->user)->test(ShowTwitchEvents::class)
        ->assertCount('events', 3);
});

it('only shows events that have not being played', function (): void {
    TwitchEvent::factory(3)->create();
    TwitchEvent::factory()->state(['played_at' => Carbon::now()])->create();

    Livewire::actingAs($this->user)->test(ShowTwitchEvents::class)
        ->assertCount('events', 3);
});

it('contains the twitch event card component', function (): void {
    TwitchEvent::factory(3)->create();
    withoutExceptionHandling();
    actingAs($this->user)->get('/events')
        ->assertSeeLivewire(ShowTwitchEvents::class);
});
