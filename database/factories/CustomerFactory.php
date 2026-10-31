<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    private const string CHECK_LETTERS = 'TRWAGMYFPDXBNJZSQVHLCKE';

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $number = fake()->unique()->numberBetween(10_000_000, 99_999_999);

        return [
            'tenant_id' => Tenant::factory(),
            'name' => fake()->name(),
            'tax_id' => $number.self::CHECK_LETTERS[$number % 23],
            'email' => fake()->unique()->safeEmail(),
            'phone' => '6'.fake()->numerify('########'),
        ];
    }
}
