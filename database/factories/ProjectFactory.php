<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => $this->faker->company(),
            'type' => json_encode([$this->faker->word(), $this->faker->word()]),
            'server' => $this->faker->domainName(),
            'domain' => $this->faker->domainName(),
            'status' => $this->faker->randomElement(['1', '0']),
            'expired_at' => $this->faker->date(),
        ];
    }
}
