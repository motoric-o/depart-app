<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Route>
 */
class RouteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        // Note: We need existing destinations for this to work
        return [
            'source' => $this->faker->city(),
            // destination_code will be assigned in the Seeder
            'distance' => $this->faker->numberBetween(50, 500),
            'estimated_duration' => $this->faker->numberBetween(60, 480), // Minutes
        ];
    }
}
