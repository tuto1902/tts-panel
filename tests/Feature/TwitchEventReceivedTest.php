<?php

declare(strict_types=1);

use App\Events\TwitchEventReceived;
use App\Listeners\TwitchEventListener;
use App\Models\TwitchAccount;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\json;

beforeEach(function (): void {
    $this->messageId = '1';
    $this->timestamp = date('Y-m-d H:i:s');
    $this->challengeBody = 'pogchamp-kappa-360noscope-vohiyo';
    Config::set('services.twitch.webhook_secret', 'secret');
});

it('dispatches a TwitchEventReceived event when the webhook is called', function (): void {
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

    json('POST', '/twitch/event', $body, [
        'Twitch-Eventsub-Message-Id' => $this->messageId,
        'Twitch-Eventsub-Message-Timestamp' => $this->timestamp,
        'Twitch-Eventsub-Message-Signature' => $signature,
        'Twitch-Eventsub-Message-Type' => 'notification',
    ]);

    Event::assertDispatched(TwitchEventReceived::class);
});

it('throws an exception when the twitch event is handled with invalid data', function (): void {

    $message = 'Event message';

    setupOpenAIRequest();

    $user = User::factory()->create();
    $account = TwitchAccount::factory()->for($user)->create();

    $response = [
        'data' => [
            [
                'display_name' => 'TwitchUser',
                // Missing data
                // 'profile_image_url' => 'profile_image.png',
            ],
        ],
    ];
    Http::fake([
        // Stub a JSON response for Twitch endpoint...
        'https://api.twitch.tv/*' => Http::response($response, 200),
    ]);
    $account_id = $account->id;

    $twitchEvent = new TwitchEventReceived(account_id: $account_id, message: $message);
    $twitchEventListener = new TwitchEventListener;

    expect(fn () => $twitchEventListener->handle($twitchEvent))->toThrow(Exception::class);

    // assertDatabaseCount('twitch_events', 0);
});

it('creates a tts record in the database when the twitch event is handled', function (): void {

    $message = 'Event message';

    setupOpenAIRequest();
    $account_id = setupTwitchUserRequest();

    $twitchEvent = new TwitchEventReceived(account_id: $account_id, message: $message);
    $twitchEventListener = new TwitchEventListener;
    $twitchEventListener->handle($twitchEvent);

    assertDatabaseCount('twitch_events', 1);
    assertDatabaseHas('twitch_events', [
        'id' => 1,
        'message' => $message,
    ]);
});
