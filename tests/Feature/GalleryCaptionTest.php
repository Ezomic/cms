<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GalleryCaptionTest extends TestCase
{
    use RefreshDatabase;

    private function projectWithImage(?string $caption = null, ?string $captionNl = null): Project
    {
        $project = Project::create([
            'name' => 'Acme Site',
            'body' => '<p>Body.</p>',
            'published' => true,
            'sort_order' => 0,
        ]);

        $project->images()->create([
            'path' => 'projects/shot.jpg',
            'sort_order' => 0,
            'caption' => $caption,
            'caption_nl' => $captionNl,
        ]);

        return $project;
    }

    public function test_a_caption_renders_under_the_image(): void
    {
        $this->projectWithImage('The scheduling screen');

        $response = $this->get('/work/acme-site');

        $response->assertOk();
        $response->assertSee('<figcaption>The scheduling screen</figcaption>', false);
    }

    public function test_a_caption_becomes_the_alt_text(): void
    {
        $this->projectWithImage('The scheduling screen');

        $response = $this->get('/work/acme-site');

        $response->assertSee('alt="The scheduling screen"', false);
    }

    public function test_alt_text_falls_back_to_the_project_name(): void
    {
        $this->projectWithImage();

        $response = $this->get('/work/acme-site');

        $response->assertSee('alt="Acme Site"', false);
        $response->assertDontSee('<figcaption>', false);
    }

    public function test_the_dutch_page_uses_the_dutch_caption(): void
    {
        $this->projectWithImage('The scheduling screen', 'Het planningsscherm');

        $response = $this->get('/nl/work/acme-site');

        $response->assertSee('Het planningsscherm');
        $response->assertDontSee('The scheduling screen');
    }

    public function test_the_dutch_page_falls_back_to_the_english_caption(): void
    {
        $this->projectWithImage('The scheduling screen');

        $response = $this->get('/nl/work/acme-site');

        $response->assertSee('The scheduling screen');
    }

    public function test_the_trigger_is_a_button_so_it_is_keyboard_reachable(): void
    {
        $this->projectWithImage('A screen');

        $response = $this->get('/work/acme-site');

        $response->assertSee('<button type="button" class="shot-trigger"', false);
        $response->assertSee('role="dialog"', false);
        $response->assertSee('aria-modal="true"', false);
    }

    public function test_the_lightbox_markup_is_absent_when_there_is_no_gallery(): void
    {
        Project::create(['name' => 'No Gallery', 'body' => '<p>x</p>', 'published' => true, 'sort_order' => 0]);

        $response = $this->get('/work/no-gallery');

        $response->assertOk();
        // The stylesheet always ships, so match the markup rather than the
        // class name, which appears in the <style> block regardless.
        $response->assertDontSee('<button type="button" class="shot-trigger"', false);
        $response->assertDontSee('role="dialog"', false);
    }

    public function test_an_admin_can_save_captions(): void
    {
        $project = $this->projectWithImage();
        $image = $project->images()->first();

        $this->actingAs(User::factory()->create())->put("/admin/projects/{$project->id}", [
            'name' => 'Acme Site',
            'sort_order' => 0,
            'captions' => [$image->id => 'Saved caption'],
            'captions_nl' => [$image->id => 'Opgeslagen bijschrift'],
        ]);

        $this->assertSame('Saved caption', $image->fresh()->caption);
        $this->assertSame('Opgeslagen bijschrift', $image->fresh()->caption_nl);
    }

    public function test_captions_cannot_be_written_onto_another_projects_image(): void
    {
        $mine = $this->projectWithImage();
        $theirs = Project::create(['name' => 'Other', 'published' => true, 'sort_order' => 1]);
        $theirImage = $theirs->images()->create(['path' => 'projects/other.jpg', 'sort_order' => 0, 'caption' => 'Untouched']);

        $this->actingAs(User::factory()->create())->put("/admin/projects/{$mine->id}", [
            'name' => 'Acme Site',
            'sort_order' => 0,
            'captions' => [$theirImage->id => 'Hijacked'],
        ]);

        $this->assertSame('Untouched', $theirImage->fresh()->caption);
    }

    public function test_clearing_a_caption_stores_null_rather_than_an_empty_string(): void
    {
        $project = $this->projectWithImage('Something');
        $image = $project->images()->first();

        $this->actingAs(User::factory()->create())->put("/admin/projects/{$project->id}", [
            'name' => 'Acme Site',
            'sort_order' => 0,
            'captions' => [$image->id => ''],
        ]);

        $this->assertNull($image->fresh()->caption);
    }
}
