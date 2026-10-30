<?php

use App\Models\Unit;
use GarAge\Units\Domain\UnitType;

it('casts type to the UnitType enum', function () {
    $unit = Unit::factory()->create(['type' => 'storage']);

    expect($unit->fresh()->type)->toBe(UnitType::Storage);
});

it('gives each type a human label', function () {
    expect(UnitType::Garage->label())->toBe('Garaje')
        ->and(UnitType::Storage->label())->toBe('Trastero');
});
