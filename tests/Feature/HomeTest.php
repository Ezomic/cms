<?php

namespace Tests\Feature;

use App\Models\Profile;
use App\Models\Project;
use App\Models\Skill;
use App\Models\Testimonial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_loads_successfully(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_home_page_shows_profile_and_content(): void
    {
        Profile::current()->update(['name' => 'Jane Developer', 'tagline' => 'Backend Engineer']);
        Skill::create(['category' => 'Backend', 'name' => 'Laravel', 'sort_order' => 0]);
        Project::create(['name' => 'Acme Rebuild', 'sort_order' => 0]);
        Testimonial::create(['quote' => 'Great to work with.', 'author_name' => 'A Client', 'featured' => true]);

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Jane Developer');
        $response->assertSee('Laravel');
        $response->assertSee('Acme Rebuild');
        $response->assertSee('Great to work with.');
    }
}
