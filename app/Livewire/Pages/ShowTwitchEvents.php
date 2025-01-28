<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Models\TwitchEvent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

final class ShowTwitchEvents extends Component
{
    // public $twitchAccountId;

    // public function mount()
    // {
    //     /** @var \App\Models\TwitchAccount $twitchAccount */
    //     $twitchAccount = Auth::user()->twitch;

    //     $this->twitchAccountId = $twitchAccount->account_id;
    // }

    public function getListeners(): array
    {
        return [
            // Private Channel
            'echo-private:events,TwitchEventCreated' => 'onTwitchEventCreated',
        ];
    }

    public function onTwitchEventCreated(): void {}

    public function render(): View
    {
        return view('livewire.pages.show-twitch-events');
    }

    public function markAsPlayed(TwitchEvent $event): void
    {
        $event->played_at = Carbon::now()->format('Y-m-d H:i:s');
        $event->save();
    }

    #[Computed]
    public function events(): Collection
    {
        return TwitchEvent::whereNull('played_at')->latest()->get();
    }
}
