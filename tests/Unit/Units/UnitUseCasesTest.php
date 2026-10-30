<?php

use GarAge\Tenancy\Domain\TenantId;
use GarAge\Units\Application\CreateUnit;
use GarAge\Units\Application\DeleteUnit;
use GarAge\Units\Application\GetUnit;
use GarAge\Units\Application\ListUnits;
use GarAge\Units\Application\UnitInput;
use GarAge\Units\Application\UpdateUnit;
use GarAge\Units\Domain\UniqueUnitCode;
use GarAge\Units\Domain\Unit;
use GarAge\Units\Domain\UnitCodeAlreadyUsed;
use GarAge\Units\Domain\UnitNotFound;
use Tests\Doubles\InMemoryUnitRepository;

function unitInput(string $code = 'G-01', string $price = '79.99'): UnitInput
{
    return new UnitInput(code: $code, type: 'garage', sizeM2: '12.5', monthlyPrice: $price);
}

beforeEach(function () {
    $units = new InMemoryUnitRepository;
    $uniqueCode = new UniqueUnitCode($units);
    $getUnit = new GetUnit($units);

    $this->create = new CreateUnit($units, $uniqueCode);
    $this->list = new ListUnits($units);
    $this->get = $getUnit;
    $this->update = new UpdateUnit($getUnit, $units, $uniqueCode);
    $this->delete = new DeleteUnit($getUnit, $units);

    $this->tenantA = new TenantId(1);
    $this->tenantB = new TenantId(2);
});

it('creates units and lists them ordered by code', function () {
    $this->create->handle($this->tenantA, unitInput('G-02'));
    $this->create->handle($this->tenantA, unitInput('G-01'));
    $this->create->handle($this->tenantB, unitInput('X-01'));

    $codes = array_map(fn (Unit $unit) => $unit->details()->code->value, $this->list->handle($this->tenantA));

    expect($codes)->toBe(['G-01', 'G-02']);
});

it('rejects a code already used in the same tenant', function () {
    $this->create->handle($this->tenantA, unitInput('G-01'));

    expect(fn () => $this->create->handle($this->tenantA, unitInput('G-01')))->toThrow(UnitCodeAlreadyUsed::class);
});

it('allows the same code in another tenant', function () {
    $this->create->handle($this->tenantA, unitInput('G-01'));
    $this->create->handle($this->tenantB, unitInput('G-01'));

    expect($this->list->handle($this->tenantB))->toHaveCount(1);
});

it('updates a unit keeping its own code but not taking another one', function () {
    $id = $this->create->handle($this->tenantA, unitInput('G-01'));
    $this->create->handle($this->tenantA, unitInput('G-02'));

    $this->update->handle($this->tenantA, $id, unitInput('G-01', '50.5'));

    expect($this->get->handle($this->tenantA, $id)->details()->monthlyPrice->cents())->toBe(5050)
        ->and(fn () => $this->update->handle($this->tenantA, $id, unitInput('G-02')))->toThrow(UnitCodeAlreadyUsed::class);
});

it('hides the units of other tenants', function () {
    $id = $this->create->handle($this->tenantA, unitInput());

    expect(fn () => $this->get->handle($this->tenantB, $id))->toThrow(UnitNotFound::class)
        ->and(fn () => $this->update->handle($this->tenantB, $id, unitInput()))->toThrow(UnitNotFound::class)
        ->and(fn () => $this->delete->handle($this->tenantB, $id))->toThrow(UnitNotFound::class);
});

it('deletes a unit', function () {
    $id = $this->create->handle($this->tenantA, unitInput());

    $this->delete->handle($this->tenantA, $id);

    expect($this->list->handle($this->tenantA))->toBe([]);
});
