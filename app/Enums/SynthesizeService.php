<?php

declare(strict_types=1);

namespace App\Enums;

enum SynthesizeService: string
{
    case Google = 'google';
    case OpenAI = 'openai';
}
