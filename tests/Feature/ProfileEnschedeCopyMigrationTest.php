<?php

namespace Tests\Feature;

use App\Models\Profile;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileEnschedeCopyMigrationTest extends TestCase
{
    use RefreshDatabase;

    private function migration(): Migration
    {
        $migration = require database_path('migrations/2026_10_13_100000_update_profile_copy_for_laravel_developer_enschede.php');
        $this->assertInstanceOf(Migration::class, $migration);

        return $migration;
    }

    private function liveProfile(): Profile
    {
        $profile = Profile::current();
        $profile->update([
            'tagline' => 'Full-Stack Web Developer',
            'meta_title' => 'Robbin Thijssen, full-stack web developer in Enschede',
            'meta_description' => 'Full-stack web developer in Enschede with 9+ years of experience, building reliable Laravel and Vue.js web products for SMEs and enterprises.',
            'hero_subtext' => 'Full-stack web developer based in Enschede with 9+ years of experience. I specialise in Laravel and Vue.js, building reliable, maintainable web products for clients from SMEs to large enterprises.',
            'tagline_nl' => 'Full-stack webdeveloper',
            'meta_title_nl' => 'Robbin Thijssen, full-stack webdeveloper in Enschede',
            'meta_description_nl' => 'Full-stack webdeveloper in Enschede met 9+ jaar ervaring, bouwt betrouwbare Laravel- en Vue.js-webproducten voor mkb en grote ondernemingen.',
            'hero_subtext_nl' => 'Full-stack webdeveloper uit Enschede met 9+ jaar ervaring. Ik ben gespecialiseerd in Laravel en Vue.js en bouw betrouwbare, onderhoudbare webproducten voor klanten van mkb tot grote ondernemingen.',
        ]);

        return $profile;
    }

    public function test_it_replaces_the_values_that_were_live_and_leaves_the_headline_alone(): void
    {
        $profile = $this->liveProfile();
        $profile->update(['hero_headline' => 'Building web applications that work, end to end.']);

        $this->migration()->up();
        $profile->refresh();

        $this->assertSame('Laravel developer in Enschede | Robbin Thijssen', $profile->meta_title);
        $this->assertSame('Laravel-ontwikkelaar in Enschede | Robbin Thijssen', $profile->meta_title_nl);
        $this->assertSame('Freelance Laravel Developer', $profile->tagline);
        $this->assertStringContainsString('Twente', (string) $profile->hero_subtext);
        $this->assertStringContainsString('Twente', (string) $profile->hero_subtext_nl);
        $this->assertStringContainsString('Freelance Laravel developer in Enschede', (string) $profile->meta_description);
        $this->assertSame('Building web applications that work, end to end.', $profile->hero_headline);
    }

    public function test_it_leaves_a_field_alone_that_was_edited_in_the_admin_since(): void
    {
        $profile = $this->liveProfile();
        $profile->update(['meta_title' => 'A title the owner wrote by hand']);

        $this->migration()->up();
        $profile->refresh();

        $this->assertSame('A title the owner wrote by hand', $profile->meta_title);
        $this->assertSame('Freelance Laravel Developer', $profile->tagline);
    }

    public function test_it_is_reversible_and_idempotent(): void
    {
        $profile = $this->liveProfile();
        $before = $profile->only(['tagline', 'meta_title', 'meta_description', 'hero_subtext', 'tagline_nl', 'meta_title_nl', 'meta_description_nl', 'hero_subtext_nl']);

        $this->migration()->up();
        $this->migration()->up();
        $this->migration()->down();

        $this->assertSame($before, $profile->refresh()->only(array_keys($before)));
    }

    public function test_the_home_page_serves_the_new_title_and_lead_after_the_migration(): void
    {
        $this->liveProfile();
        $this->migration()->up();

        $this->get('/')
            ->assertSee('<title>Laravel developer in Enschede | Robbin Thijssen</title>', false)
            ->assertSee('Freelance Laravel developer in Enschede with 9+ years of experience, working across Twente and remotely.');
    }
}
