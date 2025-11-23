<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\Project;

class CodeGenerationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $project;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create a test user
        $this->user = User::factory()->create();
        
        // Create a test project
        $this->project = Project::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Test Project',
            'description' => 'A test project for code generation'
        ]);
    }

    /**
     * Test the code generation page loads
     */
    public function test_code_generation_page_loads()
    {
        $response = $this->actingAs($this->user)
                        ->get('/projects/' . $this->project->id . '/generate');
                        
        $response->assertStatus(200);
        $response->assertViewIs('code.generator');
        $response->assertViewHas('project');
        $response->assertViewHas('templates');
        $response->assertSee('Code Generator');
    }

    /**
     * Test generating code with valid input
     */
    public function test_generate_code_with_valid_input()
    {
        $response = $this->actingAs($this->user)
                        ->post('/api/generate/code', [
                            'project_id' => $this->project->id,
                            'template' => 'laravel-crud',
                            'parameters' => [
                                'model' => 'Product',
                                'fields' => [
                                    ['name' => 'name', 'type' => 'string'],
                                    ['name' => 'price', 'type' => 'decimal']
                                ]
                            ]
                        ]);
                        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'message',
            'files' => [
                '*' => [
                    'path',
                    'content',
                    'type'
                ]
            ]
        ]);
    }

    /**
     * Test generating code with invalid input
     */
    public function test_generate_code_with_invalid_input()
    {
        $response = $this->actingAs($this->user)
                        ->post('/api/generate/code', [
                            'project_id' => $this->project->id,
                            'template' => 'invalid-template',
                            'parameters' => []
                        ]);
                        
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['template']);
    }

    /**
     * Test listing available templates
     */
    public function test_list_available_templates()
    {
        $response = $this->actingAs($this->user)
                        ->get('/api/templates');
                        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            '*' => [
                'id',
                'name',
                'description',
                'parameters'
            ]
        ]);
    }

    /**
     * Test getting template details
     */
    public function test_get_template_details()
    {
        $response = $this->actingAs($this->user)
                        ->get('/api/templates/laravel-crud');
                        
        $response->assertStatus(200);
        $response->assertJsonStructure([
            'id',
            'name',
            'description',
            'parameters' => [
                '*' => [
                    'name',
                    'type',
                    'required',
                    'description'
                ]
            ]
        ]);
    }
}
