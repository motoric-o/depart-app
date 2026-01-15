<?php

namespace Database\Seeders;

use App\Models\AccountType;
use Illuminate\Database\Seeder;

class AccountTypeSeeder extends Seeder
{
    public function run()
    {
        $types = [
            'Owner', 
            'Super Admin', 
            'Financial Admin',
            'Scheduling Admin',
            'Operations Admin',
            'Driver',
            'Customer'
        ];
        
        foreach ($types as $type) {
            AccountType::firstOrCreate(['name' => $type]);
        }
    }
}