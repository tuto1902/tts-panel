<?php

declare(strict_types=1);

use App\Models\TwitchAccount;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Config;

use function Pest\Laravel\json;

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

it('it returns an unauthorized response when a header is missing', function (): void {
    $response = json('POST', '/twitch/event', json_decode($this->body, true), [
        'Twitch-Eventsub-Message-Id' => $this->messageId,
        'Twitch-Eventsub-Message-Timestamp' => $this->timestamp,
        // Missing header
        // 'Twitch-Eventsub-Message-Signature' => $this->signature,
    ]);

    $response->assertStatus(Response::HTTP_UNAUTHORIZED);
});

it('it returns an unauthorized response when the payload is invalid', function (): void {
    $response = json('POST', '/twitch/event', json_decode($this->body, true), [
        'Twitch-Eventsub-Message-Id' => $this->messageId,
        'Twitch-Eventsub-Message-Timestamp' => $this->timestamp,
        // Invalid header
        'Twitch-Eventsub-Message-Signature' => 'invalid',
    ]);

    $response->assertStatus(Response::HTTP_UNAUTHORIZED);
});
