<?php

namespace Tests\Feature;

use App\Models\Profile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileCurrentTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_the_profile_row_with_all_columns_hydrated(): void
    {
        $this->assertDatabaseCount('profile', 0);

        $profile = Profile::current();

        $this->assertSame(1, $profile->id);
        $this->assertArrayHasKey('name', $profile->toArray());
        $this->assertArrayHasKey('tagline', $profile->toArray());
    }

    public function test_subsequent_calls_return_the_same_fully_hydrated_row(): void
    {
        Profile::current();

        $profile = Profile::current();

        $this->assertSame(1, $profile->id);
        $this->assertArrayHasKey('name', $profile->toArray());
    }

    public function test_it_reflects_updates_made_after_creation(): void
    {
        Profile::current();

        Profile::current()->forceFill(['name' => 'Updated Name'])->save();

        $this->assertSame('Updated Name', Profile::current()->name);
    }
}
