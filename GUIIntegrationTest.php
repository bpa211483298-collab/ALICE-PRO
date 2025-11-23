<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use App\Models\User;

class GUIIntegrationTest extends TestCase
{
    use WithFaker, WithoutMiddleware;

    protected $user;
    protected $password = 'password123';

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test user without database
        $this->user = new User([
            'id' => 1,
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt($this->password)
        ]);
    }

    /**
     * Test the main application page loads correctly
     */
    public function test_main_page_loads()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertViewIs('welcome');
        $response->assertSee('Laravel', false); // Check for Laravel in the page title
    }

    /**
     * Test user can log in with valid credentials
     */
    public function test_user_can_login_with_valid_credentials()
    {
        $response = $this->post('/login', [
            'email' => $this->user->email,
            'password' => $this->password,
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->user);
    }

    /**
     * Test user cannot log in with invalid credentials
     */
    public function test_user_cannot_login_with_invalid_credentials()
    {
        $response = $this->post('/login', [
            'email' => $this->user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    /**
     * Test authenticated user can access dashboard
     */
    public function test_authenticated_user_can_access_dashboard()
    {
        $response = $this->actingAs($this->user)
                        ->get('/dashboard');
                        
        $response->assertStatus(200);
        $response->assertViewIs('dashboard');
        $response->assertSee('Dashboard');
    }

    /**
     * Test unauthenticated user is redirected to login
     */
    public function test_unauthenticated_user_cannot_access_dashboard()
    {
        $response = $this->get('/dashboard');
        $response->assertRedirect('/login');
    }

    /**
     * Test user can log out
     */
    public function test_user_can_logout()
    {
        $response = $this->actingAs($this->user)
                        ->post('/logout');
                        
        $response->assertRedirect('/');
        $this->assertGuest();
    }

    /**
     * Test profile page is accessible
     */
    public function test_user_can_view_profile()
    {
        $response = $this->actingAs($this->user)
                        ->get('/profile');
                        
        $response->assertStatus(200);
        $response->assertViewIs('profile');
        $response->assertSee('Profile');
    }

    /**
     * Test user can update profile information
     */
    public function test_user_can_update_profile()
    {
        $newName = $this->faker->name;
        $newEmail = $this->faker->safeEmail;
        
        $response = $this->actingAs($this->user)
                        ->put('/profile', [
                            'name' => $newName,
                            'email' => $newEmail,
                        ]);
                        
        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/profile');
        
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => $newName,
            'email' => $newEmail,
        ]);
    }

    /**
     * Test user can change password
     */
    public function test_user_can_change_password()
    {
        $newPassword = 'new-password-123';
        
        $response = $this->actingAs($this->user)
                        ->put('/password', [
                            'current_password' => $this->password,
                            'password' => $newPassword,
                            'password_confirmation' => $newPassword,
                        ]);
                        
        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/profile');
        
        // Verify the password was changed by attempting to log in with the new password
        $this->post('/logout');
        
        $response = $this->post('/login', [
            'email' => $this->user->email,
            'password' => $newPassword,
        ]);
        
        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($this->user);
    }

    /**
     * Test 404 page is shown for non-existent routes
     */
    public function test_404_page_is_shown_for_non_existent_routes()
    {
        $response = $this->get('/non-existent-route');
        $response->assertStatus(404);
    }
}
