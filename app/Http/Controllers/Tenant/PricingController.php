<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Requests\Tenant\PricingRequest;
use GarAge\Pricing\Application\GetPricingConditions;
use GarAge\Pricing\Application\UpdatePricingConditions;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * HTTP adapter: the owner sets the discount of each recurring booking mode.
 */
class PricingController extends TenantController
{
    /**
     * Show the current discounts.
     */
    public function edit(GetPricingConditions $getPricingConditions): Response
    {
        $conditions = $getPricingConditions->handle($this->tenantId());

        return Inertia::render('tenant/pricing/edit', [
            'weekly_discount' => $conditions->weekly->value,
            'monthly_discount' => $conditions->monthly->value,
            'annual_discount' => $conditions->annual->value,
        ]);
    }

    /**
     * Save the discounts; existing contracts keep their agreed price.
     */
    public function update(PricingRequest $request, UpdatePricingConditions $updatePricingConditions): RedirectResponse
    {
        $updatePricingConditions->handle($this->tenantId(), $request->weekly(), $request->monthly(), $request->annual());

        return $this->redirectWithToast('tenant.pricing.edit', 'Descuentos guardados. Se aplicarán a las nuevas reservas.');
    }
}
