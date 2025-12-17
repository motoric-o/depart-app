<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\AccountType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed the AccountType required by AuthController
        AccountType::create(['name' => 'Customer', 'description' => 'Regular Customer']);
    }

    public function test_user_can_register()
    {
        $payload = [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '08123456789'
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertStatus(201)
                 ->assertJsonPath('message', 'Registration successful')
                 ->assertJsonStructure(['access_token', 'user']);

        $this->assertDatabaseHas('accounts', [
            'email' => 'john@example.com',
            'first_name' => 'John'
        ]);
    }

    public function test_user_cannot_register_with_duplicate_email()
    {
        Account::factory()->create([
            'email' => 'john@example.com'
        ]);

        $payload = [
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/register', $payload);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    public function test_user_can_login()
    {
        $user = Account::factory()->create([
            'email' => 'test@example.com',
            'password_hash' => Hash::make('password123')
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['access_token']);
    }

    public function test_user_cannot_login_with_invalid_credentials()
    {
        $user = Account::factory()->create([
            'email' => 'test@example.com',
            'password_hash' => Hash::make('password123')
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['email']);
    }

    public function test_logged_in_user_can_fetch_own_profile()
    {
        $user = Account::factory()->create();

        $response = $this->actingAs($user)->getJson('/api/user');

        $response->assertStatus(200)
                 ->assertJsonPath('id', $user->id)
                 ->assertJsonPath('email', $user->email);
    }
}
