<?php

use App\Models\Tenant;
use App\Models\Unit;
use App\Tenancy\CurrentTenant;

beforeEach(function () {
    $this->tenantA = Tenant::factory()->create();
    $this->tenantB = Tenant::factory()->create();

    Unit::factory()->for($this->tenantA)->create(['code' => 'A-01']);
    Unit::factory()->for($this->tenantB)->create(['code' => 'B-01']);
});

it('only returns units of the current tenant', function () {
    app(CurrentTenant::class)->set($this->tenantA->toDomain());

    expect(Unit::pluck('code')->all())->toBe(['A-01']);
});

it('returns every unit when there is no current tenant', function () {
    expect(Unit::count())->toBe(2);
});

it('fills tenant_id from the current tenant on create', function () {
    app(CurrentTenant::class)->set($this->tenantB->toDomain());

    $unit = Unit::create([
        'code' => 'N-01',
        'type' => 'garage',
        'size_m2' => 12,
        'monthly_price_cents' => 8000,
    ]);

    expect($unit->tenant_id)->toBe($this->tenantB->id);
});

it('cannot find a unit of another tenant by id', function () {
    $unitB = Unit::where('code', 'B-01')->first();

    app(CurrentTenant::class)->set($this->tenantA->toDomain());

    expect(Unit::find($unitB->id))->toBeNull();
});

it('allows the same code in different tenants', function () {
    app(CurrentTenant::class)->set($this->tenantA->toDomain());

    $unit = Unit::create([
        'code' => 'B-01',
        'type' => 'storage',
        'size_m2' => 4,
        'monthly_price_cents' => 3500,
    ]);

    expect($unit->exists)->toBeTrue();
});
