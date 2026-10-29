<?php

use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->member = User::factory()->create();
    $this->tenant->users()->attach($this->member, ['role' => 'owner']);
});

it('lists only the units of the current tenant, ordered by code', function () {
    Unit::factory()->for($this->tenant)->create(['code' => 'A-02']);
    Unit::factory()->for($this->tenant)->create(['code' => 'A-01']);
    Unit::factory()->create(['code' => 'X-01']); // belongs to another tenant

    $this->actingAs($this->member)
        ->get("/{$this->tenant->slug}/units")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('tenant/units/index')
            ->has('units', 2)
            ->where('units.0.code', 'A-01')
            ->where('units.1.code', 'A-02')
            ->where('units.0.type_label', Unit::firstWhere('code', 'A-01')->type->label())
        );
});

it('forbids users that do not belong to the tenant', function () {
    $this->actingAs(User::factory()->create())
        ->get("/{$this->tenant->slug}/units")
        ->assertForbidden();
});
