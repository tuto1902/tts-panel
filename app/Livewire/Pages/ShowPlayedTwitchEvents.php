<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Models\TwitchEvent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

final class ShowPlayedTwitchEvents extends Component
{
    public function render(): View
    {
        return view('livewire.pages.show-played-twitch-events');
    }

    #[Computed]
    public function events(): Collection
    {
        return TwitchEvent::whereNotNull('played_at')->where('type', 'reward')->latest()->get();
    }
}
