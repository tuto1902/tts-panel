<?php

declare(strict_types=1);

use App\Events\TwitchEventReceived;
use App\Listeners\TwitchEventListener;
use App\Models\TwitchEvent;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Symfony\Component\HttpFoundation\Response;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\json;

beforeEach(function (): void {
    $this->messageId = '1';
    $this->timestamp = date('Y-m-d H:i:s');
    $this->challengeBody = 'pogchamp-kappa-360noscope-vohiyo';
    Config::set('services.twitch.webhook_secret', 'secret');
});

it('dispatches an event when the webhook is called', function (): void {
    $subscription_type = config('services.twitch.subscription_type');
    $body = [
        'subscription' => [
            'type' => $subscription_type,
        ],
        'event' => [
            'user_id' => '1337',
            'user_login' => 'awesome_user',
            'user_name' => 'Awesome_User',
            'user_input' => 'reward message',
        ],
    ];
    $message = $this->messageId.$this->timestamp.json_encode($body);
    // Encrypt the application id header, timestamp and request body to create a signature
    // Signature will always be different when using a random secret
    $signature = 'sha256='.hash_hmac('sha256', $message, 'secret');
    // Fake events
    Event::fake();

    $response = json('POST', '/twitch/event', $body, [
        'Twitch-Eventsub-Message-Id' => $this->messageId,
        'Twitch-Eventsub-Message-Timestamp' => $this->timestamp,
        'Twitch-Eventsub-Message-Signature' => $signature,
        'Twitch-Eventsub-Message-Type' => 'notification',
    ]);

    expect($response->status())->toBe(Response::HTTP_NO_CONTENT);

    Event::assertDispatched(TwitchEventReceived::class);
});

it('creates a tts record in the database when the twitch event is handled', function (): void {

    $message = 'Event message';

    setupOpenAIRequest();

    $twitchEvent = new TwitchEventReceived(message: $message);
    $twitchEventListener = new TwitchEventListener;
    $twitchEventListener->handle($twitchEvent);

    assertDatabaseCount('twitch_events', 1);
    assertDatabaseHas('twitch_events', [
        'id' => 1,
        'message' => $message,
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
