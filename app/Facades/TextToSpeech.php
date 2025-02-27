<?php

declare(strict_types=1);

namespace App\Facades;

use App\Services\TextToSpeechService;
use Illuminate\Support\Facades\Facade;

final class TextToSpeech extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return TextToSpeechService::class;
    }
}
