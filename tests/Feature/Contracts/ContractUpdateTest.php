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
        'mode' => 'monthly',
        'starts_on' => '2026-01-01',
        'price_per_period_cents' => 7999,
    ]);
    $this->contract->unit->update(['daily_price_cents' => 100]);
    $this->url = "/{$this->tenant->slug}/contracts/{$this->contract->id}";
    $this->payload = [
        'unit_id' => $this->contract->unit_id,
        'customer_id' => $this->contract->customer_id,
        'mode' => 'monthly',
        'starts_on' => '2026-01-01',
        'periods' => '12',
    ];
});

it('shows the edit form with the contract data', function () {
    $this->actingAs($this->member)
        ->get("{$this->url}/edit")
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('tenant/contracts/edit')
            ->where('contract.unit_id', (string) $this->contract->unit_id)
            ->where('contract.mode', 'monthly')
            ->where('contract.starts_on', '2026-01-01')
            ->where('contract.periods', null)
            ->where('contract.price_cents', 7999)
            ->has('units', 1)
        );
});

it('keeps the agreed price when the mode does not change', function () {
    $this->actingAs($this->member)
        ->put($this->url, $this->payload)
        ->assertSessionHasNoErrors()
        ->assertRedirect("/{$this->tenant->slug}/contracts");

    $this->contract->refresh();

    expect($this->contract->ends_on)->toBe('2026-12-31')
        ->and($this->contract->price_per_period_cents)->toBe(7999);
});

it('reprices and stores the days when switching to specific days', function () {
    $this->actingAs($this->member)
        ->put($this->url, [...$this->payload, 'mode' => 'days', 'days' => ['2026-01-05', '2026-01-06']])
        ->assertSessionHasNoErrors();

    $this->contract->refresh();

    expect($this->contract->price_per_period_cents)->toBe(200)
        ->and($this->contract->days)->toHaveCount(2);

    $this->actingAs($this->member)->put($this->url, $this->payload)->assertSessionHasNoErrors();

    expect($this->contract->fresh()->days)->toHaveCount(0);
});

it('returns 404 for a contract of another tenant', function () {
    $foreign = Contract::factory()->create();
    $foreignUrl = "/{$this->tenant->slug}/contracts/{$foreign->id}";

    $this->actingAs($this->member)->get("{$foreignUrl}/edit")->assertNotFound();
    $this->actingAs($this->member)->put($foreignUrl, $this->payload)->assertNotFound();
    $this->actingAs($this->member)->delete($foreignUrl)->assertNotFound();
});

it('deletes a contract of the current tenant, with its days', function () {
    $days = Contract::factory()->for($this->tenant)->onDays(['2027-01-01'])->create();

    $this->actingAs($this->member)
        ->delete("/{$this->tenant->slug}/contracts/{$days->id}")
        ->assertRedirect("/{$this->tenant->slug}/contracts");

    $this->assertModelMissing($days);
    $this->assertDatabaseMissing('contract_days', ['contract_id' => $days->id]);
});
