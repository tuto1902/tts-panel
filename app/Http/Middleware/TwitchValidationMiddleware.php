<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class TwitchValidationMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $messageId = $request->header('Twitch-Eventsub-Message-Id');
        $timestamp = $request->header('Twitch-Eventsub-Message-Timestamp');
        $signature = $request->header('Twitch-Eventsub-Message-Signature');
        if (! $messageId || ! $timestamp || ! $signature) {
            return response('missing header', 401);
        }

        $body = $request->getContent();
        $secret = config('services.twitch.webhook_secret');

        if (! $this->verifySignature($messageId, $timestamp, $body, $signature, $secret)) {
            return response('verification failed', 401);
        }

        return $next($request);
    }

    /**
     * Verify Twitch Webhook Signature.
     */
    private function verifySignature(string $messageId, string $timestamp, string $body, string $signature, string $secret): bool
    {
        $message = $messageId.$timestamp.$body;
        $hash = 'sha256='.hash_hmac('sha256', $message, $secret);

        return hash_equals($hash, $signature);
    }
}
