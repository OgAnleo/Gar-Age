<?php

use GarAge\Catalog\Application\CatalogUnit;
use GarAge\Catalog\Application\GetPublicCatalog;
use GarAge\Catalog\Application\GetPublicPage;
use GarAge\Catalog\Application\UpdatePublicPage;
use GarAge\Contracts\Domain\Contract;
use GarAge\Contracts\Domain\ContractDetails;
use GarAge\Contracts\Domain\RecurringSchedule;
use GarAge\Contracts\Domain\SpecificDaysSchedule;
use GarAge\Customers\Domain\CustomerId;
use GarAge\Pricing\Domain\BookingMode;
use GarAge\Pricing\Domain\DiscountPercent;
use GarAge\Pricing\Domain\PricingConditions;
use GarAge\Shared\Domain\DateRange;
use GarAge\Shared\Domain\InvalidValue;
use GarAge\Shared\Domain\Money;
use GarAge\Tenancy\Domain\TenantId;
use GarAge\Units\Domain\Area;
use GarAge\Units\Domain\Unit;
use GarAge\Units\Domain\UnitCode;
use GarAge\Units\Domain\UnitDetails;
use GarAge\Units\Domain\UnitType;
use Tests\Doubles\InMemoryContractRepository;
use Tests\Doubles\InMemoryPricingConditionsRepository;
use Tests\Doubles\InMemoryPublicPageRepository;
use Tests\Doubles\InMemoryUnitRepository;

beforeEach(function () {
    $this->tenant = new TenantId(1);
    $other = new TenantId(2);

    $units = new InMemoryUnitRepository;
    $unit = fn (TenantId $tenant, string $code, string $rate) => $units->add(Unit::create(
        $tenant,
        new UnitDetails(new UnitCode($code), UnitType::Storage, Area::fromString('4'), Money::fromString($rate)),
    ));
    $rented = $unit($this->tenant, 'T-01', '2');
    $unit($this->tenant, 'T-02', '2');
    $dayOff = $unit($this->tenant, 'T-03', '1');
    $unit($other, 'B-01', '9');

    $contracts = new InMemoryContractRepository;
    $contract = fn ($unitId, $schedule) => $contracts->add(Contract::create(
        $this->tenant,
        new ContractDetails($unitId, new CustomerId(1), $schedule, Money::fromString('1')),
    ));
    $contract($rented, new RecurringSchedule(BookingMode::Monthly, DateRange::day('2026-10-01')));
    $contract($dayOff, SpecificDaysSchedule::fromStrings(['2026-11-09', '2026-11-11'])); // not today

    $pricing = new InMemoryPricingConditionsRepository;
    $pricing->save($this->tenant, new PricingConditions(new DiscountPercent(0), new DiscountPercent(10), new DiscountPercent(0)));

    $this->pages = new InMemoryPublicPageRepository;
    $this->update = new UpdatePublicPage($this->pages);
    $this->get = new GetPublicPage($this->pages);
    $this->catalog = new GetPublicCatalog($this->pages, $units, $contracts, $pricing);
    $this->today = new DateTimeImmutable('2026-11-10');
});

it('shows nothing until the business publishes its page', function () {
    expect($this->catalog->handle($this->tenant, $this->today))->toBeNull()
        ->and($this->get->handle($this->tenant)->enabled)->toBeFalse();
});

it('lists the units free today with their prices and the contact details', function () {
    $this->update->handle($this->tenant, true, '612 34 56 78', 'Hola@Trasteros.example');

    $catalog = $this->catalog->handle($this->tenant, $this->today);

    expect($catalog['page']->phone?->value)->toBe('612345678')
        ->and($catalog['page']->email?->value)->toBe('hola@trasteros.example')
        ->and(array_map(fn (CatalogUnit $item): string => $item->unit->details()->code->value, $catalog['units']))->toBe(['T-02', 'T-03'])
        ->and($catalog['units'][0]->prices['monthly']->cents())->toBe(5400) // 30 × 2 € − 10 %
        ->and($catalog['units'][0]->prices['days']->cents())->toBe(200);
});

it('keeps contact details optional but valid', function () {
    $this->update->handle($this->tenant, true, null, null);

    expect($this->get->handle($this->tenant)->phone)->toBeNull()
        ->and(fn () => $this->update->handle($this->tenant, true, '12', null))->toThrow(InvalidValue::class);
});
