<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;

class GuiTest extends TestCase
{
    use WithFaker, WithoutMiddleware;

    /**
     * Test the main page loads successfully.
     *
     * @return void
     */
    public function test_main_page_loads()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertViewIs('welcome');
        $response->assertSee('Laravel', false); // Check for Laravel in the page title
    }

    /**
     * Test the login page is accessible.
     *
     * @return void
     */
    public function test_login_page_loads()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Login');
        $response->assertSee('Email');
        $response->assertSee('Password');
    }

    /**
     * Test the dashboard is protected and redirects to login.
     *
     * @return void
     */
    public function test_dashboard_requires_authentication()
    {
        $response = $this->get('/dashboard');
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /**
     * Test the MCP services page loads for authenticated users.
     *
     * @return void
     */
    public function test_mcp_services_page_loads()
    {
        $user = \App\Models\User::factory()->create();
        
        $response = $this->actingAs($user)
                         ->get('/mcp/services');
                         
        $response->assertStatus(200);
        $response->assertSee('MCP Services');
    }

    /**
     * Test the code generation interface loads.
     *
     * @return void
     */
    public function test_code_generation_interface_loads()
    {
        $user = \App\Models\User::factory()->create();
        
        $response = $this->actingAs($user)
                         ->get('/code/generate');
                         
        $response->assertStatus(200);
        $response->assertSee('Code Generation');
        $response->assertSee('Generate Code');
    }

    /**
     * Test the self-healing interface loads.
     *
     * @return void
     */
    public function test_self_healing_interface_loads()
    {
        $user = \App\Models\User::factory()->create();
        
        $response = $this->actingAs($user)
                         ->get('/self-healing');
                         
        $response->assertStatus(200);
        $response->assertSee('Self Healing');
        $response->assertSee('Analyze Code');
    }
}
