<?php

use App\Models\Contract;
use App\Models\Customer;
use App\Models\Fee;
use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function () {
    $this->travelTo(Carbon::parse('2026-11-10'));

    $this->tenant = Tenant::factory()->create();
    $this->member = User::factory()->create();
    $this->tenant->users()->attach($this->member, ['role' => 'owner']);
    $this->url = "/{$this->tenant->slug}/fees";

    $this->contract = Contract::factory()->for($this->tenant)->create([
        'unit_id' => Unit::factory()->for($this->tenant)->create(['code' => 'G-07']),
        'customer_id' => Customer::factory()->for($this->tenant)->create(['name' => 'Ana López']),
        'starts_on' => '2026-10-01',
        'monthly_price_cents' => 7999,
    ]);
});

it('lists the fees of the current tenant with labels and status', function () {
    Fee::factory()->for($this->tenant)->create(['contract_id' => $this->contract->id, 'month' => '2026-10']);
    Fee::factory()->for($this->tenant)->create(['contract_id' => $this->contract->id, 'month' => '2026-11', 'paid_on' => '2026-11-02']);
    Fee::factory()->create(); // another tenant

    $this->actingAs($this->member)
        ->get($this->url)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('tenant/fees/index')
            ->where('current_month', '2026-11')
            ->has('fees', 2)
            ->where('fees.0.month', '2026-11')
            ->where('fees.0.status', 'paid')
            ->where('fees.1.unit_code', 'G-07')
            ->where('fees.1.customer_name', 'Ana López')
            ->where('fees.1.status_label', 'Vencida')
        );
});

it('generates this month fees for the current tenant only', function () {
    Contract::factory()->create(['starts_on' => '2026-01-01']); // another tenant

    $this->actingAs($this->member)
        ->post("{$this->url}/generate")
        ->assertRedirect($this->url)
        ->assertInertiaFlash('toast', ['type' => 'success', 'message' => 'Cuotas generadas: 1.']);

    $this->actingAs($this->member)
        ->post("{$this->url}/generate")
        ->assertInertiaFlash('toast', ['type' => 'success', 'message' => 'Las cuotas de este mes ya estaban generadas.']);

    expect(Fee::count())->toBe(1)
        ->and($this->tenant->fees()->sole()->month)->toBe('2026-11');
});

it('marks a fee paid today and undoes it', function () {
    $fee = Fee::factory()->for($this->tenant)->create(['contract_id' => $this->contract->id]);

    $this->actingAs($this->member)->post("{$this->url}/{$fee->id}/payment")->assertRedirect($this->url);
    expect($fee->fresh()->paid_on)->toBe('2026-11-10');

    $this->actingAs($this->member)
        ->from($this->url)
        ->post("{$this->url}/{$fee->id}/payment")
        ->assertInertiaFlash('toast', ['type' => 'error', 'message' => 'La cuota ya estaba pagada.']);

    $this->actingAs($this->member)->delete("{$this->url}/{$fee->id}/payment")->assertRedirect($this->url);
    expect($fee->fresh()->paid_on)->toBeNull();
});

it('returns 404 for a fee of another tenant', function () {
    $foreign = Fee::factory()->create();

    $this->actingAs($this->member)->post("{$this->url}/{$foreign->id}/payment")->assertNotFound();
    $this->actingAs($this->member)->delete("{$this->url}/{$foreign->id}/payment")->assertNotFound();

    expect($foreign->fresh()->paid_on)->toBeNull();
});

it('keeps a contract with fees and explains why', function () {
    Fee::factory()->for($this->tenant)->create(['contract_id' => $this->contract->id]);
    $contractsUrl = "/{$this->tenant->slug}/contracts";

    $this->actingAs($this->member)
        ->from($contractsUrl)
        ->delete("{$contractsUrl}/{$this->contract->id}")
        ->assertRedirect($contractsUrl)
        ->assertInertiaFlash('toast', ['type' => 'error', 'message' => 'No se puede eliminar porque tiene cuotas.']);

    $this->assertModelExists($this->contract);
});
