<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Ticket>
 */
class TicketFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'project_id' => $this->faker->randomDigitNotNull(),
            'ticket_status_id' => $this->faker->randomDigitNotNull(),
            'serverity_id' => $this->faker->randomDigitNotNull(),
            'subject' => $this->faker->sentence(),
            'code' => $this->faker->uuid(),
            'type' => json_encode([$this->faker->word(), $this->faker->word()]),
            'description' => $this->faker->paragraph(),
        ];
    }
}
