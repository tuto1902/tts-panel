<?php

declare(strict_types=1);

use App\Models\TwitchAccount;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature', 'Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function setupOpenAIRequest()
{
    // Fake mp3 file response
    $response = [
        'file' => 'data:audio/mpeg;base64,AAAAIGZ0eXBtcDQyAAAAAG1wNDE=',
    ];
    // Fake storage
    Storage::fake('public');
    // Fake OpenAI TTS request
    Http::fake([
        'https://api.openai.com/v1/audio/speech' => Http::response($response, 200),
    ]);
    // Fake events
    Event::fake();
}

function setupTwitchUserRequest(): int
{
    $user = User::factory()->create();
    $account = TwitchAccount::factory()->for($user)->create();

    $response = [
        'data' => [
            [
                'display_name' => 'TwitchUser',
                'profile_image_url' => 'profile_image.png',
                'color' => '#ffffff',
            ],
        ],
    ];
    Http::fake([
        // Stub a JSON response for Twitch endpoint...
        'https://api.twitch.tv/*' => Http::response($response, 200),
    ]);

    return $account->id;
}
