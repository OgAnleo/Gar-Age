<?php

use GarAge\Contracts\Application\ContractInput;
use GarAge\Contracts\Application\ContractRules;
use GarAge\Contracts\Application\CreateContract;
use GarAge\Contracts\Application\DeleteContract;
use GarAge\Contracts\Application\GetContract;
use GarAge\Contracts\Application\ListContracts;
use GarAge\Contracts\Application\UpdateContract;
use GarAge\Contracts\Domain\Contract;
use GarAge\Contracts\Domain\ContractId;
use GarAge\Contracts\Domain\ContractInUse;
use GarAge\Contracts\Domain\ContractNotFound;
use GarAge\Contracts\Domain\UnitAlreadyRented;
use GarAge\Contracts\Domain\UnitAvailability;
use GarAge\Contracts\Infrastructure\ContractsCustomerUsage;
use GarAge\Contracts\Infrastructure\ContractsUnitUsage;
use GarAge\Customers\Application\CustomerInput;
use GarAge\Customers\Domain\Customer;
use GarAge\Customers\Domain\CustomerId;
use GarAge\Customers\Domain\CustomerNotFound;
use GarAge\Pricing\Application\UpdatePricingConditions;
use GarAge\Shared\Domain\InvalidValue;
use GarAge\Tenancy\Domain\TenantId;
use GarAge\Units\Application\UnitInput;
use GarAge\Units\Domain\Unit;
use GarAge\Units\Domain\UnitId;
use GarAge\Units\Domain\UnitNotFound;
use Tests\Doubles\FakeUsage;
use Tests\Doubles\InMemoryContractRepository;
use Tests\Doubles\InMemoryCustomerRepository;
use Tests\Doubles\InMemoryPricingConditionsRepository;
use Tests\Doubles\InMemoryUnitRepository;

function monthly(int $unit = 1, int $customer = 1, string $start = '2026-01-01', ?int $periods = 6): ContractInput
{
    return new ContractInput(unitId: $unit, customerId: $customer, mode: 'monthly', startsOn: $start, periods: $periods);
}

/**
 * @param  list<string>  $days
 */
function specificDays(array $days, int $unit = 1): ContractInput
{
    return new ContractInput(unitId: $unit, customerId: 1, mode: 'days', days: $days);
}

beforeEach(function () {
    $this->tenantA = new TenantId(1);
    $this->tenantB = new TenantId(2);

    // Unit 1 and customer 1 belong to tenant A; unit 2 and customer 2 to tenant B. Daily rate: 2 €.
    $units = new InMemoryUnitRepository;
    $customers = new InMemoryCustomerRepository;
    foreach ([$this->tenantA, $this->tenantB] as $tenant) {
        $units->add(Unit::create($tenant, (new UnitInput('G-01', 'garage', '10', '2'))->toDetails()));
        $customers->add(Customer::create($tenant, (new CustomerInput('Ana', '12345678Z'))->toDetails()));
    }

    $pricing = new InMemoryPricingConditionsRepository;
    $this->setDiscounts = fn (int $weekly, int $monthly, int $annual) => (new UpdatePricingConditions($pricing))
        ->handle($this->tenantA, $weekly, $monthly, $annual);

    $this->contracts = new InMemoryContractRepository;
    $rules = new ContractRules($units, $customers, new UnitAvailability($this->contracts), $pricing);
    $getContract = new GetContract($this->contracts);

    $this->create = new CreateContract($this->contracts, $rules);
    $this->list = new ListContracts($this->contracts);
    $this->get = $getContract;
    $this->update = new UpdateContract($getContract, $this->contracts, $rules);
    $this->usage = new FakeUsage;
    $this->delete = new DeleteContract($getContract, $this->contracts, $this->usage);

    $this->price = fn (ContractId $id) => $this->get->handle($this->tenantA, $id)->details()->pricePerPeriod->cents();
});

it('prices new contracts with the current discounts of the business', function () {
    ($this->setDiscounts)(10, 20, 30);

    $weekly = $this->create->handle($this->tenantA, new ContractInput(1, 1, 'weekly', '2026-01-01', 1));
    $annual = $this->create->handle($this->tenantA, new ContractInput(1, 1, 'annual', '2026-02-01', 1));
    $days = $this->create->handle($this->tenantA, specificDays(['2027-03-01', '2027-03-05', '2027-03-09']));

    expect(($this->price)($weekly))->toBe(1260)   // 7 × 2 € − 10%
        ->and(($this->price)($annual))->toBe(51100) // 365 × 2 € − 30%
        ->and(($this->price)($days))->toBe(600);    // 3 × 2 €, no discount
});

it('keeps the agreed price when the discounts change later', function () {
    ($this->setDiscounts)(0, 0, 0);
    $id = $this->create->handle($this->tenantA, monthly());

    ($this->setDiscounts)(0, 50, 0);
    $this->update->handle($this->tenantA, $id, monthly(start: '2026-02-01', periods: 3));

    expect(($this->price)($id))->toBe(6000);
});

it('reprices a contract when its booking mode changes', function () {
    $id = $this->create->handle($this->tenantA, monthly());
    ($this->setDiscounts)(0, 0, 25);

    $this->update->handle($this->tenantA, $id, new ContractInput(1, 1, 'annual', '2026-01-01', 1));

    expect(($this->price)($id))->toBe(54750); // 365 × 2 € − 25%
});

it('lists contracts newest first', function () {
    $this->create->handle($this->tenantA, monthly(start: '2026-01-01', periods: 1));
    $this->create->handle($this->tenantA, monthly(start: '2026-02-01', periods: null));

    $starts = array_map(fn (Contract $c) => $c->details()->schedule->span()->startString(), $this->list->handle($this->tenantA));

    expect($starts)->toBe(['2026-02-01', '2026-01-01'])
        ->and($this->list->handle($this->tenantB))->toBe([]);
});

it('rejects a contract taking the unit on a day already booked', function () {
    $this->create->handle($this->tenantA, monthly(periods: null));

    expect(fn () => $this->create->handle($this->tenantA, specificDays(['2030-05-01'])))
        ->toThrow(UnitAlreadyRented::class);
});

it('lets bookings of specific days share a unit on different days', function () {
    $this->create->handle($this->tenantA, specificDays(['2026-10-05', '2026-10-20']));
    $this->create->handle($this->tenantA, specificDays(['2026-10-06', '2026-10-19']));

    expect($this->list->handle($this->tenantA))->toHaveCount(2)
        ->and(fn () => $this->create->handle($this->tenantA, specificDays(['2026-10-20'])))->toThrow(UnitAlreadyRented::class);
});

it('rejects units, customers and modes that are not valid', function () {
    expect(fn () => $this->create->handle($this->tenantA, monthly(unit: 2)))->toThrow(UnitNotFound::class)
        ->and(fn () => $this->create->handle($this->tenantA, monthly(customer: 2)))->toThrow(CustomerNotFound::class)
        ->and(fn () => $this->create->handle($this->tenantA, new ContractInput(1, 1, 'hourly', '2026-01-01')))->toThrow(InvalidValue::class)
        ->and(fn () => $this->create->handle($this->tenantA, new ContractInput(1, 1, 'weekly')))->toThrow(InvalidValue::class);
});

it('updates a contract without clashing with itself', function () {
    $id = $this->create->handle($this->tenantA, monthly(start: '2026-01-01', periods: 6));
    $other = $this->create->handle($this->tenantA, monthly(start: '2026-09-01', periods: null));

    $this->update->handle($this->tenantA, $id, monthly(start: '2026-01-01', periods: 8));

    expect($this->get->handle($this->tenantA, $id)->details()->schedule->span()->endString())->toBe('2026-08-31')
        ->and(fn () => $this->update->handle($this->tenantA, $other, monthly(start: '2026-08-01', periods: null)))
        ->toThrow(UnitAlreadyRented::class);
});

it('hides the contracts of other tenants', function () {
    $id = $this->create->handle($this->tenantA, monthly());

    expect(fn () => $this->get->handle($this->tenantB, $id))->toThrow(ContractNotFound::class)
        ->and(fn () => $this->update->handle($this->tenantB, $id, monthly(2, 2)))->toThrow(ContractNotFound::class)
        ->and(fn () => $this->delete->handle($this->tenantB, $id))->toThrow(ContractNotFound::class);
});

it('deletes a contract unless it is still in use', function () {
    $kept = $this->create->handle($this->tenantA, monthly(start: '2026-01-01', periods: 1));
    $deleted = $this->create->handle($this->tenantA, monthly(start: '2026-03-01', periods: 1));

    $this->delete->handle($this->tenantA, $deleted);
    $this->usage->inUse = true;

    expect(fn () => $this->delete->handle($this->tenantA, $kept))->toThrow(ContractInUse::class)
        ->and($this->list->handle($this->tenantA))->toHaveCount(1);
});

it('reports units and customers with contracts as in use', function () {
    $this->create->handle($this->tenantA, monthly());
    $unitUsage = new ContractsUnitUsage($this->contracts);
    $customerUsage = new ContractsCustomerUsage($this->contracts);

    expect($unitUsage->isInUse($this->tenantA, new UnitId(1)))->toBeTrue()
        ->and($unitUsage->isInUse($this->tenantB, new UnitId(2)))->toBeFalse()
        ->and($customerUsage->isInUse($this->tenantA, new CustomerId(1)))->toBeTrue()
        ->and($customerUsage->isInUse($this->tenantB, new CustomerId(2)))->toBeFalse();
});
