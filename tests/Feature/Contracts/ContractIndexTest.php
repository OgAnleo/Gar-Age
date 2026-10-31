<?php

use App\Models\Contract;
use App\Models\Customer;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->member = User::factory()->create();
    $this->tenant->users()->attach($this->member, ['role' => 'owner']);
});

it('lists the contracts of the current tenant with labels and status', function () {
    $this->travelTo(Carbon::parse('2026-05-15'));
    $unit = Unit::factory()->for($this->tenant)->create(['code' => 'G-07']);
    $customer = Customer::factory()->for($this->tenant)->create(['name' => 'Ana López']);
    Contract::factory()->for($this->tenant)->create([
        'unit_id' => $unit->id,
        'customer_id' => $customer->id,
        'starts_on' => '2026-01-01',
        'ends_on' => '2026-03-31',
    ]);
    Contract::factory()->for($this->tenant)->create(['starts_on' => '2026-05-01']);
    Contract::factory()->create(); // belongs to another tenant

    $this->actingAs($this->member)
        ->get("/{$this->tenant->slug}/contracts")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('tenant/contracts/index')
            ->has('contracts', 2)
            ->where('contracts.0.status', 'active')
            ->where('contracts.1.unit_code', 'G-07')
            ->where('contracts.1.customer_name', 'Ana López')
            ->where('contracts.1.status_label', 'Finalizado')
        );
});

it('shows the create form with the units and customers of the tenant', function () {
    Unit::factory()->for($this->tenant)->create(['code' => 'G-01', 'monthly_price_cents' => 7999]);
    Customer::factory()->for($this->tenant)->create();
    Unit::factory()->create(); // another tenant

    $this->actingAs($this->member)
        ->get("/{$this->tenant->slug}/contracts/create")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('tenant/contracts/create')
            ->has('units', 1)
            ->where('units.0.label', 'G-01')
            ->where('units.0.monthly_price', '79.99')
            ->has('customers', 1)
        );
});
