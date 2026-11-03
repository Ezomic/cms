<?php

namespace Tests\Feature;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AudienceLandingPagesTest extends TestCase
{
    use RefreshDatabase;

    private const PAGES = [
        'agencies' => ['/software-developer-for-agencies', '/nl/software-ontwikkelaar-voor-bureaus'],
        'founders' => ['/mvp-developer-for-startups', '/nl/mvp-ontwikkelaar-voor-startups'],
        'smallbusiness' => ['/custom-software-for-small-business', '/nl/maatwerksoftware-voor-mkb'],
    ];

    public function test_the_founders_page_names_tracker_with_its_repository_and_case_study(): void
    {
        Project::create(['name' => 'Tracker', 'slug' => 'tracker', 'published' => true, 'sort_order' => 0]);

        $this->get(self::PAGES['founders'][0])
            ->assertSee('href="https://github.com/Ezomic/tracker"', false)
            ->assertSee('href="'.route('project.show', 'tracker').'"', false);
        $this->get(self::PAGES['founders'][1])
            ->assertSee('href="https://github.com/Ezomic/tracker"', false)
            ->assertSee('href="'.route('nl.project.show', 'tracker').'"', false);
    }

    public function test_the_school_app_appears_generically_without_a_link_or_name(): void
    {
        // Application::setLocale() writes app.locale, so English goes before Dutch.
        foreach (['en' => 0, 'nl' => 1] as $locale => $index) {
            foreach (['founders', 'smallbusiness'] as $key) {
                $this->get(self::PAGES[$key][$index])->assertSee('school')->assertSee(e(trans('site.landing_private_note', [], $locale)));
            }
        }
    }

    public function test_audience_pages_use_plain_language(): void
    {
        $jargon = '/\b(Laravel|Vue|Blade|APIs?|API\'s|webhooks?|SaaS|CRM|ERP|backend|frontend|stack|framework|refactor\w*|repository|repositories|deploy\w*|CI\/CD)\b/i';

        foreach (array_keys(self::PAGES) as $key) {
            foreach (['en', 'nl'] as $locale) {
                $copy = trans("pages/{$key}", [], $locale);
                $lines = [$copy['hero_headline'], $copy['hero_lead']];

                foreach (['build_items', 'steps_items'] as $list) {
                    foreach ($copy[$list] as $item) {
                        array_push($lines, $item['title'], $item['body']);
                    }
                }

                foreach ($lines as $line) {
                    $this->assertDoesNotMatchRegularExpression($jargon, $line, "{$locale} {$key}: {$line}");
                }
            }
        }
    }

    public function test_each_audience_page_leads_with_its_outcome(): void
    {
        $this->get('/nl/mvp-ontwikkelaar-voor-startups')->assertSee('weken');
        $this->get('/nl/software-ontwikkelaar-voor-bureaus')->assertSee('capaciteit');
        $this->get('/nl/maatwerksoftware-voor-mkb')->assertSee('overtyp');
    }

    public function test_case_studies_only_appear_as_proof_when_they_are_published(): void
    {
        Project::create(['name' => 'BlueVi', 'slug' => 'bluevi', 'published' => true, 'sort_order' => 0]);
        Project::create(['name' => 'Bioscape', 'slug' => 'bioscape', 'published' => false, 'sort_order' => 1]);

        $this->get(self::PAGES['agencies'][0])
            ->assertSee('href="'.route('project.show', 'bluevi').'"', false)
            ->assertSee('<h3>BlueVi</h3>', false)
            ->assertDontSee('<h3>Bioscape</h3>', false)
            ->assertDontSee('<h3>Data-Exchange Portal</h3>', false);
    }

    public function test_the_proof_section_is_left_out_when_no_case_study_is_published(): void
    {
        $this->get(self::PAGES['agencies'][0])->assertOk()->assertDontSee('class="proof-grid"', false);
    }

    public function test_lastmod_follows_the_newest_proof_project(): void
    {
        $project = Project::create(['name' => 'Tracker', 'slug' => 'tracker', 'published' => true, 'sort_order' => 0]);
        DB::table('projects')->where('id', $project->id)->update(['updated_at' => '2027-03-01 10:00:00']);

        $xml = (string) $this->get('/sitemap.xml')->getContent();

        foreach (['founders' => '2027-03-01', 'smallbusiness' => '2027-03-01', 'agencies' => '2026-11-03', 'go' => '2026-11-03'] as $key => $expected) {
            $this->assertSame(1, preg_match('#<loc>'.preg_quote(route("landing.{$key}"), '#').'</loc>\s*<lastmod>(\d{4}-\d{2}-\d{2})#', $xml, $match));
            $this->assertSame($expected, $match[1], $key);
        }
    }
}
