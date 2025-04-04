<?php

declare(strict_types=1);

use App\Events\TwitchEventReceived;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schedule;

use function Laravel\Prompts\spin;
use function Laravel\Prompts\table;

Schedule::command('model:prune')->daily();

Artisan::command('twitch:event', function (): void {
    $quote = Inspiring::quotes()->random();

    broadcast(
        new TwitchEventReceived(account_id: 57648209, message: $quote, type: 'reward')
    );
});

Artisan::command('twitch:follow', function (): void {
    $message = ' just followed';
    broadcast(
        new TwitchEventReceived(account_id: 57648209, message: $message, type: 'follow')
    );
});

Artisan::command('twitch:status', function (): void {
    $response = spin(
        message: 'Fetching new access token...',
        callback: fn () => Http::post('https://id.twitch.tv/oauth2/token', [
            'client_id' => config('services.twitch.client_id'),
            'client_secret' => config('services.twitch.client_secret'),
            'grant_type' => 'client_credentials',
        ])
    );

    $accessToken = $response->json('access_token');

    $response = spin(
        message: 'Fetching twitch event subscription status',
        callback: fn () => Http::withToken($accessToken)
            ->withHeaders([
                'Client-Id' => config('services.twitch.client_id'),
                'Content-Type' => 'application/json',
            ])
            ->get('https://api.twitch.tv/helix/eventsub/subscriptions')
    );

    $response = $response->json();
    $rows = collect($response['data'])->map(fn ($row) => [
        'id' => $row['id'],
        'status' => $row['status'],
        'webhook' => $row['transport']['callback'],
    ]);
    table(
        headers: ['ID', 'Status', 'Webhook URL'],
        rows: $rows
    );
});
