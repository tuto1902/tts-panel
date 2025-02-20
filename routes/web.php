<?php

declare(strict_types=1);

use App\Http\Controllers\TwitchController;
use App\Http\Middleware\TwitchValidationMiddleware;
use App\Livewire\Pages\ShowOverlay;
use App\Livewire\Pages\ShowPlayedTwitchEvents;
use App\Livewire\Pages\ShowTwitchEvents;
use Google\Cloud\TextToSpeech\V1\AudioConfig;
use Google\Cloud\TextToSpeech\V1\AudioEncoding;
use Google\Cloud\TextToSpeech\V1\Client\TextToSpeechClient;
use Google\Cloud\TextToSpeech\V1\SsmlVoiceGender;
use Google\Cloud\TextToSpeech\V1\SynthesisInput;
use Google\Cloud\TextToSpeech\V1\SynthesizeSpeechRequest;
use Google\Cloud\TextToSpeech\V1\VoiceSelectionParams;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/twitch/event', [TwitchController::class, 'event'])->name('twitch.event')->middleware(TwitchValidationMiddleware::class);

Route::middleware(['auth:sanctum', config('jetstream.auth_session'), 'verified'])->group(function (): void {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('/auth/redirect', [TwitchController::class, 'redirect'])->name('twitch.redirect');

    Route::get('/auth/callback', [TwitchController::class, 'callback'])->name('twitch.callback');

    Route::get('/events', ShowTwitchEvents::class)->name('events');

    Route::get('/events/played', ShowPlayedTwitchEvents::class)->name('events.played');

    Route::get('/clip', [TwitchController::class, 'clip']);

    Route::get('/google', function (): void {
        $textToSpeechClient = new TextToSpeechClient();
        $input = new SynthesisInput();
        $input->setText('This is my first test using Google Text To Speech service. Hooray!');
        $voice = new VoiceSelectionParams();
        $voice->setLanguageCode('en-GB');
        $voice->setName('en-GB-Studio-B');
        $voice->setSsmlGender(SsmlVoiceGender::MALE);
        $audioConfig = new AudioConfig();
        $audioConfig->setAudioEncoding(AudioEncoding::MP3);
        $request = new SynthesizeSpeechRequest();
        $request->setInput($input);
        $request->setVoice($voice);
        $request->setAudioConfig($audioConfig);
        $response = $textToSpeechClient->synthesizeSpeech($request);
        Storage::disk('public')->put('google-test.mp3', $response->getAudioContent());
    });
});

Route::get('/overlay', ShowOverlay::class);
