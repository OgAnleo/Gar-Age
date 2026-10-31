<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Lets through only the external scheduler, which sends the shared secret
 * as a bearer token. Without a configured secret the endpoint does not exist.
 */
class VerifyCronToken
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = config('services.cron.token');

        abort_unless(is_string($token) && $token !== '', 404);
        abort_unless(hash_equals($token, (string) $request->bearerToken()), 401);

        return $next($request);
    }
}
