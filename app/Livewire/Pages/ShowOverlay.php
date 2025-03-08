<?php

declare(strict_types=1);

namespace App\Livewire\Pages;

use App\Enums\SynthesizeService;
use App\Events\TwitchEventMarkedAsPlayed;
use App\Facades\TextToSpeech;
use App\Models\TwitchEvent;
use Illuminate\Support\Carbon;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts.overlay')]
final class ShowOverlay extends Component
{
    #[Url('key')]
    public string $overlaySecret;

    public bool $fadeIn = false;

    public bool $fadeOut = false;

    public ?int $event_id = null;

    public ?TwitchEvent $event;

    public function mount(): void
    {
        if ($this->overlaySecret !== config('services.twitch.overlay_secret')) {
            abort(403);
        }
    }

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
        $base64Audio = TextToSpeech::synthesize($this->event->message, SynthesizeService::Google);
        $this->dispatch('play-audio', base64Audio: $base64Audio);
        $this->fadeInCard();
    }

    public function markAsPlayed(): void
    {
        $this->event->played_at = Carbon::now();
        $this->event->save();
        $this->fadeOutCard();
        broadcast(new TwitchEventMarkedAsPlayed());
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
    // @codeCoverageIgnoreEnd
}
