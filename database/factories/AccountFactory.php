<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\AccountType;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Account>
 */
class AccountFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            // ID is auto-generated (C-2025...)
            // Type ID will be assigned in Seeder
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'birthdate' => $this->faker->date(),
            'password_hash' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // "password",
            'account_type_id' => AccountType::firstOrCreate(['name' => 'Customer'])->id
        ];
    }

    /**
     * Template for creating a specific manual account.
     * Edit these values as needed.
     */
    public function rico(): static
    {
        return $this->state(fn (array $attributes) => [
            'first_name' => 'Rico',
            'last_name' => 'Dharmawan',
            'email' => 'rico.dharmawan@example.com',
            'phone' => '081234567890',
            'birthdate' => '2006-08-19',
            'password_hash' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'account_type_id' => '3'
        ]);
    }

    public function jojo(): static
    {
        return $this->state(fn (array $attributes) => [
            'first_name' => 'Jonathan',
            'last_name' => 'Waluya',
            'email' => 'jonathan.vw@example.com',
            'phone' => '081234567890',
            'birthdate' => '2006-01-01',
            'password_hash' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'account_type_id' => '1'
        ]);
    }

    public function jason(): static
    {
        return $this->state(fn (array $attributes) => [
            'first_name' => 'Jason',
            'last_name' => '',
            'email' => 'jason@example.com',
            'phone' => '081234567890',
            'birthdate' => '2006-01-01',
            'password_hash' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'account_type_id' => '1'
        ]);
    }
}
