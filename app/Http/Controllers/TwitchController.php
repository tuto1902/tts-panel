<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Events\TwitchEventReceived;
use App\Models\TwitchAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

final class TwitchController extends Controller
{
    public function redirect(): RedirectResponse
    {
        // @phpstan-ignore method.notFound
        return Socialite::driver('twitch')
            ->scopes(['channel:read:redemptions'])
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

                return response($challenge, 200, ['Content-Type' => 'text/plain']);
            case 'notification':
                if ($request->subscription['type'] !== config('services.twitch.subscription_type')) {
                    return response('Invalid notification type', 400);
                }
                $message = $request->get('event')['user_input'];
                // TwitchEventReceived::dispatch($message);
                event(new TwitchEventReceived(message: $message));

                return response(null, 204);
            default:
                return response('Missing message type header', 400);
        }
    }
}
