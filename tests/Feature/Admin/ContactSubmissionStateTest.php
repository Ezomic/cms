<?php

namespace Tests\Feature\Admin;

use App\Models\ContactSubmission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class ContactSubmissionStateTest extends TestCase
{
    use RefreshDatabase;

    private function submission(string $name = 'Jane'): ContactSubmission
    {
        return ContactSubmission::create([
            'name' => $name,
            'email' => strtolower($name).'@example.com',
            'message' => 'Hello there.',
        ]);
    }

    private function admin(): User
    {
        return User::factory()->create();
    }

    public function test_marking_replied_sets_the_timestamp(): void
    {
        $submission = $this->submission();

        $this->actingAs($this->admin())
            ->post("/admin/contact-submissions/{$submission->id}/replied")
            ->assertRedirect();

        $this->assertNotNull($submission->fresh()->replied_at);
    }

    public function test_replying_also_marks_it_read(): void
    {
        $submission = $this->submission();
        $this->assertNull($submission->read_at);

        $this->actingAs($this->admin())->post("/admin/contact-submissions/{$submission->id}/replied");

        $this->assertNotNull($submission->fresh()->read_at);
    }

    public function test_marking_replied_does_not_move_an_existing_read_timestamp(): void
    {
        $submission = $this->submission();
        $readAt = now()->subDays(3);
        $submission->forceFill(['read_at' => $readAt])->save();

        $this->actingAs($this->admin())->post("/admin/contact-submissions/{$submission->id}/replied");

        $this->assertSame($readAt->toDateTimeString(), $submission->fresh()->read_at->toDateTimeString());
    }

    public function test_unreplying_clears_the_timestamp(): void
    {
        $submission = $this->submission();
        $submission->forceFill(['replied_at' => now()])->save();

        $this->actingAs($this->admin())->post("/admin/contact-submissions/{$submission->id}/unreplied");

        $this->assertNull($submission->fresh()->replied_at);
    }

    public function test_a_note_saves_and_survives_a_reload(): void
    {
        $submission = $this->submission();

        $this->actingAs($this->admin())
            ->post("/admin/contact-submissions/{$submission->id}/note", ['note' => 'Quoted 3 days, waiting.'])
            ->assertRedirect();

        $this->assertSame('Quoted 3 days, waiting.', $submission->fresh()->note);

        $this->actingAs($this->admin())->get('/admin/contact-submissions')->assertInertia(
            fn (Assert $page) => $page->where('submissions.data.0.note', 'Quoted 3 days, waiting.')->etc()
        );
    }

    public function test_an_empty_note_is_stored_as_null(): void
    {
        $submission = $this->submission();
        $submission->forceFill(['note' => 'Something'])->save();

        $this->actingAs($this->admin())->post("/admin/contact-submissions/{$submission->id}/note", ['note' => '   ']);

        $this->assertNull($submission->fresh()->note);
    }

    public function test_the_note_is_never_rendered_on_a_public_page(): void
    {
        $submission = $this->submission();
        $submission->forceFill(['note' => 'Internal only, do not leak'])->save();

        foreach (['/', '/work', '/docs', '/nl'] as $path) {
            $this->get($path)->assertDontSee('Internal only, do not leak');
        }
    }

    public function test_filtering_by_state(): void
    {
        $unread = $this->submission('Unread');
        $read = $this->submission('Read');
        $read->forceFill(['read_at' => now()])->save();
        $replied = $this->submission('Replied');
        $replied->forceFill(['read_at' => now(), 'replied_at' => now()])->save();

        $admin = $this->admin();

        $this->actingAs($admin)->get('/admin/contact-submissions?state=unread')->assertInertia(
            fn (Assert $p) => $p->count('submissions.data', 1)->where('submissions.data.0.name', 'Unread')->etc()
        );

        $this->actingAs($admin)->get('/admin/contact-submissions?state=read')->assertInertia(
            fn (Assert $p) => $p->count('submissions.data', 1)->where('submissions.data.0.name', 'Read')->etc()
        );

        $this->actingAs($admin)->get('/admin/contact-submissions?state=replied')->assertInertia(
            fn (Assert $p) => $p->count('submissions.data', 1)->where('submissions.data.0.name', 'Replied')->etc()
        );

        $this->actingAs($admin)->get('/admin/contact-submissions')->assertInertia(
            fn (Assert $p) => $p->count('submissions.data', 3)->etc()
        );
    }

    public function test_an_unknown_state_falls_back_to_all(): void
    {
        $this->submission();

        $this->actingAs($this->admin())->get('/admin/contact-submissions?state=nonsense')->assertInertia(
            fn (Assert $p) => $p->where('filters.state', 'all')->count('submissions.data', 1)->etc()
        );
    }

    /**
     * The sidebar badge counts what still needs opening. A replied message is
     * already read, so this must not start counting un-replied messages.
     */
    public function test_the_sidebar_unread_count_still_counts_only_unread(): void
    {
        $this->submission('One');
        $read = $this->submission('Two');
        $read->forceFill(['read_at' => now()])->save();

        $this->actingAs($this->admin())->get('/admin/contact-submissions')->assertInertia(
            fn (Assert $p) => $p->where('counts.unread', 1)->where('counts.read', 1)->where('counts.all', 2)->etc()
        );
    }

    public function test_the_state_filter_is_reflected_in_pagination_links(): void
    {
        foreach (range(1, 20) as $i) {
            $this->submission("Person{$i}");
        }

        $this->actingAs($this->admin())->get('/admin/contact-submissions?state=unread')->assertInertia(
            fn (Assert $p) => $p->where('submissions.links.1.url', fn (?string $url) => is_string($url) && str_contains($url, 'state=unread'))->etc()
        );
    }
}
