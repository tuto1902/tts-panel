<?php

declare(strict_types=1);

namespace App\Livewire;

use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;
use Laravel\Jetstream\ConfirmsPasswords;
use Livewire\Attributes\Computed;
use Livewire\Component;

final class TwitchEventSubscriptionForm extends Component
{
    use ConfirmsPasswords;

    public function render(): View
    {
        return view('profile.twitch-event-subscription-form');
    }

    public function enableTwitchEventSubscription(): void
    {
        // Request an access token
        $accessToken = $this->getAccessToken();
        // Subscribe to the event
        $payload = $this->getRequestPayload();

        $response = Http::withToken($accessToken)
            ->withHeaders([
                'Client-Id' => config('services.twitch.client_id'),
                'Content-Type' => 'application/json',
            ])
            ->post('https://api.twitch.tv/helix/eventsub/subscriptions', $payload);

        if ($response->failed()) {
            throw new Exception('event subscription failed');
        }

        Auth::user()->twitch()->update([
            'status' => $response->json('data')[0]['status'],
        ]);
    }

    #[Computed]
    public function enabled(): bool
    {
        /** @var \App\Models\TwitchAccount $twitchAccount */
        $twitchAccount = Auth::user()->twitch;

        return $twitchAccount->status === 'enabled';
    }

    #[Computed]
    public function verificationPending(): bool
    {
        /** @var \App\Models\TwitchAccount $twitchAccount */
        $twitchAccount = Auth::user()->twitch;

        return $twitchAccount->status === 'webhook_callback_verification_pending';
    }

    protected function getAccessToken(): string
    {
        $response = Http::post('https://id.twitch.tv/oauth2/token', [
            'client_id' => config('services.twitch.client_id'),
            'client_secret' => config('services.twitch.client_secret'),
            'grant_type' => 'client_credentials',
        ]);

        if ($response->failed()) {
            throw new Exception('failed to obtain access token');
        }

        return $response->json('access_token');
    }

    protected function getRequestPayload(): array
    {
        /** @var \App\Models\TwitchAccount $twitchAccount */
        $twitchAccount = Auth::user()->twitch;

        return [
            'type' => 'channel.channel_points_custom_reward_redemption.add',
            'version' => '1',
            'condition' => [
                'broadcaster_user_id' => $twitchAccount->account_id,
                'reward_id' => config('services.twitch.reward_id'),
            ],
            'transport' => [
                'method' => 'webhook',
                'callback' => config('app.url').route('twitch.event', absolute: false),
                'secret' => config('services.twitch.webhook_secret'),
            ],
        ];
    }
}
