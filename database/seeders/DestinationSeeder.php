<?php

namespace Database\Seeders;

use App\Models\Destination;
use Illuminate\Database\Seeder;

class DestinationSeeder extends Seeder
{
    public function run()
    {
        $destinations = [
            ['code' => 'JKT', 'city_name' => 'Jakarta'],
            ['code' => 'BDG', 'city_name' => 'Bandung'],
            ['code' => 'SBY', 'city_name' => 'Surabaya'],
            ['code' => 'YGY', 'city_name' => 'Yogyakarta'],
            ['code' => 'SMG', 'city_name' => 'Semarang'],
            ['code' => 'DPS', 'city_name' => 'Denpasar'],
        ];

        foreach ($destinations as $dest) {
            Destination::firstOrCreate($dest);
        }
    }
}