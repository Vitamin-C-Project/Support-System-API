<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => $this->faker->numberBetween(1, 4),
            'name' => $this->faker->company,
            'type' => json_encode($this->faker->randomElements(['Tech', 'Finance', 'Health', 'Education'], rand(1, 2))),
            'city' => $this->faker->city,
            'zip_code' => $this->faker->randomNumber(5, true),
            'address' => $this->faker->address,
            'status' => $this->faker->randomElement(['1', '0']),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
