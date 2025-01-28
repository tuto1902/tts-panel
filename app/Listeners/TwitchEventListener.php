<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\TwitchEventCreated;
use App\Events\TwitchEventReceived;
use App\Models\TwitchEvent;
use Exception;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Http;
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
        Http::sink(public_path('/storage/'.$fileName))->withToken(config('services.openai.secret'))
            ->post('https://api.openai.com/v1/audio/speech', [
                'model' => 'tts-1',
                'input' => $event->message,
                'voice' => 'alloy',
            ]);

        // Get a new access token
        $response = Http::post('https://id.twitch.tv/oauth2/token', [
            'client_id' => config('services.twitch.client_id'),
            'client_secret' => config('services.twitch.client_secret'),
            'grant_type' => 'client_credentials',
        ]);

        $accessToken = $response->json('access_token');

        // Request the user information
        $response = Http::withToken($accessToken)
            ->withHeaders([
                'Client-Id' => config('services.twitch.client_id'),
                'Content-Type' => 'application/json',
            ])
            ->get("https://api.twitch.tv/helix/users?id={$event->account_id}");

        $data = $response->json();
        if (! isset($data['data'][0]['display_name'], $data['data'][0]['profile_image_url'])) {
            throw new Exception('Missing twitch user information');
        }
        TwitchEvent::create([
            'message' => $event->message,
            'file_path' => $fileName,
            'nickname' => $data['data'][0]['display_name'],
            'avatar' => $data['data'][0]['profile_image_url'],
        ]);
        broadcast(new TwitchEventCreated);
    }
}
