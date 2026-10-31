<?php

namespace Database\Factories;

use App\Models\Contract;
use App\Models\Customer;
use App\Models\Tenant;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contract>
 */
class ContractFactory extends Factory
{
    /**
     * Define the model's default state: unit and customer of the same tenant.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'unit_id' => fn (array $attributes) => Unit::factory()->create(['tenant_id' => $attributes['tenant_id']]),
            'customer_id' => fn (array $attributes) => Customer::factory()->create(['tenant_id' => $attributes['tenant_id']]),
            'starts_on' => '2026-01-01',
            'ends_on' => null,
            'monthly_price_cents' => fake()->numberBetween(2500, 15000),
        ];
    }
}
