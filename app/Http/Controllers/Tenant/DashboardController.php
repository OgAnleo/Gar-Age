<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Presenters\DashboardPresenter;
use App\Http\Presenters\TenantLabels;
use GarAge\Customers\Application\ListCustomers;
use GarAge\Dashboard\Application\GetDashboard;
use GarAge\Units\Application\ListUnits;
use Inertia\Inertia;
use Inertia\Response;

/**
 * HTTP adapter: shows the dashboard figures of the current tenant today.
 */
class DashboardController extends TenantController
{
    public function __invoke(
        GetDashboard $getDashboard,
        ListUnits $listUnits,
        ListCustomers $listCustomers,
        DashboardPresenter $presenter,
    ): Response {
        $tenantId = $this->tenantId();
        $labels = TenantLabels::from($listUnits->handle($tenantId), $listCustomers->handle($tenantId));

        return Inertia::render('tenant/dashboard', $presenter->present(
            $getDashboard->handle($tenantId, $this->today()),
            $labels,
            $this->today(),
        ));
    }
}
