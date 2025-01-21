<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Models\TwitchEvent;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Component;

final class ShowTwitchEvents extends Component
{
    public function render(): View
    {
        return view('livewire.pages.show-twitch-events');
    }

    #[Computed]
    public function events(): Collection
    {
        return TwitchEvent::whereNull('played_at')->latest()->get();
    }
}
