<?php

declare(strict_types=1);

use App\Events\TwitchEventCreated;
use App\Events\TwitchEventReceived;
use App\Listeners\TwitchEventListener;
use App\Models\TwitchEvent;
use Illuminate\Broadcasting\Channel;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(function (): void {
    $this->messageId = '1';
    $this->timestamp = date('Y-m-d H:i:s');
    $this->challengeBody = 'pogchamp-kappa-360noscope-vohiyo';
    Config::set('services.twitch.webhook_secret', 'secret');
});

it('dispatches a TwitchEventCreated event when the TwitchEventReceived is handled', function (): void {

    $message = 'Event message';

    setupOpenAIRequest();
    $account_id = setupTwitchUserRequest();

    // Fake events
    Event::fake();

    $twitchEvent = new TwitchEventReceived(account_id: $account_id, message: $message);
    $twitchEventListener = new TwitchEventListener;
    $twitchEventListener->handle($twitchEvent);

    Event::assertDispatched(TwitchEventCreated::class, function ($event) {
        return in_array(new Channel('events'), $event->broadcastOn());
    });
});

it('requests user display name and avatar from Twitch', function (): void {
    setupOpenAIRequest();
    $account_id = setupTwitchUserRequest();

    $message = 'Event message';

    $twitchEvent = new TwitchEventReceived(account_id: $account_id, message: $message);
    $twitchEventListener = new TwitchEventListener;
    $twitchEventListener->handle($twitchEvent);

    assertDatabaseHas('twitch_events', [
        'id' => 1,
        'nickname' => 'TwitchUser',
        'avatar' => 'profile_image.png',
    ]);

});

// it('creates a new audio file using OpenAI tts API', function () {
//     $message = 'Event message';

//     setupOpenAIRequest();

//     $twitchEvent = new TwitchEventReceived(message: $message);
//     $twitchEventListener = new TwitchEventListener();
//     $twitchEventListener->handle($twitchEvent);

//     Http::assertSent(function ($request) {
//         return $request->url() === 'https://api.openai.com/v1/audio/speech' &&
//                $request['input'] === 'Event message' &&
//                $request['voice'] === 'alloy';
//     });

//     $twitchEvent = TwitchEvent::first();

//     expect($twitchEvent->file_path)->not()->toBe(null);

//     // Storage::disk('public')->assertExists($twitchEvent->file_path);
// });
