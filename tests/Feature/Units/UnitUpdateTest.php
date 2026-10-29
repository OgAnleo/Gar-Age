<?php

use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->member = User::factory()->create();
    $this->tenant->users()->attach($this->member, ['role' => 'owner']);

    $this->unit = Unit::factory()->for($this->tenant)->create([
        'code' => 'G-01',
        'monthly_price_cents' => 7999,
    ]);
    $this->url = "/{$this->tenant->slug}/units/{$this->unit->id}";
    $this->payload = [
        'code' => 'G-01',
        'type' => 'storage',
        'size_m2' => '8',
        'monthly_price' => '50.5',
    ];
});

it('shows the edit form with the unit data', function () {
    $this->actingAs($this->member)
        ->get("{$this->url}/edit")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('tenant/units/edit')
            ->where('unit.code', 'G-01')
            ->where('unit.monthly_price', '79.99')
            ->has('types')
        );
});

it('updates the unit keeping its own code', function () {
    $this->actingAs($this->member)
        ->put($this->url, $this->payload)
        ->assertSessionHasNoErrors()
        ->assertRedirect("/{$this->tenant->slug}/units");

    $this->unit->refresh();

    expect($this->unit->type->value)->toBe('storage')
        ->and($this->unit->monthly_price_cents)->toBe(5050);
});

it('rejects a code used by another unit of the same tenant', function () {
    Unit::factory()->for($this->tenant)->create(['code' => 'G-02']);

    $this->actingAs($this->member)
        ->put($this->url, [...$this->payload, 'code' => 'G-02'])
        ->assertSessionHasErrors('code');
});

it('returns 404 for a unit of another tenant', function () {
    $foreign = Unit::factory()->create();
    $foreignUrl = "/{$this->tenant->slug}/units/{$foreign->id}";

    $this->actingAs($this->member)->get("{$foreignUrl}/edit")->assertNotFound();
    $this->actingAs($this->member)->put($foreignUrl, $this->payload)->assertNotFound();
});
