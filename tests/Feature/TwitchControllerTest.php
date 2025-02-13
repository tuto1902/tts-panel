<?php

declare(strict_types=1);

use App\Models\TwitchAccount;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use SocialiteProviders\Discord\Provider;
use Symfony\Component\HttpFoundation\Response;

use function Pest\Laravel\get;
use function Pest\Laravel\json;
use function Pest\Laravel\withoutExceptionHandling;

beforeEach(function (): void {
    $this->messageId = '1';
    $this->timestamp = date('Y-m-d H:i:s');
    $this->challengeBody = 'pogchamp-kappa-360noscope-vohiyo';
    $user = User::factory()->create();
    $account = TwitchAccount::factory()->for($user)->create();
    $this->body = <<<JSON
    {"challenge":"{$this->challengeBody}","subscription":{"condition":{"broadcaster_user_id":"{$account->account_id}"}}}
    JSON;
    Config::set('services.twitch.webhook_secret', 'secret');
    $this->message = $this->messageId.$this->timestamp.$this->body;
    // Encrypt the application id header, timestamp and request body to create a signature
    // Signature will always be different when using a random secret
    $this->signature = 'sha256='.hash_hmac('sha256', $this->message, 'secret');
});

it('redirects to twitch on auth redirect', function (): void {
    $user = Mockery::mock(SocialiteUser::class);

    $user->shouldReceive('getId')->andReturn('99999999');
    $user->shouldReceive('getName')->andReturn('Arturo');
    $user->shouldReceive('getEmail')->andReturn('arturo@example.com');
    $user->shouldReceive('getAvatar')->andReturn('https://example.com/avatar.jpg');

    $driver = Mockery::mock(Provider::class);
    $driver->shouldReceive('user')->andReturn($user);

    Socialite::shouldReceive('driver')
        ->with('twitch')
        ->andReturn($driver);

    get('/auth/redirect')->assertRedirect();
});

it('redirects to twitch on auth callback', function (): void {
    $user = Mockery::mock(SocialiteUser::class);

    $user->shouldReceive('getId')->andReturn('99999999');
    $user->shouldReceive('getName')->andReturn('Arturo');
    $user->shouldReceive('getEmail')->andReturn('arturo@example.com');
    $user->shouldReceive('getAvatar')->andReturn('https://example.com/avatar.jpg');

    $driver = Mockery::mock(Provider::class);
    $driver->shouldReceive('user')->andReturn($user);

    get('/auth/callback')->assertRedirect();
});

it('handles the webhook callback verification request', function (): void {
    $response = json('POST', '/twitch/event', json_decode($this->body, true), [
        'Content-Type' => 'application/json',
        'Twitch-Eventsub-Message-Id' => $this->messageId,
        'Twitch-Eventsub-Message-Timestamp' => $this->timestamp,
        'Twitch-Eventsub-Message-Signature' => $this->signature,
        'Twitch-Eventsub-Message-Type' => 'webhook_callback_verification',
    ]);

    $response->assertStatus(Response::HTTP_OK)->assertSeeText($this->challengeBody);
});

it('it returns an unauthorized response when signature validation fails', function (): void {
    $response = json('POST', '/twitch/event', json_decode($this->body, true), [
        'Twitch-Eventsub-Message-Id' => $this->messageId,
        'Twitch-Eventsub-Message-Timestamp' => $this->timestamp,
        // Missing header
        // 'Twitch-Eventsub-Message-Signature' => $this->signature,
    ]);

    $response->assertStatus(Response::HTTP_UNAUTHORIZED);
});

it('returns a bad request response status when the message type is missing', function (): void {
    $response = json('POST', '/twitch/event', json_decode($this->body, true), [
        'Twitch-Eventsub-Message-Id' => $this->messageId,
        'Twitch-Eventsub-Message-Timestamp' => $this->timestamp,
        'Twitch-Eventsub-Message-Signature' => $this->signature,
    ]);

    $response->assertStatus(Response::HTTP_BAD_REQUEST);
});

it('returns a bad request response status when the message type is invalid', function (): void {
    $response = json('POST', '/twitch/event', json_decode($this->body, true), [
        'Twitch-Eventsub-Message-Id' => $this->messageId,
        'Twitch-Eventsub-Message-Timestamp' => $this->timestamp,
        'Twitch-Eventsub-Message-Signature' => $this->signature,
        'Twitch-Eventsub-Message-Type' => 'invalid',
    ]);

    $response->assertStatus(Response::HTTP_BAD_REQUEST);
});

it('handles the webhook callback notification request', function (): void {
    setupOpenAIRequest();
    // Fake events
    Event::fake();

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

    $this->message = $this->messageId.$this->timestamp.json_encode($body);
    // Encrypt the application id header, timestamp and request body to create a signature
    // Signature will always be different when using a random secret
    $this->signature = 'sha256='.hash_hmac('sha256', $this->message, 'secret');
    withoutExceptionHandling();
    $response = json('POST', '/twitch/event', $body, [
        'Twitch-Eventsub-Message-Id' => $this->messageId,
        'Twitch-Eventsub-Message-Timestamp' => $this->timestamp,
        'Twitch-Eventsub-Message-Signature' => $this->signature,
        'Twitch-Eventsub-Message-Type' => 'notification',
    ]);

    $response->assertStatus(Response::HTTP_NO_CONTENT);
});

it('returns a bad request response when the notification type is invalid', function (): void {
    setupOpenAIRequest();
    // Fake events
    Event::fake();

    $this->body = [
        'subscription' => [
            // Invalid notification type
            'type' => 'invalid',
        ],
        'event' => [
            'user_id' => '1337',
            'user_login' => 'awesome_user',
            'user_name' => 'Awesome_User',
            'reward' => [
                'prompt' => 'reward message',
            ],
        ],
    ];

    $this->message = $this->messageId.$this->timestamp.json_encode($this->body);
    // Encrypt the application id header, timestamp and request body to create a signature
    // Signature will always be different when using a random secret
    $this->signature = 'sha256='.hash_hmac('sha256', $this->message, 'secret');

    $response = json('POST', '/twitch/event', $this->body, [
        'Twitch-Eventsub-Message-Id' => $this->messageId,
        'Twitch-Eventsub-Message-Timestamp' => $this->timestamp,
        'Twitch-Eventsub-Message-Signature' => $this->signature,
        'Twitch-Eventsub-Message-Type' => 'notification',
    ]);

    $response->assertStatus(Response::HTTP_BAD_REQUEST);
});
