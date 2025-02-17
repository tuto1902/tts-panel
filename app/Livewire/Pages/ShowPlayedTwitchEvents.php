<?php

namespace App\Livewire\Pages;

use App\Models\TwitchEvent;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ShowPlayedTwitchEvents extends Component
{
    public function render()
    {
        return view('livewire.pages.show-played-twitch-events');
    }

    #[Computed]
    public function events(): Collection
    {
        return TwitchEvent::whereNotNull('played_at')->latest()->get();
    }
}
