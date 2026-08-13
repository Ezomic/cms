<?php

namespace Tests\Feature\Admin;

use App\Models\Profile;
use App\Models\Project;
use App\Models\Testimonial;
use App\Models\User;
use App\Services\ContentCompleteness;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ContentCompletenessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // A complete profile by default, so profile gaps do not leak into the
        // project assertions below.
        Profile::current()->update([
            'tagline' => 'Dev', 'tagline_nl' => 'Ontwikkelaar',
            'hero_headline' => 'H', 'hero_headline_nl' => 'H',
            'hero_subtext' => 'S', 'hero_subtext_nl' => 'S',
            'docs_intro' => 'D', 'docs_intro_nl' => 'D',
            'meta_title' => 'M', 'meta_title_nl' => 'M',
            'meta_description' => 'MD', 'meta_description_nl' => 'MD',
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function completeProject(array $attributes = []): Project
    {
        return Project::create(array_merge([
            'name' => 'Acme Site',
            'description' => 'A description', 'description_nl' => 'Een omschrijving',
            'outcome' => 'An outcome', 'outcome_nl' => 'Een resultaat',
            'body' => '<p>Body</p>', 'body_nl' => '<p>Body</p>',
            'meta_title' => 'Title', 'meta_title_nl' => 'Titel',
            'meta_description' => 'Desc', 'meta_description_nl' => 'Omschrijving',
            'image' => 'projects/hero.jpg', 'image_alt' => 'Alt', 'image_alt_nl' => 'Alt',
            'published' => true,
            'sort_order' => 0,
        ], $attributes));
    }

    /**
     * @return list<string>
     */
    private function gapsFor(string $label): array
    {
        $row = app(ContentCompleteness::class)->report()->first(fn ($gap) => $gap->label === $label);

        return $row?->gaps ?? [];
    }

    public function test_a_complete_project_reports_nothing(): void
    {
        $this->completeProject();

        $this->assertSame(0, app(ContentCompleteness::class)->count());
    }

    public function test_it_flags_a_missing_dutch_translation(): void
    {
        $this->completeProject(['description_nl' => null]);

        $this->assertContains('Dutch description', $this->gapsFor('Acme Site'));
    }

    public function test_it_flags_missing_seo_fields(): void
    {
        $this->completeProject(['meta_title' => null, 'meta_description' => null]);

        $gaps = $this->gapsFor('Acme Site');

        $this->assertContains('Meta title', $gaps);
        $this->assertContains('Meta description', $gaps);
    }

    public function test_it_flags_missing_alt_text(): void
    {
        $this->completeProject(['image_alt' => null]);

        $this->assertContains('Image alt text', $this->gapsFor('Acme Site'));
    }

    public function test_it_flags_a_published_project_with_no_cover_image(): void
    {
        $this->completeProject(['image' => null]);

        $gaps = $this->gapsFor('Acme Site');

        $this->assertContains('Cover image', $gaps);
        // Alt text is meaningless without an image, so it is not also reported.
        $this->assertNotContains('Image alt text', $gaps);
    }

    public function test_it_flags_an_uncaptioned_gallery_image(): void
    {
        $project = $this->completeProject();
        $project->images()->create(['path' => 'projects/shot.jpg', 'sort_order' => 0]);

        $this->assertContains('Gallery caption', $this->gapsFor('Acme Site'));
    }

    /**
     * A draft being incomplete is the normal state of a draft. Flagging them
     * would bury the gaps that actually affect visitors.
     */
    public function test_drafts_are_not_checked(): void
    {
        $this->completeProject(['published' => false, 'description_nl' => null, 'meta_title' => null]);

        $this->assertSame(0, app(ContentCompleteness::class)->count());
    }

    /**
     * With no English source there is nothing to translate, so an empty pair
     * is not a gap. Otherwise every optional field would be permanently red.
     */
    public function test_an_empty_field_in_both_languages_is_not_a_gap(): void
    {
        $this->completeProject(['outcome' => null, 'outcome_nl' => null]);

        $this->assertNotContains('Dutch outcome', $this->gapsFor('Acme Site'));
    }

    public function test_it_flags_a_testimonial_missing_its_dutch_quote(): void
    {
        Testimonial::create(['quote' => 'Great work', 'author_name' => 'Jane Doe']);

        $this->assertContains('Dutch quote', $this->gapsFor('Jane Doe'));
    }

    public function test_it_flags_profile_gaps(): void
    {
        Profile::current()->update(['tagline_nl' => null]);

        $report = app(ContentCompleteness::class)->report();
        $row = $report->first(fn ($gap) => $gap->type === 'Profile');

        $this->assertNotNull($row);
        $this->assertContains('Dutch tagline', $row->gaps);
    }

    public function test_every_row_links_to_an_edit_screen(): void
    {
        $this->completeProject(['description_nl' => null]);
        Testimonial::create(['quote' => 'Great', 'author_name' => 'Jane']);
        Profile::current()->update(['meta_title' => null]);

        foreach (app(ContentCompleteness::class)->report() as $row) {
            $this->assertNotEmpty($row->editUrl);
            $this->assertStringContainsString('/admin/', $row->editUrl);
        }
    }

    public function test_the_dashboard_shows_the_gap_count(): void
    {
        $this->completeProject(['description_nl' => null]);

        $this->actingAs(User::factory()->create())->get('/admin')->assertInertia(
            fn (Assert $page) => $page->where('contentGapCount', 1)->etc()
        );
    }

    public function test_the_detail_page_renders(): void
    {
        $this->completeProject(['description_nl' => null]);

        $this->actingAs(User::factory()->create())->get('/admin/content-gaps')->assertInertia(
            fn (Assert $page) => $page
                ->component('ContentGaps/Index')
                ->where('rows.0.label', 'Acme Site')
                ->where('rows.0.type', 'Project')
                ->etc()
        );
    }

    public function test_the_detail_page_is_behind_auth(): void
    {
        $this->get('/admin/content-gaps')->assertRedirect('/admin/login');
    }
}
