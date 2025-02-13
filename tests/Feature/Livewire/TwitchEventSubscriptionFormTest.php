<?php

declare(strict_types=1);

use App\Livewire\TwitchEventSubscriptionForm;
use App\Models\TwitchAccount;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;

use function Pest\Laravel\assertDatabaseHas;

beforeEach(function (): void {
    $this->user = User::factory()->create();
    $this->account = TwitchAccount::factory()->for($this->user)->create();
});

it('renders succesfully', function (): void {
    Livewire::actingAs($this->user)->test(TwitchEventSubscriptionForm::class)
        ->assertStatus(200);
});

it('shows the subscribe button when the verification is not complete', function (): void {
    Livewire::actingAs($this->user)
        ->test(TwitchEventSubscriptionForm::class)
        ->assertSeeHtml('data-test-connect');
});

it('shows the correct message when the verification is pending', function (): void {
    $this->account->status = 'webhook_callback_verification_pending';
    $this->account->save();
    Livewire::actingAs($this->user)
        ->test(TwitchEventSubscriptionForm::class)
        ->assertSee('The event subscription needs to be verified first.');
});

it('shows the correct message when the verification is enabled', function (): void {
    $this->account->status = 'enabled';
    $this->account->save();
    Livewire::actingAs($this->user)
        ->test(TwitchEventSubscriptionForm::class)
        ->assertSee('You are subscribed to Twitch events.');
});

it('hides the subscribe button when verification is pending', function (): void {
    $this->account->status = 'webhook_callback_verification_pending';
    $this->account->save();
    Livewire::actingAs($this->user)->test(TwitchEventSubscriptionForm::class)
        ->assertDontSeeHtml('data-test-connect');
});

it('hides the subscribe button when verification is complete', function (): void {
    $this->account->status = 'enabled';
    $this->account->save();
    Livewire::actingAs($this->user)->test(TwitchEventSubscriptionForm::class)
        ->assertDontSeeHtml('data-test-connect');
});

it('updates the account status to pending after event subscription request', function (): void {
    Http::fake([
        'https://id.twitch.tv/oauth2/token' => Http::response([
            'access_token' => 'jostpf5q0uzmxmkba9iyug38kjtgh',
        ]),
        'https://api.twitch.tv/helix/eventsub/subscriptions' => Http::response([
            'data' => [
                ['status' => 'webhook_callback_verification_pending'],
            ],
        ]),
    ]);

    Livewire::actingAs($this->user)
        ->test(TwitchEventSubscriptionForm::class)
        ->call('enableTwitchEventSubscription');

    assertDatabaseHas(TwitchAccount::class, [
        'id' => $this->account->id,
        'status' => 'webhook_callback_verification_pending',
    ]);
});

it('throws an exception when the twitch request fails', function (): void {
    Http::fake([
        'https://id.twitch.tv/oauth2/token' => Http::response([
            'access_token' => 'jostpf5q0uzmxmkba9iyug38kjtgh',
        ]),
        'https://api.twitch.tv/helix/eventsub/subscriptions' => Http::response([
            'data' => [
                ['status' => 'webhook_callback_verification_failed'],
            ],
        ], 400),
    ]);

    expect(function (): void {
        Livewire::actingAs($this->user)
            ->test(TwitchEventSubscriptionForm::class)
            ->call('enableTwitchEventSubscription');

    })->toThrow(Exception::class);
});

it('throws an exception when the twitch token request fails', function (): void {
    Http::fake([
        'https://id.twitch.tv/oauth2/token' => Http::response([], 400),
    ]);

    expect(function (): void {
        Livewire::actingAs($this->user)
            ->test(TwitchEventSubscriptionForm::class)
            ->call('enableTwitchEventSubscription');
    })->toThrow(Exception::class);
});
