<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\models>
 */
class SeverityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => $this->faker->randomElement(['Minor', 'Major', 'Urgent', 'Emergency']),
            'description' => $this->faker->paragraph(),
            'estimated_day' => $this->faker->date(),
            'status' => $this->faker->randomElement(['1', '0']),
        ];
    }
}
