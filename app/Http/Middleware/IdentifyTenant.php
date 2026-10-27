<?php

namespace App\Http\Middleware;

use App\Models\Tenant;
use App\Tenancy\CurrentTenant;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    public function __construct(private CurrentTenant $currentTenant) {}

    /**
     * Resolve the tenant from the {tenant} route parameter and make it current.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $route = $request->route();
        $tenant = $route?->parameter('tenant');

        abort_unless($tenant instanceof Tenant, 404);

        $this->currentTenant->set($tenant);

        URL::defaults(['tenant' => $tenant->slug]);

        $route->forgetParameter('tenant');

        return $next($request);
    }
}
