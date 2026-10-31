<?php

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Inertia\Support\SessionKey;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->owner = User::factory()->create(['name' => 'Olga Dueña']);
    $this->tenant->users()->attach($this->owner, ['role' => 'owner']);
    $this->url = "/{$this->tenant->slug}/team";
});

it('lists the members, owners first', function () {
    $staff = User::factory()->create(['name' => 'Aitor Empleado']);
    $this->tenant->users()->attach($staff, ['role' => 'staff']);

    $this->actingAs($this->owner)
        ->get($this->url)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('tenant/team/index')
            ->has('members', 2)
            ->where('members.0.name', 'Olga Dueña')
            ->where('members.0.role_label', 'Propietario')
            ->where('members.1.name', 'Aitor Empleado')
            ->where('members.1.role', 'staff')
        );
});

it('adds someone without an account, showing their temporary password once', function () {
    $this->actingAs($this->owner)
        ->post($this->url, ['name' => 'Luis', 'email' => 'Luis@Example.com'])
        ->assertRedirect($this->url)
        ->assertInertiaFlash('toast', ['type' => 'success', 'message' => 'Empleado añadido.']);

    $flash = session(SessionKey::FLASH_DATA)['temporaryPassword'];
    $luis = User::where('email', 'luis@example.com')->sole();

    expect($flash['email'])->toBe('luis@example.com')
        ->and(Hash::check($flash['password'], $luis->password))->toBeTrue()
        ->and($luis->hasVerifiedEmail())->toBeTrue()
        ->and($this->tenant->users()->whereKey($luis->id)->first()?->pivot?->getAttribute('role'))->toBe('staff');
});

it('adds an existing user without a new password, but only once', function () {
    $ana = User::factory()->create(['email' => 'ana@example.com']);

    $this->actingAs($this->owner)
        ->post($this->url, ['name' => 'Ana', 'email' => 'ana@example.com'])
        ->assertInertiaFlashMissing('temporaryPassword');

    $this->actingAs($this->owner)
        ->post($this->url, ['name' => 'Ana', 'email' => 'ana@example.com'])
        ->assertSessionHasErrors(['email' => 'Esa persona ya pertenece al negocio.']);

    expect($this->tenant->users()->whereKey($ana->id)->exists())->toBeTrue();
});

it('removes staff but never an owner', function () {
    $staff = User::factory()->create();
    $this->tenant->users()->attach($staff, ['role' => 'staff']);

    $this->actingAs($this->owner)->delete("{$this->url}/{$this->owner->id}")->assertNotFound();
    $this->actingAs($this->owner)->delete("{$this->url}/{$staff->id}")->assertRedirect($this->url);

    expect($this->tenant->users()->whereKey($staff->id)->exists())->toBeFalse()
        ->and($staff->fresh())->not->toBeNull();
});

it('lets staff run the business but not manage it', function () {
    $staff = User::factory()->create();
    $this->tenant->users()->attach($staff, ['role' => 'staff']);
    $slug = $this->tenant->slug;

    $this->actingAs($staff)->get("/{$slug}/units")->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('tenant.canManage', false));

    foreach (["/{$slug}/pricing", "/{$slug}/business", "/{$slug}/team"] as $url) {
        $this->actingAs($staff)->get($url)->assertForbidden();
    }
    $this->actingAs($staff)->post($this->url, ['name' => 'X', 'email' => 'x@example.com'])->assertForbidden();
    $this->actingAs($staff)->delete("/{$slug}/business")->assertForbidden();

    $this->actingAs($this->owner)->get("/{$slug}/units")
        ->assertInertia(fn (Assert $page) => $page->where('tenant.canManage', true));
});

it('lands staff on the business they work for', function () {
    $staff = User::factory()->create();
    $this->tenant->users()->attach($staff, ['role' => 'staff']);

    $this->actingAs($staff)->get(route('dashboard'))->assertRedirect("/{$this->tenant->slug}/dashboard");
});
