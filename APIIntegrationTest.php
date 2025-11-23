<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Support\Facades\Artisan;

class APIIntegrationTest extends TestCase
{
    use WithFaker, WithoutMiddleware;

    protected $user;
    protected $token;
    protected $password = 'password123';

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test user without database
        $this->user = new User([
            'id' => 1,
            'name' => 'API Test User',
            'email' => 'api@example.com',
            'password' => bcrypt($this->password)
        ]);
        
        // Create a test token
        $this->token = 'test-token-12345';
    }

    /**
     * Test API authentication with valid token
     */
    public function test_api_authentication_with_valid_token()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->get('/api/user');
        
        $response->assertStatus(200);
        $response->assertJson([
            'id' => $this->user->id,
            'email' => $this->user->email,
        ]);
    }

    /**
     * Test API authentication without token
     */
    public function test_api_authentication_without_token()
    {
        $response = $this->withHeaders([
            'Accept' => 'application/json',
        ])->get('/api/user');
        
        $response->assertStatus(401);
    }

    /**
     * Test API authentication with invalid token
     */
    public function test_api_authentication_with_invalid_token()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid-token',
            'Accept' => 'application/json',
        ])->get('/api/user');
        
        $response->assertStatus(401);
    }

    /**
     * Test user registration via API
     */
    public function test_user_registration_via_api()
    {
        $userData = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson('/api/register', $userData);
        
        $response->assertStatus(201);
        $response->assertJsonStructure([
            'user' => [
                'id',
                'name',
                'email',
                'created_at',
                'updated_at',
            ],
            'token'
        ]);
        
        $this->assertDatabaseHas('users', [
            'email' => $userData['email']
        ]);
    }

    /**
     * Test user login via API
     */
    public function test_user_login_via_api()
    {
        $response = $this->postJson('/api/login', [
            'email' => $this->user->email,
            'password' => $this->password,
        ]);
        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'user' => [
                'id',
                'name',
                'email',
            ],
            'token'
        ]);
    }

    /**
     * Test user logout via API
     */
    public function test_user_logout_via_api()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->post('/api/logout');
        
        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Successfully logged out'
        ]);
    }

    /**
     * Test getting authenticated user data
     */
    public function test_get_authenticated_user_data()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->get('/api/user');
        
        $response->assertStatus(200);
        $response->assertJson([
            'id' => $this->user->id,
            'email' => $this->user->email,
        ]);
    }

    /**
     * Test updating user profile via API
     */
    public function test_update_user_profile_via_api()
    {
        $newData = [
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ];
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->put('/api/user/profile', $newData);
        
        $response->assertStatus(200);
        $response->assertJson([
            'name' => $newData['name'],
            'email' => $newData['email'],
        ]);
        
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => $newData['name'],
            'email' => $newData['email'],
        ]);
    }

    /**
     * Test changing password via API
     */
    public function test_change_password_via_api()
    {
        $newPassword = 'new-password-123';
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
            'Accept' => 'application/json',
        ])->post('/api/user/change-password', [
            'current_password' => $this->password,
            'password' => $newPassword,
            'password_confirmation' => $newPassword,
        ]);
        
        $response->assertStatus(200);
        $response->assertJson([
            'message' => 'Password changed successfully'
        ]);
        
        // Verify the password was changed by attempting to log in with the new password
        $loginResponse = $this->postJson('/api/login', [
            'email' => $this->user->email,
            'password' => $newPassword,
        ]);
        
        $loginResponse->assertStatus(200);
    }
}
