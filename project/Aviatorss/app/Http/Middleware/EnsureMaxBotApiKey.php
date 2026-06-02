<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureMaxBotApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = config('services.max_bot.api_secret');
        if (! is_string($expected) || $expected === '') {
            abort(503, 'MAX bot API is not configured.');
        }

        $given = $request->header('X-Bot-Api-Key');
        if (! is_string($given) || ! hash_equals($expected, $given)) {
            abort(401, 'Unauthorized');
        }

        return $next($request);
    }
}
