<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\TwitchEventCreated;
use App\Events\TwitchEventReceived;
use App\Facades\Twitch;
use App\Models\TwitchEvent;
use Illuminate\Contracts\Queue\ShouldQueue;

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
        $twitchUser = Twitch::getUser($event->account_id);

        $twitchUserColor = Twitch::getUserColor($event->account_id);

        // @codeCoverageIgnoreStart
        if ($event->type === 'follow') {
            $event->message = $twitchUser['display_name'].' '.$event->message;
        }
        // @codeCoverageIgnoreEnd

        $twitchEvent = TwitchEvent::create([
            'message' => $event->message,
            'nickname' => $twitchUser['display_name'],
            'avatar' => $twitchUser['profile_image_url'],
            'color' => $twitchUserColor,
            'type' => $event->type,
        ]);

        broadcast(new TwitchEventCreated($twitchEvent->id));
    }
}
