<?php

namespace Tests\Feature\Admin;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class ProjectBodySanitisingTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function hostileBodies(): array
    {
        return [
            'script tag' => ['<p>Hi</p><script>alert(1)</script>', 'alert(1)'],
            'script with attributes' => ['<script src="https://evil.test/x.js"></script><p>Hi</p>', 'evil.test'],
            'inline event handler' => ['<p onclick="alert(1)">Hi</p>', 'onclick'],
            'img onerror' => ['<img src="x" onerror="alert(1)">', 'onerror'],
            'javascript link' => ['<a href="javascript:alert(1)">go</a>', 'javascript:'],
            'mixed case javascript link' => ['<a href="JaVaScRiPt:alert(1)">go</a>', 'alert(1)'],
            'entity encoded javascript link' => ['<a href="&#106;avascript:alert(1)">go</a>', 'alert(1)'],
            'tab split javascript link' => ["<a href=\"java\tscript:alert(1)\">go</a>", 'javascript:'],
            'data uri image' => ['<img src="data:text/html;base64,PHNjcmlwdD5hbGVydCgxKTwvc2NyaXB0Pg==">', 'data:'],
            'iframe' => ['<iframe src="https://evil.test"></iframe>', 'iframe'],
            'object and embed' => ['<object data="x.swf"></object><embed src="x.swf">', 'embed'],
            'style attribute with expression' => ['<p style="background:url(javascript:alert(1))">x</p>', 'javascript:'],
            'svg onload' => ['<svg onload="alert(1)"><circle/></svg>', 'onload'],
            'form' => ['<form action="https://evil.test"><input name="x"></form>', 'evil.test'],
            'nested malformed script' => ['<scr<script>ipt>alert(1)</scr</script>ipt>', '<script'],
            'unclosed tag' => ['<p onmouseover="alert(1)"<b>x', 'onmouseover'],
            'meta refresh' => ['<meta http-equiv="refresh" content="0;url=https://evil.test">', 'evil.test'],
            'link target blank without rel' => ['<a href="https://example.com" target="_blank" onfocus="alert(1)">x</a>', 'onfocus'],
        ];
    }

    #[DataProvider('hostileBodies')]
    public function test_hostile_markup_is_removed_from_both_languages(string $input, string $mustNotSurvive): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/admin/projects', [
            'name' => 'Hostile',
            'body' => $input,
            'body_nl' => $input,
        ])->assertRedirect('/admin/projects');

        $project = Project::sole();

        $this->assertStringNotContainsStringIgnoringCase($mustNotSurvive, (string) $project->body);
        $this->assertStringNotContainsStringIgnoringCase($mustNotSurvive, (string) $project->body_nl);
    }

    public function test_updates_are_sanitised_too(): void
    {
        $user = User::factory()->create();
        $project = Project::create(['name' => 'Existing', 'sort_order' => 0, 'body' => '<p>ok</p>']);

        $this->actingAs($user)->put("/admin/projects/{$project->id}", [
            'name' => 'Existing',
            'body' => '<p>ok</p><script>alert(1)</script>',
            'body_nl' => '<img src=x onerror=alert(1)>',
        ])->assertRedirect('/admin/projects');

        $project->refresh();
        $this->assertStringNotContainsString('script', (string) $project->body);
        $this->assertStringNotContainsString('onerror', (string) $project->body_nl);
    }

    public function test_normal_rich_text_survives(): void
    {
        $user = User::factory()->create();
        $html = '<h2>Heading</h2><p>Some <strong>bold</strong>, <em>italic</em> and <code>code</code> with a '
            .'<a href="https://example.com/page">link</a>.</p><ul><li>One</li><li>Two</li></ul><ol><li>First</li></ol>'
            .'<blockquote><p>Quote</p></blockquote><pre><code>$a = 1;</code></pre>'
            .'<p><img src="https://example.com/a.png" alt="An image"></p><h3>Sub</h3>';

        $this->actingAs($user)->post('/admin/projects', ['name' => 'Rich', 'body' => $html])->assertRedirect();

        $body = (string) Project::sole()->body;

        foreach (['<h2>Heading</h2>', '<strong>bold</strong>', '<em>italic</em>', '<code>code</code>', 'href="https://example.com/page"', '<ul>', '<li>One</li>', '<ol>', '<blockquote>', '<pre>', 'src="https://example.com/a.png"', 'alt="An image"', '<h3>Sub</h3>'] as $needle) {
            $this->assertStringContainsString($needle, $body);
        }
    }

    public function test_the_seeded_case_study_bodies_survive_unchanged_in_meaning(): void
    {
        $user = User::factory()->create();
        $html = '<p>Tracker is a tool, and the <a href="https://github.com/Ezomic/tracker">source code is public</a>.</p>'
            ."\n<h2>Why</h2>\n<ul>\n    <li>A key like <code>CMS-71</code> stays put.</li>\n</ul>";

        $this->actingAs($user)->post('/admin/projects', ['name' => 'Seeded', 'body' => $html])->assertRedirect();

        $body = (string) Project::sole()->body;

        $this->assertStringContainsString('<a href="https://github.com/Ezomic/tracker">source code is public</a>', $body);
        $this->assertStringContainsString('<h2>Why</h2>', $body);
        $this->assertStringContainsString('<code>CMS-71</code>', $body);
    }

    public function test_relative_and_mailto_links_are_kept(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/admin/projects', [
            'name' => 'Links',
            'body' => '<a href="/work/other">a</a> <a href="mailto:hi@example.com">b</a> <a href="#top">c</a>',
        ])->assertRedirect();

        $body = (string) Project::sole()->body;
        $this->assertStringContainsString('href="/work/other"', $body);
        $this->assertStringContainsString('href="mailto:hi@example.com"', $body);
        $this->assertStringContainsString('href="#top"', $body);
    }

    public function test_external_links_that_open_a_new_tab_get_noopener(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/admin/projects', [
            'name' => 'Blank',
            'body' => '<a href="https://example.com" target="_blank">x</a>',
        ])->assertRedirect();

        $this->assertStringContainsString('noopener', (string) Project::sole()->body);
    }

    public function test_unicode_is_preserved(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/admin/projects', [
            'name' => 'Unicode',
            'body' => '<p>Café – “quotes” 日本語 😀 €50k</p>',
            'body_nl' => '<p>Één ij-ontwikkelaar, naïef</p>',
        ])->assertRedirect();

        $project = Project::sole();
        $this->assertStringContainsString('Café – “quotes” 日本語 😀 €50k', (string) $project->body);
        $this->assertStringContainsString('Één ij-ontwikkelaar, naïef', (string) $project->body_nl);
    }

    public function test_empty_and_whitespace_only_bodies_are_stored_as_null(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/admin/projects', [
            'name' => 'Empty',
            'body' => '',
            'body_nl' => "  \n\t ",
        ])->assertRedirect();

        $project = Project::sole();
        $this->assertNull($project->body);
        $this->assertNull($project->body_nl);
    }

    public function test_a_body_that_is_only_a_script_becomes_null(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/admin/projects', [
            'name' => 'Only script',
            'body' => '<script>alert(1)</script>',
        ])->assertRedirect();

        $this->assertNull(Project::sole()->body);
    }

    public function test_a_very_large_body_is_rejected_instead_of_chewed_on(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/admin/projects', [
            'name' => 'Huge',
            'body' => str_repeat('<p>x</p>', 100000),
        ])->assertSessionHasErrors('body');

        $this->assertDatabaseCount('projects', 0);
    }

    public function test_a_dutch_body_that_grows_past_the_text_column_when_sanitised_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/admin/projects', [
            'name' => 'Grows',
            'body_nl' => str_repeat('&', 15000).str_repeat('<p>x</p>', 4000),
        ])->assertSessionHasErrors('body_nl');

        $this->assertDatabaseCount('projects', 0);
    }

    public function test_a_large_body_inside_the_limit_is_sanitised(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/admin/projects', [
            'name' => 'Big',
            'body' => str_repeat('<p onclick="x()">line</p>', 5000),
        ])->assertRedirect();

        $this->assertStringNotContainsString('onclick', (string) Project::sole()->body);
    }

    public function test_the_public_project_page_renders_sanitised_markup_only(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/admin/projects', [
            'name' => 'Public',
            'slug' => 'public-case',
            'published' => '1',
            'body' => '<p>Visible</p><script>window.pwned=1</script>',
        ])->assertRedirect();

        auth()->logout();

        $this->get('/work/public-case')
            ->assertOk()
            ->assertSee('Visible', false)
            ->assertDontSee('window.pwned', false);
    }

    public function test_guests_cannot_write_project_bodies(): void
    {
        $this->post('/admin/projects', ['name' => 'X', 'body' => '<p>x</p>'])->assertRedirect('/admin/login');

        $this->assertDatabaseCount('projects', 0);
    }
}
