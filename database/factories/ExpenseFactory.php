<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Expense;
use App\Models\Account;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Expense>
 */
class ExpenseFactory extends Factory
{
    protected $model = Expense::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $types = ['reimbursement', 'operational', 'maintenance', 'salary', 'other'];

        $descriptions = [
            'reimbursement' => ['Travel Expenses', 'Client Meeting Lunch', 'Office Supplies', 'Taxi Fare', 'Hotel Stay'],
            'operational' => ['Internet Bill', 'Electricity Token', 'Water Bill', 'Server Hosting Fee', 'Office Rent'],
            'maintenance' => ['Bus Wash', 'Oil Change', 'Tire Replacement', 'Engine Tune-up', 'AC Repair'],
            'salary' => ['Daily Driver Wage', 'Helper Salary', 'Overtime Bonus', 'Staff Allowance'],
            'other' => ['Donation', 'Parking Fees', 'Pantry Snacks', 'Unexpected Repairs'],
        ];

        $type = $this->faker->randomElement($types);
        $descList = $descriptions[$type];

        return [
            'id' => $this->faker->uuid(),
            'description' => $this->faker->randomElement($descList) . ' - ' . $this->faker->word(),
            'amount' => $this->faker->numberBetween(50000, 2000000), // 50k to 2M
            'type' => $type,
            'status' => $this->faker->randomElement(['Pending', 'In Process', 'Pending Confirmation', 'Paid', 'Payment Issue', 'Rejected', 'Canceled', 'Failed']),
            'date' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'account_id' => Account::factory(), // Default create new, but usually overridden
        ];
    }

    /**
     * Indicate that the expense is pending.
     */
    public function pending(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'Pending',
        ]);
    }

    /**
     * Indicate that the expense is approved (In Process).
     */
    public function approved(): static
    {
        return $this->state(fn(array $attributes) => [
            'status' => 'In Process',
        ]);
    }
}
