<?php

use App\Models\Contract;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-11-10'));

    $this->tenant = Tenant::factory()->create(['name' => 'Trasteros Norte']);
    $this->owner = User::factory()->create();
    $this->tenant->users()->attach($this->owner, ['role' => 'owner']);
    $this->url = "/{$this->tenant->slug}/disponibles";

    Contract::factory()->for($this->tenant)->create([
        'unit_id' => Unit::factory()->for($this->tenant)->create(['code' => 'G-01']),
        'starts_on' => '2026-10-01',
    ]);
    Unit::factory()->for($this->tenant)->create(['code' => 'G-02', 'type' => 'garage', 'size_m2' => 12.5, 'daily_price_cents' => 500]);
});

it('does not exist until the owner publishes it', function () {
    $this->get($this->url)->assertNotFound();
});

it('shows anyone the free units with their prices and the contact details', function () {
    $this->tenant->forceFill(['public_page_enabled' => true, 'monthly_discount_percent' => 20, 'contact_phone' => '612345678'])->save();

    $this->get($this->url)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('public/catalog')
            ->where('business', ['name' => 'Trasteros Norte', 'phone' => '612345678', 'email' => null])
            ->has('units', 1)
            ->where('units.0.code', 'G-02')
            ->where('units.0.type_label', 'Garaje')
            ->where('units.0.prices_cents', ['annual' => 182500, 'monthly' => 12000, 'weekly' => 3500, 'days' => 500])
        );
});

it('lets the owner publish the page and set the contact details', function () {
    $business = "/{$this->tenant->slug}/business";

    $this->actingAs($this->owner)
        ->get($business)
        ->assertInertia(fn (Assert $page) => $page
            ->where('public_page.enabled', false)
            ->where('public_page.url', url($this->url))
        );

    $this->actingAs($this->owner)
        ->put("{$business}/public-page", ['public_page_enabled' => '1', 'contact_phone' => '612 34 56 78', 'contact_email' => ''])
        ->assertRedirect($business)
        ->assertInertiaFlash('toast', ['type' => 'success', 'message' => 'Página pública guardada y visible.']);

    expect($this->tenant->fresh())
        ->public_page_enabled->toBeTrue()
        ->contact_phone->toBe('612345678')
        ->contact_email->toBeNull();

    $this->actingAs($this->owner)
        ->put("{$business}/public-page", ['public_page_enabled' => '0', 'contact_phone' => '12', 'contact_email' => 'nope'])
        ->assertSessionHasErrors(['contact_phone', 'contact_email']);
});

it('keeps the settings away from staff', function () {
    $staff = User::factory()->create();
    $this->tenant->users()->attach($staff, ['role' => 'staff']);

    $this->actingAs($staff)
        ->put("/{$this->tenant->slug}/business/public-page", ['public_page_enabled' => '1'])
        ->assertForbidden();
});
