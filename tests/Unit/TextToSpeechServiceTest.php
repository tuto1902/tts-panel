<?php

declare(strict_types=1);

use App\Enums\SynthesizeService;
use App\Services\TextToSpeechService;
use Illuminate\Support\Facades\Storage;

it('uses the Google synthesize service', function (): void {

    $mock = Mockery::mock(TextToSpeechService::class)->makePartial();

    $mock->shouldReceive('googleSynthesize')->once();

    Storage::fake();

    /**
     * @var TextToSpeechService $mock
     */
    $mock->synthesize('Hello', SynthesizeService::Google, 'output.mp3');

});

it('uses the OpenAI synthesize service', function (): void {

    $mock = Mockery::mock(TextToSpeechService::class)->makePartial();
    $mock->shouldReceive('openAISynthesize')->once();

    Storage::fake();

    /**
     * @var TextToSpeechService $mock
     */
    $mock->synthesize('Hello', SynthesizeService::OpenAI, 'output.mp3');

});
