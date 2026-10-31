<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\Response;

/**
 * On the public demo, whose credentials are published, keeps visitors from
 * locking the demo account or deleting its business: no changes to its
 * profile, password, second factor or passkeys. Everything else is allowed
 * (the demo data is rebuilt every night).
 */
class ProtectDemoAccount
{
    /** Routes that change the account's credentials or delete what it owns. */
    public const array PROTECTED_ROUTES = [
        'profile.update',
        'profile.destroy',
        'user-password.update',
        'two-factor.enable',
        'two-factor.confirm',
        'two-factor.disable',
        'two-factor.regenerate-recovery-codes',
        'passkey.store',
        'passkey.destroy',
        'tenant.business.destroy',
    ];

    public const string MESSAGE = 'Esta acción está desactivada en la cuenta de demostración.';

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! self::isDemoUser($request) || ! $request->routeIs(self::PROTECTED_ROUTES)) {
            return $next($request);
        }

        if ($request->expectsJson() && ! $request->inertia()) {
            abort(403, self::MESSAGE);
        }

        Inertia::flash('toast', ['type' => 'error', 'message' => self::MESSAGE]);

        return back();
    }

    public static function isDemoUser(Request $request): bool
    {
        return config('demo.enabled') === true && $request->user()?->email === config('demo.email');
    }
}
