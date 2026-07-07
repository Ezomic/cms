<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\Project;
use App\Models\Skill;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CvTest extends TestCase
{
    use RefreshDatabase;

    public function test_cv_downloads_as_a_pdf(): void
    {
        $response = $this->get('/cv.pdf');

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_cv_download_is_attached_with_a_filename(): void
    {
        $response = $this->get('/cv.pdf');

        $response->assertOk();
        $response->assertHeader('content-disposition');
        $this->assertStringContainsString('attachment', $response->headers->get('content-disposition'));
        $this->assertStringContainsString('cv.pdf', $response->headers->get('content-disposition'));
    }

    public function test_cv_query_limits_published_projects_to_four(): void
    {
        for ($i = 1; $i <= 6; $i++) {
            Project::create(['name' => "Project {$i}", 'sort_order' => $i, 'published' => true]);
        }

        $projects = Project::published()->ordered()->take(4)->get();

        $this->assertCount(4, $projects);
    }

    public function test_cv_route_still_renders_successfully_with_more_than_four_published_projects(): void
    {
        for ($i = 1; $i <= 6; $i++) {
            Project::create(['name' => "Project {$i}", 'sort_order' => $i, 'published' => true]);
        }

        $response = $this->get('/cv.pdf');

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_cv_query_excludes_unpublished_projects_from_the_four_project_cap(): void
    {
        Project::create(['name' => 'Published One', 'sort_order' => 0, 'published' => true]);
        Project::create(['name' => 'Published Two', 'sort_order' => 1, 'published' => true]);
        Project::create(['name' => 'Unpublished One', 'sort_order' => 2, 'published' => false]);

        $projects = Project::published()->ordered()->take(4)->get();

        $this->assertCount(2, $projects);
        $this->assertTrue($projects->pluck('name')->doesntContain('Unpublished One'));
    }

    public function test_cv_view_only_renders_the_projects_it_is_given(): void
    {
        $profile = Profile::current();
        $skills = Skill::query()->get()->groupBy('category');

        $projects = collect(range(1, 6))->map(fn ($i) => (object) [
            'name' => "Case Study {$i}",
            'year' => '2024',
            'client_name' => null,
            'description' => null,
            'tag_list' => [],
        ])->take(4);

        $html = view('cv', ['profile' => $profile, 'skills' => $skills, 'projects' => $projects])->render();

        $this->assertStringContainsString('Case Study 1', $html);
        $this->assertStringContainsString('Case Study 4', $html);
        $this->assertStringNotContainsString('Case Study 5', $html);
        $this->assertStringNotContainsString('Case Study 6', $html);
    }

    public function test_cv_view_wraps_contact_details_in_links(): void
    {
        $profile = Profile::current();
        $profile->update([
            'email' => 'jane@example.com',
            'linkedin_url' => 'https://linkedin.com/in/jane',
            'github_url' => 'https://github.com/jane',
        ]);
        $skills = Skill::query()->get()->groupBy('category');

        $html = view('cv', ['profile' => $profile, 'skills' => $skills, 'projects' => collect()])->render();

        $this->assertStringContainsString('<a href="mailto:jane@example.com">', $html);
        $this->assertStringContainsString('<a href="https://linkedin.com/in/jane">', $html);
        $this->assertStringContainsString('<a href="https://github.com/jane">', $html);
    }

    public function test_cv_view_renders_each_project_tag_as_its_own_span(): void
    {
        $profile = Profile::current();
        $skills = Skill::query()->get()->groupBy('category');

        $projects = collect([
            (object) [
                'name' => 'Tagged Project',
                'year' => '2024',
                'client_name' => null,
                'description' => null,
                'tag_list' => ['Laravel', 'Vue', 'Tailwind'],
            ],
        ]);

        $html = view('cv', ['profile' => $profile, 'skills' => $skills, 'projects' => $projects])->render();

        $this->assertStringContainsString('<span>Laravel</span>', $html);
        $this->assertStringContainsString('<span>Vue</span>', $html);
        $this->assertStringContainsString('<span>Tailwind</span>', $html);
    }

    public function test_cv_view_shows_availability_box_when_profile_is_available(): void
    {
        $profile = Profile::current();
        $profile->update(['available' => true]);
        $skills = Skill::query()->get()->groupBy('category');

        $html = view('cv', ['profile' => $profile, 'skills' => $skills, 'projects' => collect()])->render();

        $this->assertStringContainsString('<div class="availability-box">', $html);
        $this->assertStringContainsString('Currently available', $html);
        $this->assertStringNotContainsString('<div class="availability-line">', $html);
    }

    public function test_cv_view_shows_availability_line_when_profile_is_not_available(): void
    {
        $profile = Profile::current();
        $profile->update(['available' => false]);
        $skills = Skill::query()->get()->groupBy('category');

        $html = view('cv', ['profile' => $profile, 'skills' => $skills, 'projects' => collect()])->render();

        $this->assertStringContainsString('<div class="availability-line">', $html);
        $this->assertStringContainsString('Currently booked', $html);
        $this->assertStringNotContainsString('<div class="availability-box">', $html);
    }

    public function test_cv_view_omits_kvk_number_when_blank(): void
    {
        $profile = Profile::current();
        $profile->update(['kvk_number' => null]);
        $skills = Skill::query()->get()->groupBy('category');

        $html = view('cv', ['profile' => $profile, 'skills' => $skills, 'projects' => collect()])->render();

        $this->assertStringNotContainsString('KVK', $html);
    }

    public function test_cv_view_shows_kvk_number_when_set(): void
    {
        $profile = Profile::current();
        $profile->update(['kvk_number' => '12345678']);
        $skills = Skill::query()->get()->groupBy('category');

        $html = view('cv', ['profile' => $profile, 'skills' => $skills, 'projects' => collect()])->render();

        $this->assertStringContainsString('KVK 12345678', $html);
    }

    public function test_cv_view_omits_skills_section_when_there_are_no_skills(): void
    {
        $profile = Profile::current();

        $html = view('cv', ['profile' => $profile, 'skills' => collect(), 'projects' => collect()])->render();

        $this->assertStringNotContainsString('Skills', $html);
    }

    public function test_cv_view_renders_project_outcome_when_set(): void
    {
        $profile = Profile::current();
        $skills = Skill::query()->get()->groupBy('category');

        $projects = collect([
            (object) [
                'name' => 'Outcome Project',
                'year' => '2024',
                'client_name' => null,
                'description' => null,
                'tag_list' => [],
                'outcome' => 'Cut load times by 40%.',
            ],
        ]);

        $html = view('cv', ['profile' => $profile, 'skills' => $skills, 'projects' => $projects])->render();

        $this->assertStringContainsString('<div class="project-outcome">', $html);
        $this->assertStringContainsString('Result &mdash;', $html);
        $this->assertStringContainsString('Cut load times by 40%.', $html);
    }

    public function test_cv_view_omits_project_outcome_when_null(): void
    {
        $profile = Profile::current();
        $skills = Skill::query()->get()->groupBy('category');

        $projects = collect([
            (object) [
                'name' => 'No Outcome Project',
                'year' => '2024',
                'client_name' => null,
                'description' => null,
                'tag_list' => [],
                'outcome' => null,
            ],
        ]);

        $html = view('cv', ['profile' => $profile, 'skills' => $skills, 'projects' => $projects])->render();

        $this->assertStringNotContainsString('<div class="project-outcome">', $html);
    }

    public function test_cv_view_omits_project_outcome_when_key_is_missing(): void
    {
        $profile = Profile::current();
        $skills = Skill::query()->get()->groupBy('category');

        $projects = collect([
            (object) [
                'name' => 'Missing Outcome Key Project',
                'year' => '2024',
                'client_name' => null,
                'description' => null,
                'tag_list' => [],
            ],
        ]);

        $html = view('cv', ['profile' => $profile, 'skills' => $skills, 'projects' => $projects])->render();

        $this->assertStringNotContainsString('<div class="project-outcome">', $html);
    }

    public function test_cv_view_renders_project_github_link_when_set(): void
    {
        $profile = Profile::current();
        $skills = Skill::query()->get()->groupBy('category');

        $projects = collect([
            (object) [
                'name' => 'GitHub Project',
                'year' => '2024',
                'client_name' => null,
                'description' => null,
                'tag_list' => [],
                'github_url' => 'https://github.com/example/repo',
            ],
        ]);

        $html = view('cv', ['profile' => $profile, 'skills' => $skills, 'projects' => $projects])->render();

        $this->assertStringContainsString('<a href="https://github.com/example/repo">GitHub</a>', $html);
    }

    public function test_cv_view_omits_project_github_link_when_null(): void
    {
        $profile = Profile::current();
        $skills = Skill::query()->get()->groupBy('category');

        $projects = collect([
            (object) [
                'name' => 'No GitHub Project',
                'year' => '2024',
                'client_name' => null,
                'description' => null,
                'tag_list' => [],
                'github_url' => null,
            ],
        ]);

        $html = view('cv', ['profile' => $profile, 'skills' => $skills, 'projects' => $projects])->render();

        $this->assertStringNotContainsString('>GitHub</a>', $html);
    }

    public function test_cv_view_omits_project_github_link_when_key_is_missing(): void
    {
        $profile = Profile::current();
        $skills = Skill::query()->get()->groupBy('category');

        $projects = collect([
            (object) [
                'name' => 'Missing GitHub Key Project',
                'year' => '2024',
                'client_name' => null,
                'description' => null,
                'tag_list' => [],
            ],
        ]);

        $html = view('cv', ['profile' => $profile, 'skills' => $skills, 'projects' => $projects])->render();

        $this->assertStringNotContainsString('>GitHub</a>', $html);
    }

    public function test_cv_view_shows_see_all_work_cta_linking_to_work_index(): void
    {
        $profile = Profile::current();
        $skills = Skill::query()->get()->groupBy('category');

        $projects = collect([
            (object) [
                'name' => 'Case Study Project',
                'year' => '2024',
                'client_name' => null,
                'description' => null,
                'tag_list' => [],
            ],
        ]);

        $html = view('cv', ['profile' => $profile, 'skills' => $skills, 'projects' => $projects])->render();

        $this->assertStringContainsString('See all work &rarr;', $html);
        $this->assertStringContainsString('<a class="section-cta" href="'.route('work.index').'">', $html);
    }

    public function test_cv_route_renders_successfully_with_real_project_outcome_and_github_url(): void
    {
        Project::create([
            'name' => 'Real Project',
            'sort_order' => 0,
            'published' => true,
            'outcome' => 'Increased conversion by 25%.',
            'github_url' => 'https://github.com/example/real-project',
        ]);

        $response = $this->get('/cv.pdf');

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }
}
