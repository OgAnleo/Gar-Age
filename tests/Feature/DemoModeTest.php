<?php

use App\Http\Middleware\ProtectDemoAccount;
use App\Models\Tenant;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    config(['demo.enabled' => true, 'services.cron.token' => 'secret-token']);
    $this->seed(DemoSeeder::class);
    $this->demo = User::where('email', config('demo.email'))->sole();
    $this->tenant = Tenant::sole();
});

it('keeps the demo account from changing its credentials or deleting its business', function (string $method, string $uri, array $data) {
    $this->actingAs($this->demo)
        ->from('/settings/profile')
        ->call($method, str_replace('{tenant}', $this->tenant->slug, $uri), $data)
        ->assertRedirect('/settings/profile')
        ->assertInertiaFlash('toast', ['type' => 'error', 'message' => ProtectDemoAccount::MESSAGE]);

    $demo = $this->demo->fresh();
    expect($demo->email)->toBe(config('demo.email'))
        ->and(Hash::check('password', $demo->password))->toBeTrue()
        ->and($demo->two_factor_secret)->toBeNull()
        ->and(Tenant::whereKey($this->tenant->id)->exists())->toBeTrue();
})->with([
    'profile' => ['PATCH', '/settings/profile', ['name' => 'Hacker', 'email' => 'hacker@example.com']],
    'password' => ['PUT', '/settings/password', ['current_password' => 'password', 'password' => 'n3w-Passw0rd!', 'password_confirmation' => 'n3w-Passw0rd!']],
    'two factor' => ['POST', '/user/two-factor-authentication', []],
    'business' => ['DELETE', '/{tenant}/business', []],
    'team' => ['POST', '/{tenant}/team', ['name' => 'Pepa', 'email' => 'pepa@example.com']],
    'new business' => ['POST', '/businesses', ['business_name' => 'Otro']],
]);

it('answers 403 to JSON requests such as passkey registration', function () {
    $this->actingAs($this->demo)->postJson('/user/passkeys')->assertForbidden();
});

it('lets the demo account use the rest of the app', function () {
    $this->actingAs($this->demo)
        ->post("/{$this->tenant->slug}/units", ['code' => 'Z-99', 'type' => 'garage', 'size_m2' => '10', 'daily_price' => '3'])
        ->assertRedirect();

    expect($this->tenant->units()->where('code', 'Z-99')->exists())->toBeTrue();
});

it('leaves other accounts alone, and the demo account when demo mode is off', function () {
    $other = User::factory()->create();
    $this->actingAs($other)->patch('/settings/profile', ['name' => 'Otro', 'email' => $other->email]);
    expect($other->fresh()->name)->toBe('Otro');

    config(['demo.enabled' => false]);
    $this->actingAs($this->demo)->patch('/settings/profile', ['name' => 'Cambiado', 'email' => config('demo.email')]);
    expect($this->demo->fresh()->name)->toBe('Cambiado');
});

it('shares the demo credentials, and whether the visitor is the demo account', function () {
    $this->get('/login')->assertInertia(fn (Assert $page) => $page
        ->where('demo', ['email' => config('demo.email'), 'staffEmail' => config('demo.staff_email'), 'password' => 'password', 'isDemoUser' => false])
    );

    config(['demo.enabled' => false]);
    $this->get('/login')->assertInertia(fn (Assert $page) => $page->where('demo', null));

    config(['demo.enabled' => true]);
    $this->actingAs($this->demo)->get("/{$this->tenant->slug}/dashboard")
        ->assertInertia(fn (Assert $page) => $page->where('demo.isDemoUser', true));
});

it('rebuilds the demo every night, whatever visitors did to it', function () {
    $this->demo->forceFill(['password' => 'changed', 'two_factor_secret' => 'x', 'name' => 'Hacker'])->save();
    $this->tenant->units()->create(['code' => 'Z-99', 'type' => 'garage', 'size_m2' => 10, 'daily_price_cents' => 300]);

    $this->withToken('secret-token')->postJson('/cron/demo')->assertOk()->assertExactJson(['reset' => true]);

    $demo = $this->demo->fresh();
    expect(Hash::check('password', $demo->password))->toBeTrue()
        ->and($demo->two_factor_secret)->toBeNull()
        ->and($demo->name)->toBe('Demo')
        ->and(Tenant::sole()->units()->count())->toBe(12);
});

it('has no reset outside the demo', function () {
    config(['demo.enabled' => false]);

    $this->withToken('secret-token')->postJson('/cron/demo')->assertNotFound();
});

it('protects the demo employee too, who sees the app as staff', function () {
    $staff = User::where('email', config('demo.staff_email'))->sole();

    $this->actingAs($staff)
        ->from('/settings/profile')
        ->patch('/settings/profile', ['name' => 'Hacker', 'email' => 'hacker@example.com'])
        ->assertInertiaFlash('toast', ['type' => 'error', 'message' => ProtectDemoAccount::MESSAGE]);

    $this->actingAs($staff)->get("/{$this->tenant->slug}/dashboard")
        ->assertInertia(fn (Assert $page) => $page
            ->where('tenant.canManage', false)
            ->where('demo.isDemoUser', true)
        );

    expect($staff->fresh()->email)->toBe(config('demo.staff_email'));
});
