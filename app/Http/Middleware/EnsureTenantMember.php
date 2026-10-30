<?php

namespace App\Http\Middleware;

use App\Tenancy\CurrentTenant;
use Closure;
use GarAge\Shared\Domain\UserId;
use GarAge\Tenancy\Application\CheckMembership;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantMember
{
    public function __construct(
        private CurrentTenant $currentTenant,
        private CheckMembership $checkMembership,
    ) {}

    /**
     * Abort with 403 unless the authenticated user belongs to the current tenant.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = $this->currentTenant->get();
        $user = $request->user();

        abort_unless(
            $tenant !== null && $user !== null && $this->checkMembership->handle($tenant->id, new UserId($user->id)),
            403,
        );

        return $next($request);
    }
}
