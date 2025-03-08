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

class TextToSpeechService
{
    public function synthesize(string $message, SynthesizeService $service): string
    {
        switch ($service) {
            case SynthesizeService::Google:
                return $this->googleSynthesize($message);
            case SynthesizeService::OpenAI:
                return $this->openAISynthesize($message);
            default:
                return $this->googleSynthesize($message);
        }
    }

    public function googleSynthesize(string $message): string
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
        return base64_encode($response->getAudioContent());
    }

    public function openAISynthesize(string $message): string
    {
        // @codeCoverageIgnoreStart
        $response = Http::withToken(config('services.openai.secret'))
            ->post('https://api.openai.com/v1/audio/speech', [
                'model' => 'tts-1',
                'input' => $message,
                'voice' => 'alloy',
            ]);

        return base64_encode($response->body());
        // @codeCoverageIgnoreEnd
    }
}
