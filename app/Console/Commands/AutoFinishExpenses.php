<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Expense;
use Carbon\Carbon;

class AutoFinishExpenses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'expenses:autofinish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Automatically finish expenses compliant with the 3-day rule';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cutoffDate = Carbon::now()->subDays(3);

        $expenses = Expense::where('status', 'Pending Confirmation')
                           ->where('updated_at', '<', $cutoffDate)
                           ->get();

        $count = 0;
        foreach ($expenses as $expense) {
            $expense->update(['status' => 'Paid']);
            $count++;
        }

        $this->info("Automatically finished $count expenses.");
    }
}
