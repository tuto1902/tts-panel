<?php

declare(strict_types=1);

use App\Events\TwitchEventReceived;
use App\Listeners\TwitchEventListener;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;

it('creates a record in the database when the twitch event is handled', function (): void {

    $message = 'Event message';

    $account_id = setupTwitchUserRequest();

    $twitchEvent = new TwitchEventReceived(account_id: $account_id, message: $message, type: 'reward');
    $twitchEventListener = new TwitchEventListener;
    $twitchEventListener->handle($twitchEvent);

    assertDatabaseCount('twitch_events', 1);
    assertDatabaseHas('twitch_events', [
        'id' => 1,
        'message' => $message,
    ]);
});
