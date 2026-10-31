<?php

use App\Models\Contract;
use App\Models\Customer;
use App\Models\Fee;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-11-10'));
});

it('builds a demo business with paid, pending and overdue fees, again and again', function () {
    $this->seed(DemoSeeder::class);
    $this->seed(DemoSeeder::class);

    $tenant = Tenant::sole();
    $fees = Fee::withoutGlobalScopes()->get();

    expect(User::where('email', config('demo.email'))->count())->toBe(1)
        ->and(Unit::withoutGlobalScopes()->count())->toBe(12)
        ->and(Customer::withoutGlobalScopes()->count())->toBe(8)
        ->and(Contract::withoutGlobalScopes()->count())->toBe(9)
        ->and($fees->whereNotNull('paid_on')->count())->toBeGreaterThan(5)
        ->and($fees->whereNull('paid_on')->count())->toBeGreaterThan(2);

    $this->actingAs(User::where('email', config('demo.email'))->sole())
        ->get("/{$tenant->slug}/dashboard")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('occupancy.total', 12)
            ->where('overdue.count', 2)
            ->where('pending.count', fn (int $count) => $count > 0)
        );
});
