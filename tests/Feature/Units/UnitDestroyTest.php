<?php

use App\Models\Tenant;
use App\Models\Unit;
use App\Models\User;

beforeEach(function () {
    $this->tenant = Tenant::factory()->create();
    $this->member = User::factory()->create();
    $this->tenant->users()->attach($this->member, ['role' => 'owner']);

    $this->unit = Unit::factory()->for($this->tenant)->create();
});

it('deletes a unit of the current tenant', function () {
    $this->actingAs($this->member)
        ->delete("/{$this->tenant->slug}/units/{$this->unit->id}")
        ->assertRedirect("/{$this->tenant->slug}/units");

    $this->assertModelMissing($this->unit);
});

it('returns 404 when deleting a unit of another tenant', function () {
    $foreign = Unit::factory()->create();

    $this->actingAs($this->member)
        ->delete("/{$this->tenant->slug}/units/{$foreign->id}")
        ->assertNotFound();

    $this->assertModelExists($foreign);
});
