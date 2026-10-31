<?php

use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use GarAge\Units\Domain\UnitType;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->member = User::factory()->create();
    $this->tenant->users()->attach($this->member, ['role' => 'owner']);

    $this->url = "/{$this->tenant->slug}/units";
    $this->payload = [
        'code' => 'G-01',
        'type' => 'garage',
        'size_m2' => '12.5',
        'daily_price' => '79.99',
    ];
});

it('shows the create form with the unit types', function () {
    $this->actingAs($this->member)
        ->get("{$this->url}/create")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('tenant/units/create')
            ->has('types', count(UnitType::cases()))
            ->where('types.0', ['value' => 'garage', 'label' => 'Garaje'])
        );
});

it('creates a unit for the current tenant', function () {
    $this->actingAs($this->member)
        ->post($this->url, $this->payload)
        ->assertSessionHasNoErrors()
        ->assertRedirect($this->url);

    $unit = Unit::firstWhere('code', 'G-01');

    expect($unit->tenant_id)->toBe($this->tenant->id)
        ->and($unit->daily_price_cents)->toBe(7999)
        ->and($unit->size_m2)->toBe('12.50');
});

it('rejects a code already used in the same tenant', function () {
    Unit::factory()->for($this->tenant)->create(['code' => 'G-01']);

    $this->actingAs($this->member)
        ->post($this->url, $this->payload)
        ->assertSessionHasErrors('code');
});

it('allows a code already used by another tenant', function () {
    Unit::factory()->create(['code' => 'G-01']);

    $this->actingAs($this->member)
        ->post($this->url, $this->payload)
        ->assertSessionHasNoErrors();

    expect($this->tenant->units()->where('code', 'G-01')->exists())->toBeTrue();
});

it('explains validation errors in Spanish', function () {
    $this->actingAs($this->member)
        ->post($this->url, [...$this->payload, 'code' => '', 'size_m2' => '1.234', 'daily_price' => 'abc'])
        ->assertSessionHasErrors([
            'code' => 'El código es obligatorio.',
            'size_m2' => 'La superficie admite como máximo 2 decimales.',
            'daily_price' => 'La tarifa diaria debe ser un número.',
        ]);
});

it('rejects an unknown type', function () {
    $this->actingAs($this->member)
        ->post($this->url, [...$this->payload, 'type' => 'castle'])
        ->assertSessionHasErrors('type');
});
