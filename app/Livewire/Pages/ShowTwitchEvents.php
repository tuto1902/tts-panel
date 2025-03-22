<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Events\TwitchEventMarkedAsPlayed;
use App\Models\TwitchEvent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

final class ShowTwitchEvents extends Component
{
    public function getListeners(): array
    {
        return [
            // Channel
            'echo:events,TwitchEventCreated' => 'onTwitchEventCreated',
            'echo:events,TwitchEventMarkedAsPlayed' => 'onTwitchEventMarkedAsPlayed',
        ];
    }

    // @codeCoverageIgnoreStart
    public function onTwitchEventCreated(): void {}
    // @codeCoverageIgnoreEnd

    // @codeCoverageIgnoreStart
    public function onTwitchEventMarkedAsPlayed(): void {}
    // @codeCoverageIgnoreEnd

    public function render(): View
    {
        return view('livewire.pages.show-twitch-events');
    }

    public function markAsPlayed(TwitchEvent $event): void
    {
        $event->played_at = Carbon::now()->format('Y-m-d H:i:s');
        $event->save();
        broadcast(new TwitchEventMarkedAsPlayed());
    }

    #[Computed]
    public function events(): Collection
    {
        return TwitchEvent::whereNull('played_at')->where('type', 'reward')->latest()->get();
    }
}
