<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Schedule>
 */
class ScheduleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            // route_id and bus_id assigned in Seeder
            'departure_time' => $this->faker->dateTimeBetween('now', '+1 month'),
            'arrival_time' => $this->faker->dateTimeBetween('+1 month', '+1 month 5 hours'),
            'price_per_seat' => $this->faker->randomElement([100000, 150000, 250000, 300000]),
            'status' => 'Scheduled',
        ];
    }
}
