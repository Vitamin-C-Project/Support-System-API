<?php

namespace Database\Seeders;

use App\Models\TicketStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TicketStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $name = ['Open', 'On Hold', 'Cancel', 'On Progress', 'Review', 'Merged', 'Deploy', 'Done'];

        foreach ($name as $key) {
            TicketStatus::create([
                'name' => $key
            ]);
        }
    }
}
