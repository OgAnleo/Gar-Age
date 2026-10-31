<?php

use App\Models\Customer;
use App\Models\Tenant;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->member = User::factory()->create();
    $this->tenant->users()->attach($this->member, ['role' => 'owner']);
});

it('lists only the customers of the current tenant, ordered by name', function () {
    Customer::factory()->for($this->tenant)->create(['name' => 'Bea Ruiz']);
    Customer::factory()->for($this->tenant)->create(['name' => 'Ana López', 'email' => null]);
    Customer::factory()->create(['name' => 'Carlos Otro']); // belongs to another tenant

    $this->actingAs($this->member)
        ->get("/{$this->tenant->slug}/customers")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('tenant/customers/index')
            ->has('customers', 2)
            ->where('customers.0.name', 'Ana López')
            ->where('customers.0.email', null)
            ->where('customers.1.name', 'Bea Ruiz')
        );
});

it('forbids users that do not belong to the tenant', function () {
    $this->actingAs(User::factory()->create())
        ->get("/{$this->tenant->slug}/customers")
        ->assertForbidden();
});
