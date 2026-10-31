<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Requests\Tenant\PublicPageRequest;
use GarAge\Catalog\Application\GetPublicPage;
use GarAge\Catalog\Application\UpdatePublicPage;
use GarAge\Shared\Domain\UserId;
use GarAge\Tenancy\Application\DeleteBusiness;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * HTTP adapter: the business page of the current tenant — its public page
 * settings and its deletion (owners only).
 */
class BusinessController extends TenantController
{
    public function edit(GetPublicPage $getPublicPage): Response
    {
        $page = $getPublicPage->handle($this->tenantId());

        return Inertia::render('tenant/business', [
            'public_page' => [
                'enabled' => $page->enabled,
                'contact_phone' => $page->phone->value ?? '',
                'contact_email' => $page->email->value ?? '',
                'url' => route('tenant.catalog'),
            ],
        ]);
    }

    /**
     * Publish or hide the public page and set its contact details.
     */
    public function updatePublicPage(PublicPageRequest $request, UpdatePublicPage $updatePublicPage): RedirectResponse
    {
        $updatePublicPage->handle($this->tenantId(), $request->enabled(), $request->phone(), $request->email());

        return $this->redirectWithToast('tenant.business.edit', $request->enabled()
            ? 'Página pública guardada y visible.'
            : 'Página pública guardada y oculta.');
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
