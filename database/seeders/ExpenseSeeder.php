<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Expense;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ExpenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing expenses to avoid duplicates/messy data
        DB::table('expenses')->truncate();

        $categories = ['reimbursement', 'operational', 'maintenance', 'salary', 'other'];
        $descriptions = [
            'reimbursement' => ['Travel Expenses - Jakarta', 'Client Lunch', 'Office Supplies', 'Taxi Fare'],
            'operational' => ['Internet Bill', 'Electricity Token', 'Water Refill', 'Server Monthly'],
            'maintenance' => ['Quick Bus Wash', 'Oil Top-up', 'Tire Patch', 'Light Bulb Replacement'],
            'salary' => ['Daily Helper Wage', 'Driver Meal Allowance', 'Bonus'],
            'other' => ['Community Donation', 'Parking Fees', 'Snacks for Office'],
        ];

        // Target Date Range: Current Month (Jan 2026 based on context)
        // Revenue is ~5M. Expenses should be ~2-3M to show profit.
        $targetTotal = 0;
        $maxTotal = 3000000; 

        for ($i = 0; $i < 15; $i++) {
            $type = $categories[array_rand($categories)];
            $descList = $descriptions[$type];
            $description = $descList[array_rand($descList)];
            
            // Random amount between 20k and 500k
            $amount = rand(20, 500) * 1000; 

            if ($targetTotal + $amount > $maxTotal) {
                break;
            }

            // Distribute over the last 15 days
            $date = Carbon::create(2026, 1, 14)->subDays(rand(0, 14));

            Expense::create([
                'description' => $description,
                'amount' => $amount,
                'type' => $type,
                'date' => $date,
                'account_id' => null, 
            ]);

            $targetTotal += $amount;
        }
    }
}
