<?php

namespace Tests\Feature\Admin;

use App\Models\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SkillTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_skill_admin_routes(): void
    {
        $this->get('/admin/skills')->assertRedirect('/admin/login');
    }

    public function test_admin_can_create_a_skill(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/admin/skills', [
            'category'   => 'Backend',
            'name'       => 'Laravel',
            'sort_order' => 0,
        ]);

        $response->assertRedirect('/admin/skills');
        $this->assertDatabaseHas('skills', ['category' => 'Backend', 'name' => 'Laravel']);
    }

    public function test_creating_a_skill_requires_category_and_name(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/admin/skills', ['category' => '', 'name' => '']);

        $response->assertSessionHasErrors(['category', 'name']);
    }

    public function test_admin_can_update_a_skill(): void
    {
        $user = User::factory()->create();
        $skill = Skill::create(['category' => 'Backend', 'name' => 'PHP', 'sort_order' => 0]);

        $response = $this->actingAs($user)->put("/admin/skills/{$skill->id}", [
            'category'   => 'Backend',
            'name'       => 'PHP 8',
            'sort_order' => 0,
        ]);

        $response->assertRedirect('/admin/skills');
        $this->assertDatabaseHas('skills', ['id' => $skill->id, 'name' => 'PHP 8']);
    }

    public function test_admin_can_delete_a_skill(): void
    {
        $user = User::factory()->create();
        $skill = Skill::create(['category' => 'Backend', 'name' => 'To Delete', 'sort_order' => 0]);

        $response = $this->actingAs($user)->delete("/admin/skills/{$skill->id}");

        $response->assertRedirect();
        $this->assertSoftDeleted('skills', ['id' => $skill->id]);
    }
}
