<?php

use App\Models\Contract;
use App\Models\Customer;
use App\Models\Fee;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-11-10 12:00'));

    $this->tenant = Tenant::factory()->create();
    $this->member = User::factory()->create();
    $this->tenant->users()->attach($this->member, ['role' => 'owner']);
    $this->url = "/{$this->tenant->slug}/dashboard";

    $this->contract = Contract::factory()->for($this->tenant)->create([
        'unit_id' => Unit::factory()->for($this->tenant)->create(['code' => 'G-07']),
        'customer_id' => Customer::factory()->for($this->tenant)->create(['name' => 'Ana López']),
        'mode' => 'monthly',
        'starts_on' => '2026-10-01',
        'price_per_period_cents' => 7999,
    ]);
    Unit::factory()->for($this->tenant)->create(['code' => 'G-08', 'type' => 'storage', 'daily_price_cents' => 250]);
});

it('shows the figures of the current tenant today', function () {
    $fee = ['contract_id' => $this->contract->id, 'amount_cents' => 7999];
    Fee::factory()->for($this->tenant)->create([...$fee, 'period_start' => '2026-10-01', 'period_end' => '2026-10-31']);
    Fee::factory()->for($this->tenant)->create([...$fee, 'period_start' => '2026-11-01', 'period_end' => '2026-11-30', 'paid_on' => '2026-11-02']);
    Fee::factory()->create(['paid_on' => '2026-11-03']); // another tenant

    $this->actingAs($this->member)
        ->get($this->url)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('tenant/dashboard')
            ->where('occupancy', ['occupied' => 1, 'total' => 2, 'percent' => 50])
            ->where('revenue.collected_cents', 7999)
            ->where('revenue.issued_cents', 7999)
            ->has('revenue.months', 6)
            ->where('revenue.months.5', ['month' => '2026-11', 'collected_cents' => 7999])
            ->where('pending', ['count' => 0, 'amount_cents' => 0])
            ->where('overdue', ['count' => 1, 'amount_cents' => 7999])
            ->has('overdue_fees', 1)
            ->where('overdue_fees.0.unit_code', 'G-07')
            ->where('overdue_fees.0.customer_name', 'Ana López')
            ->where('overdue_fees.0.due_on', '2026-10-08')
            ->where('overdue_fees.0.days_overdue', 33)
            ->has('free_units', 1)
            ->where('free_units.0.code', 'G-08')
            ->where('free_units.0.type_label', 'Trastero')
            ->where('free_units.0.daily_price_cents', 250)
        );
});

it('keeps only the first rows of its lists', function () {
    Unit::factory()->for($this->tenant)->count(6)->create();

    $this->actingAs($this->member)
        ->get($this->url)
        ->assertInertia(fn (Assert $page) => $page
            ->where('occupancy.total', 8)
            ->has('free_units', 5)
        );
});

it('is only for members of the business', function () {
    $this->get($this->url)->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create())->get($this->url)->assertForbidden();
});
