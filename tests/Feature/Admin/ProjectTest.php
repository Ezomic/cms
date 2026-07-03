<?php

namespace Tests\Feature\Admin;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_project_admin_routes(): void
    {
        $project = Project::create(['name' => 'Test Project', 'sort_order' => 0]);

        $this->get('/admin/projects')->assertRedirect('/admin/login');
        $this->get("/admin/projects/{$project->id}/edit")->assertRedirect('/admin/login');
    }

    public function test_admin_can_create_a_project(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/admin/projects', [
            'name'        => 'New Project',
            'client_name' => 'Acme Inc',
            'year'        => '2026',
            'description' => 'Shipped a thing.',
            'tags'        => 'Laravel, Vue',
            'sort_order'  => 1,
        ]);

        $response->assertRedirect('/admin/projects');
        $this->assertDatabaseHas('projects', ['name' => 'New Project', 'client_name' => 'Acme Inc']);
    }

    public function test_creating_a_project_requires_a_name(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/admin/projects', ['name' => '']);

        $response->assertSessionHasErrors('name');
    }

    public function test_admin_can_update_a_project(): void
    {
        $user = User::factory()->create();
        $project = Project::create(['name' => 'Old Name', 'sort_order' => 0]);

        $response = $this->actingAs($user)->put("/admin/projects/{$project->id}", [
            'name'       => 'Updated Name',
            'sort_order' => 0,
        ]);

        $response->assertRedirect('/admin/projects');
        $this->assertDatabaseHas('projects', ['id' => $project->id, 'name' => 'Updated Name']);
    }

    public function test_admin_can_delete_a_project(): void
    {
        $user = User::factory()->create();
        $project = Project::create(['name' => 'To Delete', 'sort_order' => 0]);

        $response = $this->actingAs($user)->delete("/admin/projects/{$project->id}");

        $response->assertRedirect();
        $this->assertSoftDeleted('projects', ['id' => $project->id]);
    }
}
