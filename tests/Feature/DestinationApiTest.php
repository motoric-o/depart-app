<?php

namespace Tests\Feature;

use App\Models\Destination;
use App\Models\Route;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DestinationApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_fetch_destinations_and_sources()
    {
        // 1. Arrange: Create data
        $dest1 = Destination::create(['code' => 'BDG', 'city_name' => 'Bandung']);
        $dest2 = Destination::create(['code' => 'JKT', 'city_name' => 'Jakarta']);
        
        // Create routes to populate "sources"
        Route::create(['source' => 'Jakarta', 'destination_code' => 'BDG']);
        Route::create(['source' => 'Surabaya', 'destination_code' => 'JKT']);
        Route::create(['source' => 'Jakarta', 'destination_code' => 'BDG']); // Duplicate source to test distinct

        // 2. Act
        $response = $this->getJson('/api/destinations');

        // 3. Assert
        $response->assertStatus(200)
                 ->assertJsonStructure(['sources', 'destinations']);

        // Check sources (should contain unique values)
        $this->assertTrue(in_array('Jakarta', $response->json('sources')));
        $this->assertTrue(in_array('Surabaya', $response->json('sources')));
        $this->assertCount(2, $response->json('sources')); // 'Jakarta' and 'Surabaya'

        // Check destinations
        $this->assertCount(2, $response->json('destinations'));
        $response->assertJsonFragment(['code' => 'BDG', 'city_name' => 'Bandung']);
    }
}
