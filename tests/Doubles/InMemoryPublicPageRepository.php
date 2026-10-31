<?php

namespace Tests\Doubles;

use GarAge\Catalog\Domain\PublicPage;
use GarAge\Catalog\Domain\PublicPageRepository;
use GarAge\Tenancy\Domain\TenantId;

/**
 * Test adapter: PublicPageRepository port kept in memory (no database).
 */
final class InMemoryPublicPageRepository implements PublicPageRepository
{
    /** @var array<int, PublicPage> */
    private array $pages = [];

    public function forTenant(TenantId $tenantId): PublicPage
    {
        return $this->pages[$tenantId->value] ?? PublicPage::unpublished();
    }

    public function save(TenantId $tenantId, PublicPage $page): void
    {
        $this->pages[$tenantId->value] = $page;
    }
}
