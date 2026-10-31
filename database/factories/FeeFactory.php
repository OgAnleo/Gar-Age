<?php

namespace Database\Factories;

use App\Models\Contract;
use App\Models\Fee;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Fee>
 */
class FeeFactory extends Factory
{
    /**
     * Define the model's default state: an unpaid fee of a contract of the same tenant.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'contract_id' => fn (array $attributes) => Contract::factory()->create(['tenant_id' => $attributes['tenant_id']]),
            'month' => '2026-10',
            'amount_cents' => fake()->numberBetween(2500, 15000),
            'paid_on' => null,
        ];
    }
}
