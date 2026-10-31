<?php

use App\Models\Contract;
use App\Models\Tenant;
use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->member = User::factory()->create();
    $this->tenant->users()->attach($this->member, ['role' => 'owner']);

    $this->contract = Contract::factory()->for($this->tenant)->create([
        'starts_on' => '2026-01-01',
        'ends_on' => null,
        'monthly_price_cents' => 7999,
    ]);
    $this->url = "/{$this->tenant->slug}/contracts/{$this->contract->id}";
    $this->payload = [
        'unit_id' => $this->contract->unit_id,
        'customer_id' => $this->contract->customer_id,
        'starts_on' => '2026-01-01',
        'ends_on' => '2026-12-31',
        'monthly_price' => '85',
    ];
});

it('shows the edit form with the contract data', function () {
    $this->actingAs($this->member)
        ->get("{$this->url}/edit")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('tenant/contracts/edit')
            ->where('contract.unit_id', (string) $this->contract->unit_id)
            ->where('contract.monthly_price', '79.99')
            ->where('contract.ends_on', null)
            ->has('units', 1)
        );
});

it('updates the contract without clashing with itself', function () {
    $this->actingAs($this->member)
        ->put($this->url, $this->payload)
        ->assertSessionHasNoErrors()
        ->assertRedirect("/{$this->tenant->slug}/contracts");

    $this->contract->refresh();

    expect($this->contract->ends_on)->toBe('2026-12-31')
        ->and($this->contract->monthly_price_cents)->toBe(8500);
});

it('returns 404 for a contract of another tenant', function () {
    $foreign = Contract::factory()->create();
    $foreignUrl = "/{$this->tenant->slug}/contracts/{$foreign->id}";

    $this->actingAs($this->member)->get("{$foreignUrl}/edit")->assertNotFound();
    $this->actingAs($this->member)->put($foreignUrl, $this->payload)->assertNotFound();
    $this->actingAs($this->member)->delete($foreignUrl)->assertNotFound();
});

it('deletes a contract of the current tenant', function () {
    $this->actingAs($this->member)
        ->delete($this->url)
        ->assertRedirect("/{$this->tenant->slug}/contracts");

    $this->assertModelMissing($this->contract);
});
