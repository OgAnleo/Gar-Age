<?php

use App\Models\Contract;
use App\Models\Customer;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create(['monthly_discount_percent' => 20]);
    $this->member = User::factory()->create();
    $this->tenant->users()->attach($this->member, ['role' => 'owner']);

    $this->unit = Unit::factory()->for($this->tenant)->create(['daily_price_cents' => 300]);
    $this->customer = Customer::factory()->for($this->tenant)->create();

    $this->url = "/{$this->tenant->slug}/contracts";
    $this->payload = [
        'unit_id' => $this->unit->id,
        'customer_id' => $this->customer->id,
        'mode' => 'monthly',
        'starts_on' => '2026-01-15',
        'periods' => '',
    ];
});

it('creates an indefinite monthly contract priced with the business discount', function () {
    $this->actingAs($this->member)
        ->post($this->url, $this->payload)
        ->assertSessionHasNoErrors()
        ->assertRedirect($this->url);

    $contract = Contract::sole();

    expect($contract->tenant_id)->toBe($this->tenant->id)
        ->and($contract->mode->value)->toBe('monthly')
        ->and($contract->ends_on)->toBeNull()
        ->and($contract->period_count)->toBeNull()
        ->and($contract->price_per_period_cents)->toBe(7200); // 30 × 3 € − 20%
});

it('creates a fixed number of periods', function () {
    $this->actingAs($this->member)
        ->post($this->url, [...$this->payload, 'periods' => '3'])
        ->assertSessionHasNoErrors();

    expect(Contract::sole()->ends_on)->toBe('2026-04-14');
});

it('books specific days at the daily rate', function () {
    $this->actingAs($this->member)
        ->post($this->url, [...$this->payload, 'mode' => 'days', 'starts_on' => '', 'days' => ['2026-03-09', '2026-03-02']])
        ->assertSessionHasNoErrors();

    $contract = Contract::sole();

    expect($contract->days->pluck('day')->all())->toBe(['2026-03-02', '2026-03-09'])
        ->and([$contract->starts_on, $contract->ends_on])->toBe(['2026-03-02', '2026-03-09'])
        ->and($contract->price_per_period_cents)->toBe(600);
});

it('rejects dates already booked for the unit', function () {
    Contract::factory()->for($this->tenant)->onDays(['2026-02-10'])->create(['unit_id' => $this->unit->id]);

    $this->actingAs($this->member)
        ->post($this->url, $this->payload)
        ->assertSessionHasErrors(['dates' => 'La unidad ya está reservada en alguna de esas fechas.']);
});

it('explains validation errors in Spanish', function () {
    $this->actingAs($this->member)
        ->post($this->url, [...$this->payload, 'unit_id' => '', 'starts_on' => '', 'periods' => '0'])
        ->assertSessionHasErrors([
            'unit_id' => 'Elige una unidad.',
            'starts_on' => 'La fecha de inicio es obligatoria.',
            'periods' => 'La duración debe ser un número entero entre 1 y 120.',
        ]);

    $this->actingAs($this->member)
        ->post($this->url, [...$this->payload, 'mode' => 'days'])
        ->assertSessionHasErrors(['days' => 'Añade al menos un día.']);

    $this->actingAs($this->member)
        ->post($this->url, [...$this->payload, 'mode' => 'hourly'])
        ->assertSessionHasErrors(['mode' => 'La modalidad no es válida.']);
});

it('returns 404 for a unit of another tenant', function () {
    $foreignUnit = Unit::factory()->create();

    $this->actingAs($this->member)
        ->post($this->url, [...$this->payload, 'unit_id' => $foreignUnit->id])
        ->assertNotFound();

    expect(Contract::count())->toBe(0);
});
