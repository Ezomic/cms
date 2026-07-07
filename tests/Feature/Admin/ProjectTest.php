<?php

namespace Tests\Feature\Admin;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
            'name' => 'New Project',
            'client_name' => 'Acme Inc',
            'year' => '2026',
            'description' => 'Shipped a thing.',
            'tags' => 'Laravel, Vue',
            'sort_order' => 1,
        ]);

        $response->assertRedirect('/admin/projects');
        $this->assertDatabaseHas('projects', ['name' => 'New Project', 'client_name' => 'Acme Inc']);
    }

    public function test_admin_can_set_project_meta_fields(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/admin/projects', [
            'name' => 'SEO Project',
            'meta_title' => 'Custom title',
            'meta_description' => 'Custom description.',
        ]);

        $this->assertDatabaseHas('projects', [
            'name' => 'SEO Project',
            'meta_title' => 'Custom title',
            'meta_description' => 'Custom description.',
        ]);
    }

    public function test_project_meta_fields_are_limited_to_255_characters(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/admin/projects', [
            'name' => 'SEO Project',
            'meta_description' => str_repeat('a', 256),
        ]);

        $response->assertSessionHasErrors('meta_description');
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
            'name' => 'Updated Name',
            'sort_order' => 0,
        ]);

        $response->assertRedirect('/admin/projects');
        $this->assertDatabaseHas('projects', ['id' => $project->id, 'name' => 'Updated Name']);
    }

    public function test_admin_can_set_image_alt_text(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/admin/projects', [
            'name' => 'Accessible Project',
            'image_alt' => 'Dashboard screenshot showing the invoice list',
        ]);

        $this->assertDatabaseHas('projects', [
            'name' => 'Accessible Project',
            'image_alt' => 'Dashboard screenshot showing the invoice list',
        ]);
    }

    public function test_image_alt_falls_back_to_project_name(): void
    {
        $project = Project::create(['name' => 'Fallback Project', 'sort_order' => 0]);

        $this->assertSame('Fallback Project', $project->imageAlt());

        $project->update(['image_alt' => 'A custom description']);

        $this->assertSame('A custom description', $project->fresh()->imageAlt());
    }

    public function test_admin_can_delete_a_project(): void
    {
        $user = User::factory()->create();
        $project = Project::create(['name' => 'To Delete', 'sort_order' => 0]);

        $response = $this->actingAs($user)->delete("/admin/projects/{$project->id}");

        $response->assertRedirect();
        $this->assertSoftDeleted('projects', ['id' => $project->id]);
    }

    public function test_admin_can_set_github_url_on_a_project(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/admin/projects', [
            'name' => 'Open Source Project',
            'github_url' => 'https://github.com/example/repo',
        ]);

        $this->assertDatabaseHas('projects', [
            'name' => 'Open Source Project',
            'github_url' => 'https://github.com/example/repo',
        ]);
    }

    public function test_github_url_must_be_a_valid_url(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/admin/projects', [
            'name' => 'Bad URL Project',
            'github_url' => 'not-a-url',
        ]);

        $response->assertSessionHasErrors('github_url');
    }

    public function test_admin_can_upload_gallery_images_when_creating_a_project(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/admin/projects', [
            'name' => 'Gallery Project',
            'gallery' => [
                UploadedFile::fake()->image('screen-1.jpg'),
                UploadedFile::fake()->image('screen-2.jpg'),
            ],
        ]);

        $response->assertRedirect('/admin/projects');

        $project = Project::where('name', 'Gallery Project')->firstOrFail();
        $this->assertCount(2, $project->images);

        foreach ($project->images as $image) {
            Storage::disk('public')->assertExists($image->path);
        }
    }

    public function test_admin_can_add_gallery_images_when_updating_a_project(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $project = Project::create(['name' => 'Existing Project', 'sort_order' => 0]);

        $this->actingAs($user)->put("/admin/projects/{$project->id}", [
            'name' => 'Existing Project',
            'sort_order' => 0,
            'gallery' => [UploadedFile::fake()->image('screen.jpg')],
        ]);

        $this->assertCount(1, $project->fresh()->images);
    }

    public function test_admin_can_remove_a_gallery_image(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $project = Project::create(['name' => 'Existing Project', 'sort_order' => 0]);
        $image = $project->images()->create(['path' => 'projects/screen.jpg', 'sort_order' => 0]);
        Storage::disk('public')->put($image->path, 'fake-contents');

        $this->actingAs($user)->put("/admin/projects/{$project->id}", [
            'name' => 'Existing Project',
            'sort_order' => 0,
            'remove_images' => [$image->id],
        ]);

        $this->assertModelMissing($image);
        Storage::disk('public')->assertMissing($image->path);
    }

    public function test_uploading_more_than_eight_gallery_images_fails_validation(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $files = [];
        for ($i = 0; $i < 9; $i++) {
            $files[] = UploadedFile::fake()->image("screen-{$i}.jpg");
        }

        $response = $this->actingAs($user)->post('/admin/projects', [
            'name' => 'Too Many Images',
            'gallery' => $files,
        ]);

        $response->assertSessionHasErrors('gallery');
        $this->assertDatabaseMissing('projects', ['name' => 'Too Many Images']);
    }

    public function test_uploading_exactly_eight_gallery_images_is_allowed(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $files = [];
        for ($i = 0; $i < 8; $i++) {
            $files[] = UploadedFile::fake()->image("screen-{$i}.jpg");
        }

        $response = $this->actingAs($user)->post('/admin/projects', [
            'name' => 'Exactly Eight Images',
            'gallery' => $files,
        ]);

        $response->assertSessionDoesntHaveErrors('gallery');
        $project = Project::where('name', 'Exactly Eight Images')->firstOrFail();
        $this->assertCount(8, $project->images);
    }

    public function test_admin_cannot_remove_a_gallery_image_belonging_to_another_project(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $projectA = Project::create(['name' => 'Project A', 'sort_order' => 0]);
        $projectB = Project::create(['name' => 'Project B', 'sort_order' => 1]);

        $imageOnB = $projectB->images()->create(['path' => 'projects/b-screen.jpg', 'sort_order' => 0]);
        Storage::disk('public')->put($imageOnB->path, 'fake-contents');

        $this->actingAs($user)->put("/admin/projects/{$projectA->id}", [
            'name' => 'Project A',
            'sort_order' => 0,
            'remove_images' => [$imageOnB->id],
        ]);

        $this->assertModelExists($imageOnB);
        Storage::disk('public')->assertExists($imageOnB->path);
    }

    public function test_soft_deleting_a_project_keeps_gallery_images_and_files(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $project = Project::create(['name' => 'Soft Delete Project', 'sort_order' => 0]);
        $image = $project->images()->create(['path' => 'projects/soft-delete.jpg', 'sort_order' => 0]);
        Storage::disk('public')->put($image->path, 'fake-contents');

        $this->actingAs($user)->delete("/admin/projects/{$project->id}");

        $this->assertSoftDeleted('projects', ['id' => $project->id]);
        $this->assertModelExists($image);
        Storage::disk('public')->assertExists($image->path);
    }

    public function test_restoring_a_soft_deleted_project_keeps_gallery_intact(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $project = Project::create(['name' => 'Restore Project', 'sort_order' => 0]);
        $image = $project->images()->create(['path' => 'projects/restore.jpg', 'sort_order' => 0]);
        Storage::disk('public')->put($image->path, 'fake-contents');

        $project->delete();
        $this->actingAs($user)->post("/admin/projects/{$project->id}/restore");

        $this->assertNotSoftDeleted($project->fresh());
        $this->assertCount(1, $project->fresh()->images);
        Storage::disk('public')->assertExists($image->fresh()->path);
    }

    public function test_force_deleting_a_project_removes_gallery_files_and_cascades_rows(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();

        $project = Project::create(['name' => 'Force Delete Project', 'sort_order' => 0]);
        $imageOne = $project->images()->create(['path' => 'projects/force-1.jpg', 'sort_order' => 0]);
        $imageTwo = $project->images()->create(['path' => 'projects/force-2.jpg', 'sort_order' => 1]);
        Storage::disk('public')->put($imageOne->path, 'fake-contents');
        Storage::disk('public')->put($imageTwo->path, 'fake-contents');

        $project->delete();

        $response = $this->actingAs($user)->delete("/admin/projects/{$project->id}/force");

        $response->assertRedirect();
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
        $this->assertDatabaseMissing('project_images', ['id' => $imageOne->id]);
        $this->assertDatabaseMissing('project_images', ['id' => $imageTwo->id]);
        Storage::disk('public')->assertMissing($imageOne->path);
        Storage::disk('public')->assertMissing($imageTwo->path);
    }
}
