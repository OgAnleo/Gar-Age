<?php

use App\Models\Customer;
use App\Models\Tenant;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->member = User::factory()->create();
    $this->tenant->users()->attach($this->member, ['role' => 'owner']);

    $this->customer = Customer::factory()->for($this->tenant)->create([
        'name' => 'Ana López',
        'tax_id' => '12345678Z',
    ]);
    $this->url = "/{$this->tenant->slug}/customers/{$this->customer->id}";
    $this->payload = [
        'name' => 'Ana López García',
        'tax_id' => '12345678Z',
        'email' => '',
        'phone' => '+34 600 111 222',
    ];
});

it('shows the edit form with the customer data', function () {
    $this->actingAs($this->member)
        ->get("{$this->url}/edit")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('tenant/customers/edit')
            ->where('customer.name', 'Ana López')
            ->where('customer.tax_id', '12345678Z')
        );
});

it('updates the customer keeping its own tax id', function () {
    $this->actingAs($this->member)
        ->put($this->url, $this->payload)
        ->assertSessionHasNoErrors()
        ->assertRedirect("/{$this->tenant->slug}/customers");

    $this->customer->refresh();

    expect($this->customer->name)->toBe('Ana López García')
        ->and($this->customer->email)->toBeNull()
        ->and($this->customer->phone)->toBe('+34600111222');
});

it('rejects a tax id used by another customer of the same tenant', function () {
    Customer::factory()->for($this->tenant)->create(['tax_id' => 'X1234567L']);

    $this->actingAs($this->member)
        ->put($this->url, [...$this->payload, 'tax_id' => 'X1234567L'])
        ->assertSessionHasErrors('tax_id');
});

it('returns 404 for a customer of another tenant', function () {
    $foreign = Customer::factory()->create();
    $foreignUrl = "/{$this->tenant->slug}/customers/{$foreign->id}";

    $this->actingAs($this->member)->get("{$foreignUrl}/edit")->assertNotFound();
    $this->actingAs($this->member)->put($foreignUrl, $this->payload)->assertNotFound();
});
