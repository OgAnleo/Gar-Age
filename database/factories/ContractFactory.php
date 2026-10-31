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
     * Define the model's default state: an indefinite monthly booking of a
     * unit by a customer of the same tenant.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'unit_id' => fn (array $attributes) => Unit::factory()->create(['tenant_id' => $attributes['tenant_id']]),
            'customer_id' => fn (array $attributes) => Customer::factory()->create(['tenant_id' => $attributes['tenant_id']]),
            'mode' => 'monthly',
            'starts_on' => '2026-01-01',
            'ends_on' => null,
            'period_count' => null,
            'price_per_period_cents' => fake()->numberBetween(2500, 15000),
        ];
    }

    /**
     * A booking of specific days.
     *
     * @param  list<string>  $days  Sorted "Y-m-d" dates.
     */
    public function onDays(array $days): static
    {
        return $this
            ->state(['mode' => 'days', 'starts_on' => $days[0], 'ends_on' => $days[count($days) - 1]])
            ->afterCreating(function (Contract $contract) use ($days): void {
                $contract->days()->createMany(array_map(fn (string $day) => ['day' => $day], $days));
            });
    }
}
