<?php

use App\Models\Customer;
use App\Models\Tenant;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->member = User::factory()->create();
    $this->tenant->users()->attach($this->member, ['role' => 'owner']);

    $this->url = "/{$this->tenant->slug}/customers";
    $this->payload = [
        'name' => 'Ana López',
        'tax_id' => '12345678-z',
        'email' => 'Ana@Example.com',
        'phone' => '600 111 222',
    ];
});

it('shows the create form', function () {
    $this->actingAs($this->member)
        ->get("{$this->url}/create")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('tenant/customers/create'));
});

it('creates a customer for the current tenant with normalized data', function () {
    $this->actingAs($this->member)
        ->post($this->url, $this->payload)
        ->assertSessionHasNoErrors()
        ->assertRedirect($this->url);

    $customer = Customer::firstWhere('tax_id', '12345678Z');

    expect($customer->tenant_id)->toBe($this->tenant->id)
        ->and($customer->email)->toBe('ana@example.com')
        ->and($customer->phone)->toBe('600111222');
});

it('stores empty contact fields as null', function () {
    $this->actingAs($this->member)
        ->post($this->url, [...$this->payload, 'email' => '', 'phone' => ''])
        ->assertSessionHasNoErrors();

    $customer = Customer::firstWhere('tax_id', '12345678Z');

    expect($customer->email)->toBeNull()
        ->and($customer->phone)->toBeNull();
});

it('rejects a tax id already used in the same tenant', function () {
    Customer::factory()->for($this->tenant)->create(['tax_id' => '12345678Z']);

    $this->actingAs($this->member)
        ->post($this->url, $this->payload)
        ->assertSessionHasErrors(['tax_id' => 'Ya existe un cliente con ese DNI, NIE o CIF.']);
});

it('allows a tax id already used by another tenant', function () {
    Customer::factory()->create(['tax_id' => '12345678Z']);

    $this->actingAs($this->member)
        ->post($this->url, $this->payload)
        ->assertSessionHasNoErrors();

    expect($this->tenant->customers()->where('tax_id', '12345678Z')->exists())->toBeTrue();
});

it('explains validation errors in Spanish', function () {
    $this->actingAs($this->member)
        ->post($this->url, ['name' => '', 'tax_id' => '12345678A', 'email' => 'ana@', 'phone' => '123'])
        ->assertSessionHasErrors([
            'name' => 'El nombre es obligatorio.',
            'tax_id' => 'El DNI, NIE o CIF no es válido.',
            'email' => 'El email no es válido.',
            'phone' => 'El teléfono debe tener entre 9 y 15 dígitos.',
        ]);
});

it('requires the tax id', function () {
    $this->actingAs($this->member)
        ->post($this->url, [...$this->payload, 'tax_id' => ''])
        ->assertSessionHasErrors(['tax_id' => 'El DNI, NIE o CIF es obligatorio.']);
});
