<?php

namespace App\Http\Controllers;

use App\Http\Presenters\UnitPresenter;
use App\Tenancy\CurrentTenant;
use GarAge\Catalog\Application\CatalogUnit;
use GarAge\Catalog\Application\GetPublicCatalog;
use GarAge\Shared\Domain\Money;
use Inertia\Inertia;
use Inertia\Response;

/**
 * HTTP adapter: the public page of a business (no login), listing its units
 * free today with their prices. 404 unless the business publishes it.
 */
class PublicCatalogController extends Controller
{
    public function __invoke(CurrentTenant $currentTenant, GetPublicCatalog $getPublicCatalog, UnitPresenter $units): Response
    {
        $tenant = $currentTenant->require();
        $catalog = $getPublicCatalog->handle($tenant->id, now()->toDateTimeImmutable());

        abort_if($catalog === null, 404);

        return Inertia::render('public/catalog', [
            'business' => [
                'name' => $tenant->name,
                'phone' => $catalog['page']->phone?->value,
                'email' => $catalog['page']->email?->value,
            ],
            'units' => array_map(fn (CatalogUnit $item): array => [
                ...$units->listItem($item->unit),
                'prices_cents' => array_map(fn (Money $price): int => $price->cents(), $item->prices),
            ], $catalog['units']),
        ]);
    }
}
