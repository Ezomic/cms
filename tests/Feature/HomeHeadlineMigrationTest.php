<?php

namespace Tests\Feature;

use App\Models\Profile;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeHeadlineMigrationTest extends TestCase
{
    use RefreshDatabase;

    private function migration(): Migration
    {
        $migration = require database_path('migrations/2026_11_03_100000_make_home_headline_outcome_led.php');
        $this->assertInstanceOf(Migration::class, $migration);

        return $migration;
    }

    private function liveProfile(): Profile
    {
        $profile = Profile::current();
        $profile->update([
            'hero_headline' => 'Building web applications that work, end to end.',
            'hero_headline_nl' => 'Webapplicaties die werken, van begin tot eind.',
        ]);

        return $profile;
    }

    public function test_the_headline_becomes_outcome_led_in_both_languages(): void
    {
        $profile = $this->liveProfile();

        $this->migration()->up();
        $profile->refresh();

        $this->assertSame('From idea to working software.', $profile->hero_headline);
        $this->assertSame('Van idee naar werkende software.', $profile->hero_headline_nl);
    }

    public function test_it_leaves_a_headline_alone_that_was_edited_since(): void
    {
        $profile = $this->liveProfile();
        $profile->update(['hero_headline_nl' => 'Mijn eigen kop.']);

        $this->migration()->up();
        $profile->refresh();

        $this->assertSame('From idea to working software.', $profile->hero_headline);
        $this->assertSame('Mijn eigen kop.', $profile->hero_headline_nl);
    }

    public function test_down_restores_the_previous_headline(): void
    {
        $profile = $this->liveProfile();

        $this->migration()->up();
        $this->migration()->down();
        $profile->refresh();

        $this->assertSame('Building web applications that work, end to end.', $profile->hero_headline);
        $this->assertSame('Webapplicaties die werken, van begin tot eind.', $profile->hero_headline_nl);
    }

    public function test_it_does_nothing_without_a_profile(): void
    {
        $this->migration()->up();

        $this->assertSame(0, Profile::count());
    }
}
