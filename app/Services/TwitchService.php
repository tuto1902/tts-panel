<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Http;

final class TwitchService
{
    public function getUser($accountId): array
    {
        // Request the user information
        $accessToken = $this->getAccessToken();
        $response = Http::withToken($accessToken)
            ->withHeaders([
                'Client-Id' => config('services.twitch.client_id'),
                'Content-Type' => 'application/json',
            ])
            ->get("https://api.twitch.tv/helix/users?id={$accountId}");

        $response = $response->json();

        return [
            'display_name' => $response['data'][0]['display_name'],
            'profile_image_url' => $response['data'][0]['profile_image_url'],
        ];
    }

    private function getAccessToken(): string
    {

        // Get a new access token
        $response = Http::post('https://id.twitch.tv/oauth2/token', [
            'client_id' => config('services.twitch.client_id'),
            'client_secret' => config('services.twitch.client_secret'),
            'grant_type' => 'client_credentials',
        ]);

        $accessToken = $response->json('access_token');

        return $accessToken;
    }
}
