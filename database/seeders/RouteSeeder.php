<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Route;

class RouteSeeder extends Seeder
{
    public function run()
    {
        // 1. Jakarta - Bandung (Existing)
        Route::create([
            'source' => 'Jakarta Terminal 1',
            'source_code' => 'JKT',
            'destination_code' => 'BDG',
            'distance' => 150,
            'estimated_duration' => 180
        ]);

        Route::create([
            'source' => 'Bandung Terminal A',
            'source_code' => 'BDG',
            'destination_code' => 'JKT',
            'distance' => 150,
            'estimated_duration' => 180
        ]);

        // 2. New Routes
        
        // Jakarta - Surabaya (approx 780km, 10-12 hours)
        Route::create([
            'source' => 'Jakarta Terminal 1',
            'source_code' => 'JKT',
            'destination_code' => 'SBY',
            'distance' => 780,
            'estimated_duration' => 720
        ]);
        Route::create([
            'source' => 'Surabaya Bungurasih',
            'source_code' => 'SBY',
            'destination_code' => 'JKT',
            'distance' => 780,
            'estimated_duration' => 720
        ]);

        // Jakarta - Yogyakarta (approx 560km, 8-9 hours)
         Route::create([
            'source' => 'Jakarta Terminal 1',
            'source_code' => 'JKT',
            'destination_code' => 'YGY',
            'distance' => 560,
            'estimated_duration' => 540
        ]);
        Route::create([
            'source' => 'Yogyakarta Giwangan',
            'source_code' => 'YGY',
            'destination_code' => 'JKT',
            'distance' => 560,
            'estimated_duration' => 540
        ]);

        // Surabaya - Denpasar (approx 420km, 10-12 hours incl ferry)
        Route::create([
            'source' => 'Surabaya Bungurasih',
            'source_code' => 'SBY',
            'destination_code' => 'DPS',
            'distance' => 450,
            'estimated_duration' => 720 
        ]);
        Route::create([
            'source' => 'Denpasar Mengwi',
            'source_code' => 'DPS',
            'destination_code' => 'SBY',
            'distance' => 450,
            'estimated_duration' => 720
        ]);
        
        // Semarang - Yogyakarta (approx 130km, 3-4 hours)
        Route::create([
            'source' => 'Semarang Terboyo',
            'source_code' => 'SMG',
            'destination_code' => 'YGY',
            'distance' => 130,
            'estimated_duration' => 240
        ]);
        Route::create([
             'source' => 'Yogyakarta Giwangan',
            'source_code' => 'YGY',
            'destination_code' => 'SMG',
            'distance' => 130,
            'estimated_duration' => 240
        ]);

        // Bandung - Yogyakarta (approx 400km, 7-9 hours)
        Route::create([
            'source' => 'Bandung Terminal A',
            'source_code' => 'BDG',
            'destination_code' => 'YGY',
            'distance' => 410,
            'estimated_duration' => 540
        ]);
        Route::create([
             'source' => 'Yogyakarta Giwangan',
            'source_code' => 'YGY',
            'destination_code' => 'BDG',
            'distance' => 410,
            'estimated_duration' => 540
        ]);
    }
}
