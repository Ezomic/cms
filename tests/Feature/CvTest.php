<?php

namespace Tests\Feature;

use App\Models\Profile;
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
}
