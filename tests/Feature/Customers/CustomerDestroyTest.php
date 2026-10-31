<?php

use App\Models\Customer;
use App\Models\Tenant;
use App\Models\User;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->member = User::factory()->create();
    $this->tenant->users()->attach($this->member, ['role' => 'owner']);

    $this->customer = Customer::factory()->for($this->tenant)->create();
});

it('deletes a customer of the current tenant', function () {
    $this->actingAs($this->member)
        ->delete("/{$this->tenant->slug}/customers/{$this->customer->id}")
        ->assertRedirect("/{$this->tenant->slug}/customers");

    $this->assertModelMissing($this->customer);
});

it('returns 404 when deleting a customer of another tenant', function () {
    $foreign = Customer::factory()->create();

    $this->actingAs($this->member)
        ->delete("/{$this->tenant->slug}/customers/{$foreign->id}")
        ->assertNotFound();

    $this->assertModelExists($foreign);
});
