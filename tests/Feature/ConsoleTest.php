<?php

declare(strict_types=1);

use App\Models\TwitchEvent;
use Illuminate\Support\Carbon;

use function Pest\Laravel\assertDatabaseCount;

it('prunes models', function (): void {
    TwitchEvent::factory()->create(['created_at' => Carbon::now()->subDay()]);

    $this->artisan('model:prune')->assertSuccessful();

    assertDatabaseCount('twitch_events', 0);
});
