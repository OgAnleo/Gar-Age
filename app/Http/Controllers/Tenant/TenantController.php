<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Tenancy\CurrentTenant;
use GarAge\Tenancy\Domain\TenantId;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;

/**
 * Base for controllers of tenant sections: gives them the current tenant id
 * (passed explicitly to every use case) and the "back with a toast" response.
 */
abstract class TenantController extends Controller
{
    public function __construct(private CurrentTenant $currentTenant) {}

    protected function tenantId(): TenantId
    {
        return $this->currentTenant->require()->id;
    }

    protected function redirectWithToast(string $route, string $message): RedirectResponse
    {
        Inertia::flash('toast', ['type' => 'success', 'message' => $message]);

        return to_route($route);
    }
}
