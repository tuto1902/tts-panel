<?php

declare(strict_types=1);

use App\Enums\SynthesizeService;
use App\Services\TextToSpeechService;

it('calls the TextToSpeech::service method when TwitchEventListener is handled', function (): void {
    $service = new TextToSpeechService(); // Create a real instance

    $reflection = new ReflectionProperty(TextToSpeechService::class, 'synthesizeService');
    $reflection->setAccessible(true);
    $reflection->setValue($service, SynthesizeService::Google);

    $mock = Mockery::mock($service)->makePartial();
    $mock->shouldReceive('synthesize')->once()->with('Hello', 'output.mp3');

    $mock->synthesize('Hello', 'output.mp3');

});
