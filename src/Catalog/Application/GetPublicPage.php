<?php

namespace GarAge\Catalog\Application;

use GarAge\Catalog\Domain\PublicPage;
use GarAge\Catalog\Domain\PublicPageRepository;
use GarAge\Tenancy\Domain\TenantId;

/**
 * Use case: a business's public page settings.
 */
final readonly class GetPublicPage
{
    public function __construct(private PublicPageRepository $pages) {}

    public function handle(TenantId $tenantId): PublicPage
    {
        return $this->pages->forTenant($tenantId);
    }
}
