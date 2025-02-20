<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\SynthesizeService;
use Google\Cloud\TextToSpeech\V1\AudioConfig;
use Google\Cloud\TextToSpeech\V1\AudioEncoding;
use Google\Cloud\TextToSpeech\V1\Client\TextToSpeechClient;
use Google\Cloud\TextToSpeech\V1\SsmlVoiceGender;
use Google\Cloud\TextToSpeech\V1\SynthesisInput;
use Google\Cloud\TextToSpeech\V1\SynthesizeSpeechRequest;
use Google\Cloud\TextToSpeech\V1\VoiceSelectionParams;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

final class TextToSpeechService
{
    private SynthesizeService $synthesizeService;

    public function synthesize($message, $fileName): void
    {
        match ($this->synthesizeService) {
            SynthesizeService::Google => $this->googleSynthesize($message, $fileName),
            SynthesizeService::OpenAI => $this->openAISynthesize($message, $fileName),
            default => $this->googleSynthesize($message, $fileName)
        };
    }

    public function service(SynthesizeService $service): self
    {
        $this->synthesizeService = $service;

        return $this;
    }

    private function googleSynthesize($message, $fileName): void
    {
        $textToSpeechClient = new TextToSpeechClient();
        $input = new SynthesisInput();
        $input->setText($message);
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
        Storage::disk('public')->put($fileName, $response->getAudioContent());
    }

    private function openAISynthesize($message, $fileName)
    {
        Http::sink(public_path('/storage/'.$fileName))->withToken(config('services.openai.secret'))
            ->post('https://api.openai.com/v1/audio/speech', [
                'model' => 'tts-1',
                'input' => $message,
                'voice' => 'alloy',
            ]);
    }
}
