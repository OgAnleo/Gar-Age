<?php

namespace GarAge\Catalog\Infrastructure;

use App\Models\Tenant as TenantModel;
use GarAge\Catalog\Domain\PublicPage;
use GarAge\Catalog\Domain\PublicPageRepository;
use GarAge\Customers\Domain\Email;
use GarAge\Customers\Domain\PhoneNumber;
use GarAge\Tenancy\Domain\TenantId;

/**
 * Adapter: the public page settings are columns of the tenants table.
 */
final class EloquentPublicPageRepository implements PublicPageRepository
{
    public function forTenant(TenantId $tenantId): PublicPage
    {
        $tenant = TenantModel::find($tenantId->value);

        return $tenant === null ? PublicPage::unpublished() : new PublicPage(
            $tenant->public_page_enabled,
            $tenant->contact_phone === null ? null : new PhoneNumber($tenant->contact_phone),
            $tenant->contact_email === null ? null : new Email($tenant->contact_email),
        );
    }

    public function save(TenantId $tenantId, PublicPage $page): void
    {
        TenantModel::whereKey($tenantId->value)->update([
            'public_page_enabled' => $page->enabled,
            'contact_phone' => $page->phone?->value,
            'contact_email' => $page->email?->value,
        ]);
    }
}
