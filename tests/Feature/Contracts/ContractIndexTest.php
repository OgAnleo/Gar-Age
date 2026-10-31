<?php

use App\Models\Contract;
use App\Models\Customer;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create(['weekly_discount_percent' => 10, 'annual_discount_percent' => 30]);
    $this->member = User::factory()->create();
    $this->tenant->users()->attach($this->member, ['role' => 'owner']);
});

it('lists the contracts of the current tenant with labels, mode, price and status', function () {
    $this->travelTo(Carbon::parse('2026-05-15'));
    $unit = Unit::factory()->for($this->tenant)->create(['code' => 'G-07']);
    $customer = Customer::factory()->for($this->tenant)->create(['name' => 'Ana López']);
    Contract::factory()->for($this->tenant)->create([
        'unit_id' => $unit->id,
        'customer_id' => $customer->id,
        'mode' => 'weekly',
        'starts_on' => '2026-01-01',
        'ends_on' => '2026-01-14',
        'period_count' => 2,
        'price_per_period_cents' => 1260,
    ]);
    Contract::factory()->for($this->tenant)->onDays(['2026-05-01', '2026-05-20'])->create();
    Contract::factory()->create(); // belongs to another tenant

    $this->actingAs($this->member)
        ->get("/{$this->tenant->slug}/contracts")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('tenant/contracts/index')
            ->has('contracts', 2)
            ->where('contracts.0.mode', 'days')
            ->where('contracts.0.days_count', 2)
            ->where('contracts.0.status', 'active')
            ->where('contracts.1.unit_code', 'G-07')
            ->where('contracts.1.customer_name', 'Ana López')
            ->where('contracts.1.mode_label', 'Semanal')
            ->where('contracts.1.ends_on', '2026-01-14')
            ->where('contracts.1.price_cents', 1260)
            ->where('contracts.1.status_label', 'Finalizado')
        );
});

it('shows the create form with what each mode would cost for each unit', function () {
    Unit::factory()->for($this->tenant)->create(['code' => 'G-01', 'daily_price_cents' => 200]);
    Customer::factory()->for($this->tenant)->create();
    Unit::factory()->create(); // another tenant

    $this->actingAs($this->member)
        ->get("/{$this->tenant->slug}/contracts/create")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('tenant/contracts/create')
            ->has('units', 1)
            ->where('units.0.label', 'G-01')
            ->where('units.0.prices', ['days' => 200, 'annual' => 51100, 'monthly' => 6000, 'weekly' => 1260])
            ->has('customers', 1)
            ->has('modes', 4)
        );
});
