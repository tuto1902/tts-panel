<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Events\TwitchEventReceived;
use App\Models\TwitchEvent;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.overlay')]
final class ShowOverlay extends Component
{
    public bool $fadeIn = false;

    public bool $fadeOut = false;

    public ?int $event_id = null;

    public ?TwitchEvent $event;

    public function render(): View
    {
        if ($this->event_id) {
            $this->event = TwitchEvent::find($this->event_id);
        }

        return view('livewire.pages.show-overlay');
    }

    // @codeCoverageIgnoreStart
    public function getListeners(): array
    {
        return [
            'echo:events,TwitchEventCreated' => 'onTwitchEventCreated',
        ];
    }

    public function onTwitchEventCreated(array $payload): void
    {
        $this->event_id = $payload['event_id'];
        $this->event = TwitchEvent::find($this->event_id);
        $this->dispatch('fadeout-timer');
        $this->fadeInCard();
    }

    public function fadeInCard(): void
    {
        $this->fadeIn = true;
        $this->fadeOut = false;
    }

    public function fadeOutCard(): void
    {
        $this->fadeIn = false;
        $this->fadeOut = true;
    }

    public function handleRewardEvent(array $event): void
    {
        if (in_array($event['payload']['subscription']['type'], config('services.twitch.subscription_types'))) {

            $accountId = (int) $event['payload']['event']['user_id'];
            $message = '';

            if ($event['payload']['subscription']['type'] === 'channel.channel_points_custom_reward_redemption.add') {
                $message = $event['payload']['event']['user_input'];
            }
            event(new TwitchEventReceived(account_id: $accountId, message: $message, type: 'reward'));
        }
    }

    public function handleFollowEvent(array $event): void
    {
        $accountId = (int) $event['payload']['event']['user_id'];
        $message = $event['payload']['event']['user_name'].' just followed!';

        event(new TwitchEventReceived(account_id: $accountId, message: $message, type: 'follow'));
    }
    // @codeCoverageIgnoreEnd
}
