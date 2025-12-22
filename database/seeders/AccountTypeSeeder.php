<?php

namespace Database\Seeders;

use App\Models\AccountType;
use Illuminate\Database\Seeder;

class AccountTypeSeeder extends Seeder
{
    public function run()
    {
        $types = ['Admin', 'Customer', 'Owner', 'Driver'];
        
        foreach ($types as $type) {
            AccountType::firstOrCreate(['name' => $type]);
        }
    }
}