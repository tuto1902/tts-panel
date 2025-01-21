<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\TwitchEvent;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Livewire\Component;

final class TwitchEventCard extends Component
{
    public TwitchEvent $event;

    public function render(): View
    {
        return view('livewire.twitch-event-card');
    }

    public function markAsPlayed(): void
    {
        /** @var TwitchEvent $event */
        $event = $this->event;
        $event->played_at = Carbon::now()->format('Y-m-d H:i:s');
        $event->save();
    }
}
