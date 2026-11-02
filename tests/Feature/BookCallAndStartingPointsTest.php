<?php

namespace Tests\Feature;

use App\Models\ContactSubmission;
use App\Models\Profile;
use App\Support\ContactFormToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class BookCallAndStartingPointsTest extends TestCase
{
    use RefreshDatabase;

    private const BOOKING_URL = 'https://chronos.example.test/book/robbin';

    /**
     * @return array<string, array{0: string, 1: string, 2: string}>
     */
    public static function pages(): array
    {
        $nl = ['Plan een kennismaking van 20 minuten', 'Ik reageer binnen één werkdag.'];
        $en = ['Book a 20 minute intro call', 'I reply within one working day.'];

        return [
            'home nl' => ['/nl', ...$nl],
            'enschede nl' => ['/nl/laravel-ontwikkelaar-enschede', ...$nl],
            'remote nl' => ['/nl/laravel-ontwikkelaar-remote', ...$nl],
            'home en' => ['/', ...$en],
            'enschede en' => ['/laravel-developer-enschede', ...$en],
            'remote en' => ['/laravel-developer-remote', ...$en],
        ];
    }

    #[DataProvider('pages')]
    public function test_without_a_booking_url_the_button_points_at_the_contact_section(string $path, string $label, string $promise): void
    {
        config(['site.booking_url' => null]);

        $html = (string) $this->get($path)->assertOk()->assertSee($label, false)->assertSee($promise, false)->getContent();

        $this->assertMatchesRegularExpression('#<a class="btn pri book-call" href="[^"]*\#contact"[^>]*>\s*'.preg_quote($label, '#').'#', $html);
        $this->assertStringNotContainsString('chronos', $html);
    }

    #[DataProvider('pages')]
    public function test_with_a_booking_url_the_button_links_to_it(string $path, string $label, string $promise): void
    {
        config(['site.booking_url' => self::BOOKING_URL]);

        $html = (string) $this->get($path)->assertOk()->getContent();

        $this->assertMatchesRegularExpression('#<a class="btn pri book-call" href="'.preg_quote(self::BOOKING_URL, '#').'"[^>]*>\s*'.preg_quote($label, '#').'#', $html);
        $this->assertStringContainsString('rel="noopener noreferrer"', $html);
    }

    public function test_the_booking_url_is_a_single_config_value_that_defaults_to_null(): void
    {
        $this->assertNull(config('site.booking_url'));
    }

    public function test_the_home_page_shows_the_button_in_the_hero_and_beside_the_form(): void
    {
        $html = (string) $this->get('/nl')->getContent();

        $this->assertSame(2, substr_count($html, 'class="btn pri book-call"'));
    }

    public function test_the_form_only_requires_name_email_and_message(): void
    {
        $this->assertStringContainsString('Estimated budget (optional)', (string) $this->get('/')->getContent());

        $html = (string) $this->get('/nl')->getContent();

        $this->assertMatchesRegularExpression('#id="contact-name"[^>]*\srequired#', $html);
        $this->assertMatchesRegularExpression('#id="contact-email"[^>]*\srequired#', $html);
        $this->assertMatchesRegularExpression('#id="contact-message"[^>]*\srequired#', $html);
        $this->assertDoesNotMatchRegularExpression('#id="contact-company"[^>]*\srequired#', $html);
        $this->assertDoesNotMatchRegularExpression('#id="contact-budget"[^>]*\srequired#', $html);
        $this->assertStringContainsString('Bedrijf (optioneel)', $html);
        $this->assertStringContainsString('Geschat budget (optioneel)', $html);

    }

    public function test_a_submission_with_only_name_email_and_message_is_saved_with_a_null_budget(): void
    {
        Profile::current()->update(['email' => 'owner@example.com']);
        $token = ContactFormToken::issue();
        $this->travel(5)->seconds();

        $this->post(route('contact.store'), [
            'name' => 'Jane Client',
            'email' => 'jane@example.com',
            'message' => 'Hello',
            'company' => '',
            'budget' => '',
            'form_token' => $token,
        ])->assertSessionHasNoErrors()->assertSessionHas('status');

        $submission = ContactSubmission::query()->firstOrFail();
        $this->assertNull($submission->budget);
        $this->assertNull($submission->company);
    }

    public function test_the_home_page_names_both_starting_points_in_plain_language(): void
    {
        $this->get('/')
            ->assertSee('Where to start', false)
            ->assertSee('<h3>Idea session</h3>', false)
            ->assertSee('<h3>Stuck project?</h3>', false)
            ->assertSee('half a day', false)
            ->assertSee('an honest assessment', false);

        $this->get('/nl')
            ->assertSee('Zo begin je', false)
            ->assertSee('<h3>Idee-sessie</h3>', false)
            ->assertSee('<h3>Vastgelopen project?</h3>', false)
            ->assertSee('Een dagdeel', false)
            ->assertSee('een eerlijk oordeel', false);
    }

    public function test_the_starting_points_do_not_join_the_four_service_cards(): void
    {
        $html = (string) $this->get('/nl')->getContent();

        $this->assertSame(4, substr_count($html, '<div class="sc">'));
        $this->assertSame(2, substr_count($html, '<div class="sc start">'));
        $this->assertStringContainsString('id="start"', $html);
    }

    #[DataProvider('homePages')]
    public function test_the_starting_points_name_no_tool_and_no_school_app(string $path): void
    {
        $html = (string) $this->get($path)->assertOk()->getContent();
        $start = strstr((string) strstr($html, 'id="start"'), '</section>', true);

        foreach (['claude', 'openai', 'copilot', 'chatgpt', 'weekregistratie', '€'] as $word) {
            $this->assertStringNotContainsStringIgnoringCase($word, $start);
        }
    }

    /**
     * @return array<string, array{0: string}>
     */
    public static function homePages(): array
    {
        return ['nl' => ['/nl'], 'en' => ['/']];
    }

    public function test_the_person_offer_catalog_lists_the_two_starting_points(): void
    {
        preg_match('#<script type="application/ld\+json">(.*?)</script>#s', (string) $this->get('/')->getContent(), $match);
        $person = json_decode($match[1], true, flags: JSON_THROW_ON_ERROR);
        $this->assertIsArray($person);

        $names = array_column(array_column($person['hasOfferCatalog']['itemListElement'], 'itemOffered'), 'name');
        $this->assertCount(5, $names);
        $this->assertContains('Idea session: scoping workshop with plan and prototype', $names);
        $this->assertContains('Rescue and assessment of a stuck software project', $names);
    }
}
