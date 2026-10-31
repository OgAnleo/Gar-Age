<?php

use App\Models\Contract;
use App\Models\Customer;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->member = User::factory()->create();
    $this->tenant->users()->attach($this->member, ['role' => 'owner']);

    $this->unit = Unit::factory()->for($this->tenant)->create();
    $this->customer = Customer::factory()->for($this->tenant)->create();

    $this->url = "/{$this->tenant->slug}/contracts";
    $this->payload = [
        'unit_id' => $this->unit->id,
        'customer_id' => $this->customer->id,
        'starts_on' => '2026-01-01',
        'ends_on' => '',
        'monthly_price' => '79.99',
    ];
});

it('creates an open-ended contract for the current tenant', function () {
    $this->actingAs($this->member)
        ->post($this->url, $this->payload)
        ->assertSessionHasNoErrors()
        ->assertRedirect($this->url);

    $contract = Contract::sole();

    expect($contract->tenant_id)->toBe($this->tenant->id)
        ->and($contract->ends_on)->toBeNull()
        ->and($contract->monthly_price_cents)->toBe(7999);
});

it('rejects dates overlapping another contract of the unit', function () {
    Contract::factory()->for($this->tenant)->create([
        'unit_id' => $this->unit->id,
        'starts_on' => '2025-06-01',
        'ends_on' => '2026-01-15',
    ]);

    $this->actingAs($this->member)
        ->post($this->url, $this->payload)
        ->assertSessionHasErrors(['starts_on' => 'La unidad ya tiene un contrato en esas fechas.']);
});

it('allows a contract right after the previous one ends', function () {
    Contract::factory()->for($this->tenant)->create([
        'unit_id' => $this->unit->id,
        'starts_on' => '2025-06-01',
        'ends_on' => '2025-12-31',
    ]);

    $this->actingAs($this->member)
        ->post($this->url, $this->payload)
        ->assertSessionHasNoErrors();

    expect($this->tenant->contracts()->count())->toBe(2);
});

it('explains validation errors in Spanish', function () {
    $this->actingAs($this->member)
        ->post($this->url, [...$this->payload, 'unit_id' => '', 'starts_on' => '2026-02-01', 'ends_on' => '2026-01-01'])
        ->assertSessionHasErrors([
            'unit_id' => 'Elige una unidad.',
            'ends_on' => 'La fecha de fin no puede ser anterior a la de inicio.',
        ]);
});

it('returns 404 for a unit of another tenant', function () {
    $foreignUnit = Unit::factory()->create();

    $this->actingAs($this->member)
        ->post($this->url, [...$this->payload, 'unit_id' => $foreignUnit->id])
        ->assertNotFound();

    expect(Contract::count())->toBe(0);
});
