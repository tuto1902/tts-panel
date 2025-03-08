<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Events\TwitchAccountUpdated;
use App\Events\TwitchEventReceived;
use App\Models\TwitchAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Facades\Socialite;

final class TwitchController extends Controller
{
    // @codeCoverageIgnoreStart
    public function redirect(): RedirectResponse
    {
        // @phpstan-ignore method.notFound
        return Socialite::driver('twitch')
            ->scopes(['channel:read:redemptions', 'clips:edit', 'moderator:read:followers'])
            ->redirect();
    }

    public function callback(): RedirectResponse
    {
        $twitchUser = Socialite::driver('twitch')->user();
        if (! $twitchUser) {
            return redirect()->route('dashboard')->dangerBanner('Twitch authentication failed');
        }

        Auth::user()->twitch()->updateOrCreate(
            [
                'account_id' => $twitchUser->getId(),
            ],
            [
                'nickname' => $twitchUser->getNickname(),
                'name' => $twitchUser->getName(),
                'email' => $twitchUser->getEmail(),
                'avatar' => $twitchUser->getAvatar(),
                // @phpstan-ignore property.notFound
                'access_token' => $twitchUser->token,
                // @phpstan-ignore property.notFound
                'refresh_token' => $twitchUser->refreshToken,
            ]
        );

        return redirect()->route('dashboard')->banner('Twitch authentication successfull');
    }
    // @codeCoverageIgnoreEnd

    public function event(Request $request): Response
    {
        if (! $request->header('Twitch-Eventsub-Message-Type')) {
            return response('Missing message type header', 400);
        }

        switch ($request->header('Twitch-Eventsub-Message-Type')) {
            case 'webhook_callback_verification':
                $challenge = $request->get('challenge');
                $accountId = $request->get('subscription')['condition']['broadcaster_user_id'];
                $twitchAccount = TwitchAccount::where('account_id', $accountId)->first();
                $twitchAccount->status = 'enabled';
                $twitchAccount->save();
                broadcast(new TwitchAccountUpdated());

                return response($challenge, 200, ['Content-Type' => 'text/plain']);
            case 'notification':
                if ($request->subscription['type'] !== config('services.twitch.subscription_type')) {
                    return response('Invalid notification type', 400);
                }
                $message = $request->get('event')['user_input'];
                $accountId = (int) $request->get('event')['user_id'];

                event(new TwitchEventReceived(account_id: $accountId, message: $message));

                return response(null, 204);
            default:
                return response('Missing message type header', 400);
        }
    }

    // @codeCoverageIgnoreStart
    public function clip(Request $request): void
    {
        /** @var TwitchAccount $twitchAccount */
        $twitchAccount = Auth::user()->twitch;
        $response = Http::withToken($twitchAccount->access_token)
            ->withHeaders(['Client-Id' => config('services.twitch.client_id')])
            ->post('https://api.twitch.tv/helix/clips', [
                'broadcaster_id' => $twitchAccount->account_id,
            ]);

        Log::info($response->body());

        if ($response->status() === 401) {
            // Refresh the access token
            $payload = [
                'client_id' => config('services.twitch.client_id'),
                'client_secret' => config('services.twitch.client_secret'),
                'grant_type' => 'refresh_token',
                'refresh_token' => $twitchAccount->refresh_token,
            ];
            $response = Http::asForm()->post('https://id.twitch.tv/oauth2/token', $payload);

            if ($response->successful()) {
                $twitchAccount->access_token = $response->json('access_token');
                $twitchAccount->refresh_token = $response->json('refresh_token');
                $twitchAccount->save();
                // retry the clip request
                $response = Http::withToken($twitchAccount->access_token)
                    ->withHeaders(['Client-Id' => config('services.twitch.client_id')])
                    ->post('https://api.twitch.tv/helix/clips', [
                        'broadcaster_id' => $twitchAccount->account_id,
                    ]);
            }
        }
    }
    // @codeCoverageIgnoreEnd
}
