<?php

namespace Database\Factories;

use App\Models\Tenant;
use App\Models\Unit;
use GarAge\Units\Domain\UnitType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Unit>
 */
class UnitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'code' => fake()->unique()->bothify('?-##'),
            'type' => fake()->randomElement(UnitType::cases()),
            'size_m2' => fake()->randomFloat(2, 2, 30),
            'daily_price_cents' => fake()->numberBetween(100, 600),
        ];
    }
}
