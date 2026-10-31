<?php

use GarAge\Contracts\Application\ContractInput;
use GarAge\Contracts\Application\ContractRules;
use GarAge\Contracts\Application\CreateContract;
use GarAge\Contracts\Application\DeleteContract;
use GarAge\Contracts\Application\GetContract;
use GarAge\Contracts\Application\ListContracts;
use GarAge\Contracts\Application\UpdateContract;
use GarAge\Contracts\Domain\Contract;
use GarAge\Contracts\Domain\ContractNotFound;
use GarAge\Contracts\Domain\UnitAlreadyRented;
use GarAge\Contracts\Domain\UnitAvailability;
use GarAge\Contracts\Infrastructure\ContractsCustomerUsage;
use GarAge\Contracts\Infrastructure\ContractsUnitUsage;
use GarAge\Customers\Application\CustomerInput;
use GarAge\Customers\Domain\Customer;
use GarAge\Customers\Domain\CustomerId;
use GarAge\Customers\Domain\CustomerNotFound;
use GarAge\Tenancy\Domain\TenantId;
use GarAge\Units\Application\UnitInput;
use GarAge\Units\Domain\Unit;
use GarAge\Units\Domain\UnitId;
use GarAge\Units\Domain\UnitNotFound;
use Tests\Doubles\InMemoryContractRepository;
use Tests\Doubles\InMemoryCustomerRepository;
use Tests\Doubles\InMemoryUnitRepository;

function contractInput(int $unit = 1, int $customer = 1, string $start = '2026-01-01', ?string $end = '2026-06-30'): ContractInput
{
    return new ContractInput(unitId: $unit, customerId: $customer, startsOn: $start, endsOn: $end, monthlyPrice: '79.99');
}

beforeEach(function () {
    $this->tenantA = new TenantId(1);
    $this->tenantB = new TenantId(2);

    // Unit 1 and customer 1 belong to tenant A; unit 2 and customer 2 to tenant B.
    $units = new InMemoryUnitRepository;
    $customers = new InMemoryCustomerRepository;
    foreach ([$this->tenantA, $this->tenantB] as $tenant) {
        $units->add(Unit::create($tenant, (new UnitInput('G-01', 'garage', '10', '80'))->toDetails()));
        $customers->add(Customer::create($tenant, (new CustomerInput('Ana', '12345678Z'))->toDetails()));
    }

    $this->contracts = new InMemoryContractRepository;
    $rules = new ContractRules($units, $customers, new UnitAvailability($this->contracts));
    $getContract = new GetContract($this->contracts);

    $this->create = new CreateContract($this->contracts, $rules);
    $this->list = new ListContracts($this->contracts);
    $this->get = $getContract;
    $this->update = new UpdateContract($getContract, $this->contracts, $rules);
    $this->delete = new DeleteContract($getContract, $this->contracts);
});

it('creates contracts and lists them newest first', function () {
    $this->create->handle($this->tenantA, contractInput(start: '2026-01-01', end: '2026-01-31'));
    $this->create->handle($this->tenantA, contractInput(start: '2026-02-01', end: null));

    $starts = array_map(fn (Contract $c) => $c->details()->period->startString(), $this->list->handle($this->tenantA));

    expect($starts)->toBe(['2026-02-01', '2026-01-01'])
        ->and($this->list->handle($this->tenantB))->toBe([]);
});

it('rejects a contract overlapping another one of the same unit', function () {
    $this->create->handle($this->tenantA, contractInput(start: '2026-01-01', end: null));

    expect(fn () => $this->create->handle($this->tenantA, contractInput(start: '2027-01-01', end: '2027-12-31')))
        ->toThrow(UnitAlreadyRented::class);
});

it('rejects units and customers of another tenant', function () {
    expect(fn () => $this->create->handle($this->tenantA, contractInput(unit: 2)))->toThrow(UnitNotFound::class)
        ->and(fn () => $this->create->handle($this->tenantA, contractInput(customer: 2)))->toThrow(CustomerNotFound::class);
});

it('updates a contract without clashing with itself', function () {
    $id = $this->create->handle($this->tenantA, contractInput(start: '2026-01-01', end: '2026-06-30'));
    $other = $this->create->handle($this->tenantA, contractInput(start: '2026-09-01', end: null));

    $this->update->handle($this->tenantA, $id, contractInput(start: '2026-01-01', end: '2026-08-31'));

    expect($this->get->handle($this->tenantA, $id)->details()->period->endString())->toBe('2026-08-31')
        ->and(fn () => $this->update->handle($this->tenantA, $other, contractInput(start: '2026-08-01', end: null)))
        ->toThrow(UnitAlreadyRented::class);
});

it('hides the contracts of other tenants', function () {
    $id = $this->create->handle($this->tenantA, contractInput());

    expect(fn () => $this->get->handle($this->tenantB, $id))->toThrow(ContractNotFound::class)
        ->and(fn () => $this->update->handle($this->tenantB, $id, contractInput(2, 2)))->toThrow(ContractNotFound::class)
        ->and(fn () => $this->delete->handle($this->tenantB, $id))->toThrow(ContractNotFound::class);
});

it('deletes a contract', function () {
    $id = $this->create->handle($this->tenantA, contractInput());

    $this->delete->handle($this->tenantA, $id);

    expect($this->list->handle($this->tenantA))->toBe([]);
});

it('reports units and customers with contracts as in use', function () {
    $this->create->handle($this->tenantA, contractInput());
    $unitUsage = new ContractsUnitUsage($this->contracts);
    $customerUsage = new ContractsCustomerUsage($this->contracts);

    expect($unitUsage->isInUse($this->tenantA, new UnitId(1)))->toBeTrue()
        ->and($unitUsage->isInUse($this->tenantB, new UnitId(2)))->toBeFalse()
        ->and($customerUsage->isInUse($this->tenantA, new CustomerId(1)))->toBeTrue()
        ->and($customerUsage->isInUse($this->tenantB, new CustomerId(2)))->toBeFalse();
});
