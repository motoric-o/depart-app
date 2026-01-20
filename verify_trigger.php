<?php

use App\Models\Schedule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// 1. Create Data
$destCode = 'TST';
DB::statement("INSERT INTO destinations (code, city_name, created_at, updated_at) VALUES (?, ?, NOW(), NOW()) ON CONFLICT DO NOTHING", [$destCode, 'Test City']);

$routeId = 'RTE-TST-001';
DB::statement("DELETE FROM routes WHERE id = ?", [$routeId]); // Cleanup
DB::statement("INSERT INTO routes (id, source, destination_code, distance, estimated_duration, created_at, updated_at) VALUES (?, ?, ?, 100, 60, NOW(), NOW())", [$routeId, 'Test Terminal', $destCode]);

$scheduleId = 'SCH-TST-001';
DB::statement("DELETE FROM schedules WHERE id = ?", [$scheduleId]);
DB::statement("INSERT INTO schedules (id, route_id, bus_id, driver_id, departure_time, arrival_time, price_per_seat, quota, remarks, created_at, updated_at) VALUES (?, ?, NULL, NULL, NOW(), NOW(), 100000, 10, 'Scheduled', NOW(), NOW())", [$scheduleId, $routeId]);

echo "Created Schedule: $scheduleId with Route: $routeId\n";

// 2. Delete Route (Should trigger snapshot)
echo "Deleting Route...\n";
DB::statement("DELETE FROM routes WHERE id = ?", [$routeId]);

// 3. Verify
$schedule = DB::table('schedules')->where('id', $scheduleId)->first();

echo "Schedule Status: " . $schedule->remarks . "\n";
echo "Route Source: " . ($schedule->route_source ?? 'NULL') . "\n"; // Expect 'Test Terminal'
echo "Route Dest: " . ($schedule->route_destination ?? 'NULL') . "\n"; // Expect 'Test City'

// Cleanup
DB::statement("DELETE FROM schedules WHERE id = ?", [$scheduleId]);
DB::statement("DELETE FROM destinations WHERE code = ?", [$destCode]);
