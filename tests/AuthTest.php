<?php

namespace Tests;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Organization;

class AuthTest extends TestCase
{
    // Use DatabaseMigrations for fresh database each test
    use DatabaseMigrations;

    /** @test */
    public function a_user_can_register()
    {
        $this->post('/register', [
            'organization_name' => 'Test Org',
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])->seeStatusCode(201)
          ->seeJsonStructure([
              'user' => [
                  'id', 'organization_id', 'name', 'email', 'role', 'api_token'
              ],
              'api_token'
          ]);

        $this->seeInDatabase('organizations', ['name' => 'Test Org']);
        $this->seeInDatabase('users', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'role' => 'admin'
        ]);

        $user = User::where('email', 'john@example.com')->first();
        $this->assertTrue(Hash::check('password', $user->password));
    }

    /** @test */
    public function a_user_can_login()
    {
        $organization = Organization::factory()->create(['name' => 'Login Org']);
        $user = User::factory()->for($organization)->create([
            'email' => 'login@example.com',
            'password' => Hash::make('secret'),
            'api_token' => null, // Ensure no token initially
        ]);

        $this->post('/login', [
            'email' => 'login@example.com',
            'password' => 'secret',
        ])->seeStatusCode(200)
          ->seeJsonStructure([
              'user' => [
                  'id', 'organization_id', 'name', 'email', 'role', 'api_token'
              ],
              'api_token'
          ]);

        $user = $user->fresh(); // Reload user to get new API token
        $this->assertNotNull($user->api_token);
    }

    /** @test */
    public function a_user_cannot_login_with_invalid_credentials()
    {
        $organization = Organization::factory()->create();
        User::factory()->for($organization)->create([
            'email' => 'badlogin@example.com',
            'password' => Hash::make('secret'),
        ]);

        $this->post('/login', [
            'email' => 'badlogin@example.com',
            'password' => 'wrong-secret',
        ])->seeStatusCode(401)
          ->seeJson(['message' => 'Unauthorized']);
    }
}