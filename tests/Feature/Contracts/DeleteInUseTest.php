<?php

use App\Models\Contract;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\QueryException;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->member = User::factory()->create();
    $this->tenant->users()->attach($this->member, ['role' => 'owner']);

    $this->contract = Contract::factory()->for($this->tenant)->create();
});

it('keeps a unit or customer with contracts and explains why', function (string $resource, string $key) {
    $url = "/{$this->tenant->slug}/{$resource}";

    $this->actingAs($this->member)
        ->from($url)
        ->delete("{$url}/{$this->contract->{$key}}")
        ->assertRedirect($url)
        ->assertInertiaFlash('toast', ['type' => 'error', 'message' => 'No se puede eliminar porque tiene contratos.']);

    expect($this->contract->{str_replace('_id', '', $key)}()->exists())->toBeTrue();
})->with([
    'unit' => ['units', 'unit_id'],
    'customer' => ['customers', 'customer_id'],
]);

it('lets the database refuse deleting a unit with contracts too', function () {
    expect(fn () => $this->contract->unit->delete())->toThrow(QueryException::class);
});
