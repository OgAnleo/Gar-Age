<?php

use App\Models\Contract;
use App\Models\ContractDay;
use App\Models\Customer;
use App\Models\Fee;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create(['name' => 'Trasteros Norte']);
    $this->owner = User::factory()->create();
    $this->tenant->users()->attach($this->owner, ['role' => 'owner']);
    $this->url = "/{$this->tenant->slug}/business";
});

it('shows the business page to its members', function () {
    $this->actingAs($this->owner)
        ->get($this->url)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('tenant/business'));
});

it('deletes the business with all its data, leaving other businesses alone', function () {
    $contract = Contract::factory()->for($this->tenant)->create();
    Contract::factory()->for($this->tenant)->onDays(['2026-10-05', '2026-10-06'])->create();
    Fee::factory()->for($this->tenant)->create(['contract_id' => $contract->id]);
    Unit::factory()->for($this->tenant)->create();
    $other = Fee::factory()->create(); // another tenant, with its own contract, unit and customer

    $this->actingAs($this->owner)
        ->delete($this->url)
        ->assertRedirect(route('dashboard'))
        ->assertInertiaFlash('toast', ['type' => 'success', 'message' => 'Negocio eliminado.']);

    expect(Tenant::whereKey($this->tenant->id)->exists())->toBeFalse()
        ->and(Unit::withoutGlobalScopes()->count())->toBe(1)
        ->and(Customer::withoutGlobalScopes()->count())->toBe(1)
        ->and(Contract::withoutGlobalScopes()->count())->toBe(1)
        ->and(ContractDay::count())->toBe(0)
        ->and(Fee::withoutGlobalScopes()->pluck('id')->all())->toBe([$other->id])
        ->and($this->owner->fresh())->not->toBeNull();
});

it('lets only owners delete the business', function () {
    $staff = User::factory()->create();
    $this->tenant->users()->attach($staff, ['role' => 'staff']);

    $this->actingAs($staff)->delete($this->url)->assertForbidden();

    expect(Tenant::whereKey($this->tenant->id)->exists())->toBeTrue();
});

it('lets a user without a business create one', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn (Assert $page) => $page->component('dashboard'));

    $this->actingAs($user)
        ->post(route('businesses.store'), ['business_name' => 'Garajes Sur'])
        ->assertRedirect('/garajes-sur/dashboard');

    $this->actingAs($user)
        ->post(route('businesses.store'), ['business_name' => ''])
        ->assertSessionHasErrors(['business_name' => 'El nombre del negocio es obligatorio.']);
});

it('keeps owners from deleting their account until their businesses are gone', function () {
    $this->actingAs($this->owner)
        ->get(route('profile.edit'))
        ->assertInertia(fn (Assert $page) => $page
            ->where('ownedBusinesses', [['name' => 'Trasteros Norte', 'slug' => $this->tenant->slug]])
        );

    $this->actingAs($this->owner)
        ->delete(route('profile.destroy'), ['password' => 'password'])
        ->assertSessionHasErrors('password');

    expect($this->owner->fresh())->not->toBeNull();

    $this->actingAs($this->owner)->delete($this->url);
    $this->actingAs($this->owner)
        ->delete(route('profile.destroy'), ['password' => 'password'])
        ->assertRedirect('/');

    expect($this->owner->fresh())->toBeNull();
});
