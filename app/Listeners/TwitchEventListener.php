<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Enums\SynthesizeService;
use App\Events\TwitchEventCreated;
use App\Events\TwitchEventReceived;
use App\Facades\TextToSpeech;
use App\Facades\Twitch;
use App\Models\TwitchEvent;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Str;

final class TwitchEventListener implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(TwitchEventReceived $event): void
    {
        $fileName = Str::uuid()->toString().'.mp3';

        $twitchUser = Twitch::getUser($event->account_id);

        $twitchUserColor = Twitch::getUserColor($event->account_id);

        if (! $twitchUser['display_name'] || ! $twitchUser['profile_image_url']) {
            throw new Exception('Missing twitch user information');
        }

        $twitchEvent = TwitchEvent::create([
            'message' => $event->message,
            'nickname' => $twitchUser['display_name'],
            'avatar' => $twitchUser['profile_image_url'],
            'color' => $twitchUserColor
        ]);

        broadcast(new TwitchEventCreated($twitchEvent->id));
    }
}
