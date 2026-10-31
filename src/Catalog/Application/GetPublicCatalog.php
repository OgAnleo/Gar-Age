<?php

namespace GarAge\Catalog\Application;

use DateTimeImmutable;
use GarAge\Catalog\Domain\PublicPage;
use GarAge\Catalog\Domain\PublicPageRepository;
use GarAge\Contracts\Domain\ContractRepository;
use GarAge\Contracts\Domain\TakenUnits;
use GarAge\Pricing\Domain\PricingConditionsRepository;
use GarAge\Tenancy\Domain\TenantId;
use GarAge\Units\Domain\Unit;
use GarAge\Units\Domain\UnitRepository;

/**
 * Use case: what a business shows the public — its units free today with
 * their current prices — or null when it does not publish a page.
 */
final readonly class GetPublicCatalog
{
    public function __construct(
        private PublicPageRepository $pages,
        private UnitRepository $units,
        private ContractRepository $contracts,
        private PricingConditionsRepository $pricing,
    ) {}

    /**
     * @return array{page: PublicPage, units: list<CatalogUnit>}|null
     */
    public function handle(TenantId $tenantId, DateTimeImmutable $today): ?array
    {
        $page = $this->pages->forTenant($tenantId);
        if (! $page->enabled) {
            return null;
        }

        $taken = TakenUnits::on($this->contracts->startedBy($today, $tenantId), $today);
        $conditions = $this->pricing->forTenant($tenantId);
        $free = array_filter($this->units->allOfTenant($tenantId), fn (Unit $unit): bool => ! isset($taken[$unit->id()->value]));

        return [
            'page' => $page,
            'units' => array_values(array_map(
                fn (Unit $unit): CatalogUnit => new CatalogUnit($unit, $conditions->priceList($unit->details()->dailyPrice)),
                $free,
            )),
        ];
    }
}
