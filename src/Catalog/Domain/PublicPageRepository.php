<?php

namespace GarAge\Catalog\Domain;

use GarAge\Tenancy\Domain\TenantId;

/**
 * Port: persistence of each business's public page settings.
 */
interface PublicPageRepository
{
    public function forTenant(TenantId $tenantId): PublicPage;

    public function save(TenantId $tenantId, PublicPage $page): void;
}
