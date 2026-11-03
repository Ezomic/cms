<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\Skill;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PricingAndAiCopyTest extends TestCase
{
    use RefreshDatabase;

    private const PRICING_PHRASES = [
        'op aanvraag', 'on request', 'tarief', 'dagtarief', 'vaste prijs', 'day rate', 'fixed price',
        'fixed-price', 'pricerange',
    ];

    /**
     * @return array<string, array{0: string}>
     */
    public static function publicPages(): array
    {
        return [
            'home en' => ['/'],
            'home nl' => ['/nl'],
            'enschede en' => ['/laravel-developer-enschede'],
            'enschede nl' => ['/nl/laravel-ontwikkelaar-enschede'],
            'remote en' => ['/laravel-developer-remote'],
            'remote nl' => ['/nl/laravel-ontwikkelaar-remote'],
            'go en' => ['/freelance-golang-developer'],
            'go nl' => ['/nl/freelance-go-ontwikkelaar'],
            'python en' => ['/freelance-python-developer'],
            'python nl' => ['/nl/freelance-python-ontwikkelaar'],
            'dotnet en' => ['/freelance-csharp-dotnet-developer'],
            'dotnet nl' => ['/nl/freelance-csharp-dotnet-ontwikkelaar'],
        ];
    }

    #[DataProvider('publicPages')]
    public function test_public_pages_carry_no_pricing_language(string $path): void
    {
        $html = strtolower((string) $this->get($path)->assertOk()->getContent());

        foreach (self::PRICING_PHRASES as $phrase) {
            $this->assertStringNotContainsString($phrase, $html, "{$path} still says '{$phrase}'");
        }
    }

    public function test_the_cv_carries_no_pricing_language_in_either_language(): void
    {
        foreach (['en', 'nl'] as $locale) {
            app()->setLocale($locale);

            foreach ([true, false] as $available) {
                Profile::current()->update(['available' => $available, 'availability_from' => $available ? null : '1 January 2027']);

                $html = strtolower(view('cv', [
                    'profile' => Profile::current()->refresh(),
                    'skills' => collect(),
                    'projects' => collect(),
                ])->render());

                foreach (self::PRICING_PHRASES as $phrase) {
                    $this->assertStringNotContainsString($phrase, $html, "{$locale} cv still says '{$phrase}'");
                }
            }
        }
    }

    public function test_the_budget_field_stays_and_defaults_to_i_do_not_know_yet(): void
    {
        $this->get('/')
            ->assertSeeInOrder(['id="contact-budget"', '<option value="" ', '>I do not know yet</option>'], false);

        $this->get('/nl')
            ->assertSee('Geschat budget')
            ->assertSeeInOrder(['id="contact-budget"', '<option value="" ', '>Ik weet het nog niet</option>', '€5k–€20k'], false);
    }

    public function test_the_scoping_step_promises_a_working_prototype_within_days(): void
    {
        $this->get('/')->assertSee('working prototype', false)->assertSee('within days', false);
        $this->get('/nl')->assertSee('werkend prototype', false)->assertSee('binnen enkele dagen', false);
    }

    public function test_the_home_page_has_a_proof_of_concept_card_without_a_price(): void
    {
        $this->get('/nl')
            ->assertSee('<h3>Proof of concept</h3>', false)
            ->assertSee('MVP laten bouwen')
            ->assertSee('Prototype webapplicatie');

        $this->get('/')->assertSee('<h3>Proof of concept</h3>', false);
    }

    #[DataProvider('aiPages')]
    public function test_ai_is_described_as_a_working_method_without_naming_a_tool(string $path, string $expected): void
    {
        $html = (string) $this->get($path)->assertOk()->getContent();

        $this->assertStringContainsString($expected, $html);
        $this->assertStringContainsString('/work/tracker', $html);
        $this->assertStringNotContainsStringIgnoringCase('claude', $html);
        $this->assertStringNotContainsStringIgnoringCase('openai', $html);
        $this->assertStringNotContainsStringIgnoringCase('copilot', $html);
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function aiPages(): array
    {
        return [
            'home nl' => ['/nl', 'AI-assistenten'],
            'enschede nl' => ['/nl/laravel-ontwikkelaar-enschede', 'AI-assistenten'],
            'remote nl' => ['/nl/laravel-ontwikkelaar-remote', 'AI-assistenten'],
            'home en' => ['/', 'AI assistants'],
            'enschede en' => ['/laravel-developer-enschede', 'AI assistants'],
            'remote en' => ['/laravel-developer-remote', 'AI assistants'],
            'go nl' => ['/nl/freelance-go-ontwikkelaar', 'AI-assistenten'],
            'python nl' => ['/nl/freelance-python-ontwikkelaar', 'AI-assistenten'],
            'dotnet nl' => ['/nl/freelance-csharp-dotnet-ontwikkelaar', 'AI-assistenten'],
            'go en' => ['/freelance-golang-developer', 'AI assistants'],
            'python en' => ['/freelance-python-developer', 'AI assistants'],
            'dotnet en' => ['/freelance-csharp-dotnet-developer', 'AI assistants'],
        ];
    }

    public function test_the_dutch_pages_use_the_poc_keywords(): void
    {
        $this->get('/nl')->assertSee('AI-ondersteunde softwareontwikkeling');
        $this->get('/nl/laravel-ontwikkelaar-enschede')->assertSee('proof of concept laten bouwen');
        $this->get('/nl/laravel-ontwikkelaar-remote')->assertSee('proof of concept laten bouwen');
    }

    public function test_the_private_school_app_is_only_mentioned_generically(): void
    {
        foreach (['/nl', '/nl/laravel-ontwikkelaar-enschede', '/nl/laravel-ontwikkelaar-remote'] as $path) {
            $html = strtolower((string) $this->get($path)->getContent());

            $this->assertStringContainsString('school', $html);
            $this->assertStringNotContainsString('ob-weekregistratie', $html);
            $this->assertStringNotContainsString('obw', $html);
        }

        $this->get('/sitemap.xml')->assertDontSee('weekregistratie');
    }

    private function runHeroMigration(string $method): void
    {
        $migration = require database_path('migrations/2026_11_02_100200_make_home_copy_stack_neutral.php');
        $this->assertInstanceOf(Migration::class, $migration);
        $migration->{$method}();
    }

    private const LIVE_SUBTEXT_NL = 'Freelance Laravel-ontwikkelaar uit Enschede met 9+ jaar ervaring, werkzaam in heel Twente en op afstand. Ik ben gespecialiseerd in Laravel en Vue.js en bouw betrouwbare, onderhoudbare webproducten voor klanten van mkb tot grote ondernemingen.';

    private const LIVE_TITLE_NL = 'Laravel-ontwikkelaar Enschede en remote | Robbin Thijssen';

    public function test_the_home_migration_makes_the_live_copy_stack_neutral_and_spares_edited_values(): void
    {
        Profile::current()->update([
            'hero_subtext_nl' => self::LIVE_SUBTEXT_NL,
            'meta_title_nl' => self::LIVE_TITLE_NL,
            'tagline_nl' => 'Freelance Laravel-ontwikkelaar',
            'hero_subtext' => 'Edited by hand',
        ]);

        $this->runHeroMigration('up');

        $profile = Profile::current()->refresh();
        $this->assertSame('Software-ontwikkelaar in Enschede, van idee naar werkend prototype | Robbin Thijssen', $profile->meta_title_nl);
        $this->assertSame('Freelance software-ontwikkelaar', $profile->tagline_nl);
        $this->assertStringContainsString('werkend prototype', (string) $profile->hero_subtext_nl);
        $this->assertStringNotContainsString('Laravel', (string) $profile->hero_subtext_nl);
        $this->assertSame('Edited by hand', $profile->hero_subtext);
    }

    public function test_the_home_migration_reverses_cleanly(): void
    {
        Profile::current()->update(['hero_subtext_nl' => self::LIVE_SUBTEXT_NL, 'meta_title_nl' => self::LIVE_TITLE_NL]);

        $this->runHeroMigration('up');
        $this->runHeroMigration('down');

        $profile = Profile::current()->refresh();
        $this->assertSame(self::LIVE_SUBTEXT_NL, $profile->hero_subtext_nl);
        $this->assertSame(self::LIVE_TITLE_NL, $profile->meta_title_nl);
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function clientFacingFiles(): array
    {
        return ['site' => ['site'], 'landing' => ['landing'], 'remote' => ['remote']];
    }

    #[DataProvider('clientFacingFiles')]
    public function test_services_copy_is_plain_language_without_framework_names(string $file): void
    {
        $jargon = '/\b(Laravel|Vue|Blade|APIs?|API\'s|webhooks?|SaaS|CRM|ERP|refactor\w*|event-driven)\b/i';

        foreach (['en', 'nl'] as $locale) {
            $all = require lang_path("{$locale}/{$file}.php");
            $lines = [];

            if ($file === 'site') {
                foreach ([1, 2, 3, 4] as $n) {
                    $lines[] = $all["service_{$n}_title"];
                    $lines[] = $all["service_{$n}_body"];
                    array_push($lines, ...$all["service_{$n}_items"]);
                }
                $lines[] = $all['ai_p1'];
                $lines[] = $all['ai_p2'];
            } else {
                foreach ($all['build_items'] as $item) {
                    $lines[] = $item['body'];
                    if (! str_starts_with($item['title'], 'Laravel')) {
                        $lines[] = $item['title'];
                    }
                }
            }

            foreach ($lines as $line) {
                $this->assertDoesNotMatchRegularExpression($jargon, $line, "{$locale}/{$file}: {$line}");
            }
        }
    }

    private function runSkillsMigration(string $method): void
    {
        $migration = require database_path('migrations/2026_11_02_100300_add_python_and_go_backend_skills.php');
        $this->assertInstanceOf(Migration::class, $migration);
        $migration->{$method}();
    }

    private function seedBackend(): void
    {
        foreach (['PHP 8.x', 'Laravel', 'REST APIs', 'MySQL'] as $i => $name) {
            Skill::create(['category' => 'Backend', 'name' => $name, 'sort_order' => $i]);
        }
    }

    public function test_the_skills_migration_adds_python_and_go_after_the_existing_backend_skills(): void
    {
        $this->seedBackend();

        $this->runSkillsMigration('up');
        $this->runSkillsMigration('up');

        $names = Skill::where('category', 'Backend')->orderBy('sort_order')->pluck('name')->all();
        $this->assertSame(['PHP 8.x', 'Laravel', 'REST APIs', 'MySQL', 'Python', 'Go'], $names);
    }

    public function test_the_skills_migration_does_nothing_without_a_backend_category(): void
    {
        Skill::create(['category' => 'Frontend', 'name' => 'Vue', 'sort_order' => 0]);

        $this->runSkillsMigration('up');

        $this->assertSame(1, Skill::count());
    }

    public function test_the_skills_migration_reverses_cleanly(): void
    {
        $this->seedBackend();

        $this->runSkillsMigration('up');
        $this->runSkillsMigration('down');

        $this->assertSame(4, Skill::withTrashed()->count());
    }
}
