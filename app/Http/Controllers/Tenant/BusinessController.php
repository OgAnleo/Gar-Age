<?php

namespace App\Http\Controllers\Tenant;

use GarAge\Shared\Domain\UserId;
use GarAge\Tenancy\Application\DeleteBusiness;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * HTTP adapter: the business page of the current tenant and its deletion.
 */
class BusinessController extends TenantController
{
    public function edit(): Response
    {
        return Inertia::render('tenant/business');
    }

    /**
     * Delete the business with all of its data (owners only).
     */
    public function destroy(Request $request, DeleteBusiness $deleteBusiness): RedirectResponse
    {
        $deleteBusiness->handle($this->tenantId(), new UserId($request->user()->id));

        return $this->redirectWithToast('dashboard', 'Negocio eliminado.');
    }
}
