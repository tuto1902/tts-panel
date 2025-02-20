<?php

declare(strict_types=1);

namespace App\Facades;

use App\Services\TwitchService;
use Illuminate\Support\Facades\Facade;

final class Twitch extends Facade
{
    protected static function getFacadeAccessor()
    {
        return TwitchService::class;
    }
}
