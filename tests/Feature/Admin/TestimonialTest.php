<?php

namespace Tests\Feature\Admin;

use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TestimonialTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_testimonial_admin_routes(): void
    {
        $this->get('/admin/testimonials')->assertRedirect('/admin/login');
    }

    public function test_admin_can_create_a_testimonial(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/admin/testimonials', [
            'quote'       => 'Excellent work.',
            'author_name' => 'A Client',
            'author_role' => 'CEO, Acme',
            'featured'    => '1',
        ]);

        $response->assertRedirect('/admin/testimonials');
        $this->assertDatabaseHas('testimonials', ['quote' => 'Excellent work.', 'featured' => true]);
    }

    public function test_creating_a_testimonial_requires_a_quote(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/admin/testimonials', ['quote' => '']);

        $response->assertSessionHasErrors('quote');
    }

    public function test_admin_can_update_a_testimonial(): void
    {
        $user = User::factory()->create();
        $testimonial = Testimonial::create(['quote' => 'Old quote', 'featured' => false]);

        $response = $this->actingAs($user)->put("/admin/testimonials/{$testimonial->id}", [
            'quote'    => 'Updated quote',
            'featured' => '1',
        ]);

        $response->assertRedirect('/admin/testimonials');
        $this->assertDatabaseHas('testimonials', ['id' => $testimonial->id, 'quote' => 'Updated quote', 'featured' => true]);
    }

    public function test_admin_can_delete_a_testimonial(): void
    {
        $user = User::factory()->create();
        $testimonial = Testimonial::create(['quote' => 'To delete', 'featured' => false]);

        $response = $this->actingAs($user)->delete("/admin/testimonials/{$testimonial->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('testimonials', ['id' => $testimonial->id]);
    }
}
