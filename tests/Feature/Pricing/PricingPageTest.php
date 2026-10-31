<?php

use App\Models\Tenant;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->member = User::factory()->create();
    $this->tenant->users()->attach($this->member, ['role' => 'owner']);
    $this->url = "/{$this->tenant->slug}/pricing";
});

it('shows the current discounts, none by default', function () {
    $this->actingAs($this->member)
        ->get($this->url)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('tenant/pricing/edit')
            ->where('weekly_discount', 0)
            ->where('monthly_discount', 0)
            ->where('annual_discount', 0)
        );
});

it('saves the discounts of the current tenant', function () {
    $other = Tenant::factory()->create();

    $this->actingAs($this->member)
        ->put($this->url, ['weekly_discount' => '5', 'monthly_discount' => '10', 'annual_discount' => '25'])
        ->assertSessionHasNoErrors()
        ->assertRedirect($this->url)
        ->assertInertiaFlash('toast', ['type' => 'success', 'message' => 'Descuentos guardados. Se aplicarán a las nuevas reservas.']);

    $this->tenant->refresh();

    expect([$this->tenant->weekly_discount_percent, $this->tenant->monthly_discount_percent, $this->tenant->annual_discount_percent])
        ->toBe([5, 10, 25])
        ->and($other->fresh()->annual_discount_percent)->toBe(0);
});

it('only accepts whole percentages from 0 to 100', function () {
    $this->actingAs($this->member)
        ->put($this->url, ['weekly_discount' => '-1', 'monthly_discount' => '101', 'annual_discount' => '12.5'])
        ->assertSessionHasErrors([
            'weekly_discount' => 'El descuento semanal debe ser un número entero entre 0 y 100.',
            'monthly_discount' => 'El descuento mensual debe ser un número entero entre 0 y 100.',
            'annual_discount' => 'El descuento anual debe ser un número entero entre 0 y 100.',
        ]);
});

it('forbids users that do not belong to the tenant', function () {
    $this->actingAs(User::factory()->create())->get($this->url)->assertForbidden();
});
