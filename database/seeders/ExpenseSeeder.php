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
        // Clear existing expenses
        DB::table('expenses')->truncate();

        // Fetch potential requestors: Drivers, Ops Admins, Super Admins
        // We exclude 'Customer' type generally.
        $requestors = \App\Models\Account::whereHas('accountType', function($q) {
            $q->whereIn('name', ['Driver', 'Operations Admin', 'Sensors Admin', 'Super Admin']); 
        })->get();

        if ($requestors->isEmpty()) {
            // Fallback if no specific users found (e.g. running seed independently)
            $requestors = \App\Models\Account::factory(3)->state(['account_type_id' => 1])->create(); // create generic
        }

        // Generate Expenses
        foreach ($requestors as $user) {
            // Each user submits 3-5 expenses
            Expense::factory(rand(3, 5))->create([
                'account_id' => $user->id,
            ]);
        }
    }
}
