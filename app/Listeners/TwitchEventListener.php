<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\TwitchEventReceived;
use App\Models\TwitchEvent;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

final class TwitchEventListener implements ShouldQueue
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(TwitchEventReceived $event): void
    {
        $fileName = Str::uuid()->toString().'.mp3';
        Http::sink(public_path('/storage/'.$fileName))->withToken(config('services.openai.secret'))
            ->post('https://api.openai.com/v1/audio/speech', [
                'model' => 'tts-1',
                'input' => $event->message,
                'voice' => 'alloy',
            ]);
        TwitchEvent::create([
            'message' => $event->message,
            'file_path' => $fileName,
        ]);
    }
}
