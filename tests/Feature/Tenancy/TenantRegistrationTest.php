<?php

use App\Models\Tenant;
use App\Models\User;

function registerPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Ana',
        'business_name' => 'Trasteros Norte',
        'email' => 'ana@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ], $overrides);
}

it('creates a tenant owned by the new user on registration', function () {
    $this->post(route('register.store'), registerPayload());

    $user = User::where('email', 'ana@example.com')->firstOrFail();
    $tenant = $user->tenants()->firstOrFail();

    expect($tenant->name)->toBe('Trasteros Norte')
        ->and($tenant->slug)->toBe('trasteros-norte')
        ->and($tenant->pivot->role)->toBe('owner');
});

it('requires a business name', function () {
    $this->post(route('register.store'), registerPayload(['business_name' => '']))
        ->assertSessionHasErrors('business_name');

    expect(User::count())->toBe(0)
        ->and(Tenant::count())->toBe(0);
});

it('generates a unique slug when the name is taken', function () {
    Tenant::factory()->create(['slug' => 'trasteros-norte']);

    $this->post(route('register.store'), registerPayload());

    expect(Tenant::where('name', 'Trasteros Norte')->value('slug'))->toBe('trasteros-norte-2');
});

it('never generates a reserved slug', function () {
    $this->post(route('register.store'), registerPayload(['business_name' => 'Settings']));

    expect(Tenant::where('name', 'Settings')->value('slug'))->toBe('settings-2');
});

it('redirects /dashboard to the tenant dashboard of the user', function () {
    $user = User::factory()->create();
    $tenant = Tenant::factory()->create();
    $tenant->users()->attach($user, ['role' => 'owner']);

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertRedirect("/{$tenant->slug}/dashboard");
});

it('shows the generic dashboard to users without a tenant', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertOk();
});
