<?php

declare(strict_types=1);

use App\Events\TwitchEventCreated;
use App\Events\TwitchEventReceived;
use App\Listeners\TwitchEventListener;
use Illuminate\Broadcasting\Channel;
use Illuminate\Support\Facades\Event;

use function Pest\Laravel\assertDatabaseHas;

it('dispatches a TwitchEventCreated event when the TwitchEventReceived is handled', function (): void {

    $message = 'Event message';

    $account_id = setupTwitchUserRequest();

    // Fake events
    Event::fake();

    $twitchEvent = new TwitchEventReceived(account_id: $account_id, message: $message, type: 'reward');
    $twitchEventListener = new TwitchEventListener;
    $twitchEventListener->handle($twitchEvent);

    Event::assertDispatched(TwitchEventCreated::class, function ($event) {
        return in_array(new Channel('events'), $event->broadcastOn());
    });
});

it('requests user display name and avatar from Twitch', function (): void {
    $account_id = setupTwitchUserRequest();

    $message = 'Event message';

    $twitchEvent = new TwitchEventReceived(account_id: $account_id, message: $message, type: 'reward');
    $twitchEventListener = new TwitchEventListener;
    $twitchEventListener->handle($twitchEvent);

    assertDatabaseHas('twitch_events', [
        'id' => 1,
        'nickname' => 'TwitchUser',
        'avatar' => 'profile_image.png',
    ]);

});
