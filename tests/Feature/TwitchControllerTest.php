<?php

declare(strict_types=1);

use App\Models\User;
use Laravel\Socialite\Contracts\User as SocialiteUser;
use Laravel\Socialite\Facades\Socialite;
use SocialiteProviders\Discord\Provider;

use function Pest\Laravel\get;

beforeEach(function (): void {
    $user = User::factory()->create();
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
