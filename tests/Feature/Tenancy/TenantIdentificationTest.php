<?php

use App\Models\Tenant;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->member = User::factory()->create();
    $this->tenant->users()->attach($this->member, ['role' => 'owner']);
});

it('redirects guests to the login page', function () {
    $this->get("/{$this->tenant->slug}/dashboard")
        ->assertRedirect(route('login'));
});

it('returns 404 for an unknown tenant slug', function () {
    $this->actingAs($this->member)
        ->get('/does-not-exist/dashboard')
        ->assertNotFound();
});

it('forbids users that do not belong to the tenant', function () {
    $outsider = User::factory()->create();

    $this->actingAs($outsider)
        ->get("/{$this->tenant->slug}/dashboard")
        ->assertForbidden();
});

it('shows the tenant dashboard to its members with the tenant prop', function () {
    $this->actingAs($this->member)
        ->get("/{$this->tenant->slug}/dashboard")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('tenant/dashboard')
            ->where('tenant.id', $this->tenant->id)
            ->where('tenant.slug', $this->tenant->slug)
        );
});

it('generates tenant urls without passing the slug explicitly', function () {
    $this->actingAs($this->member)
        ->get("/{$this->tenant->slug}/dashboard")
        ->assertOk();

    expect(route('tenant.dashboard'))->toEndWith("/{$this->tenant->slug}/dashboard");
});

it('shares a null tenant outside tenant routes', function () {
    $this->actingAs($this->member)
        ->get(route('profile.edit'))
        ->assertInertia(fn (Assert $page) => $page->where('tenant', null));
});
