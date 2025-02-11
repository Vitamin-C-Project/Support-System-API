<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\Ticket;
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
        $project = Project::inRandomOrder()->first();
        $initial = 'PCS'; // Default jika tidak ada project

        if ($project) {
            $words = explode(' ', $project->name);
            $initials = array_map(fn($word) => strtoupper(substr($word, 0, 1)), $words);
            $initial = implode('', array_slice($initials, 0, 2));
        }

        $lastTicket = Ticket::where('project_id', $project ? $project->id : null)
            ->whereNotNull('code')
            ->latest('id')
            ->value('code');

        if ($lastTicket) {
            $lastNumber = (int) substr($lastTicket, -3);
        } else {
            $lastNumber = 0;
        }

        $newNumber = str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        $code = "{$initial}-{$newNumber}";

        return [
            'user_id' => $this->faker->numberBetween(1, 4),
            'project_id' => $project ? $project->id : null, // Gunakan project_id yang dipilih
            'ticket_status_id' => $this->faker->randomDigitNotNull(),
            'severity_id' => $this->faker->randomDigitNotNull(),
            'subject' => $this->faker->sentence(),
            'code' => $code, // Gunakan kode yang sudah dibuat
            'type' => json_encode([$this->faker->word(), $this->faker->word()]),
            'description' => $this->faker->paragraph(),
        ];
    }
}
