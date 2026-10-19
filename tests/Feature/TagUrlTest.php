<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Project;
use App\Support\TagSlugs;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagUrlTest extends TestCase
{
    use RefreshDatabase;

    private const TAGS = ['.NET 9', '..x', '.', 'a.b', ' .net', 'Übung', '日本語', 'Laravel', 'NET 9', 'x', 'tag', 'Tailwind CSS', '.0'];

    public function test_every_tag_url_in_the_sitemap_is_requestable_and_has_no_dot_segment(): void
    {
        $this->seedProjectPerTag();

        $urls = $this->sitemapTagUrls();

        $this->assertCount(count(self::TAGS) * 2, $urls);
        $this->assertSame($urls, array_values(array_unique($urls)));

        foreach ($urls as $url) {
            foreach (explode('/', (string) parse_url($url, PHP_URL_PATH)) as $segment) {
                $this->assertStringStartsNotWith('.', rawurldecode($segment), "Dot segment in {$url}");
            }

            $this->get($url)->assertOk();
        }
    }

    public function test_each_tag_url_resolves_back_to_its_own_tag(): void
    {
        $this->seedProjectPerTag();
        $slugs = TagSlugs::from(collect($this->publishedTags()));

        foreach (self::TAGS as $i => $tag) {
            $response = $this->get(route('work.tag', $slugs->slug(trim($tag))))->assertOk();

            foreach (array_keys(self::TAGS) as $j) {
                $j === $i
                    ? $response->assertSee($this->projectName($j), false)
                    : $response->assertDontSee($this->projectName($j), false);
            }
        }
    }

    public function test_dotted_tag_url_has_the_dot_stripped(): void
    {
        Project::create(['name' => 'Overdub', 'tags' => '.NET 9', 'published' => true, 'sort_order' => 0]);

        $this->assertContains(route('work.tag', 'NET 9'), $this->sitemapTagUrls());
        $this->assertContains(route('nl.work.tag', 'NET 9'), $this->sitemapTagUrls());
        $this->get('/nl/work/tag/NET%209')->assertOk()->assertSee('Overdub');
    }

    public function test_existing_non_dot_tag_urls_do_not_change(): void
    {
        Project::create(['name' => 'One', 'tags' => 'Laravel, Tailwind CSS, a.b, Übung', 'published' => true, 'sort_order' => 0]);

        $urls = $this->sitemapTagUrls();

        foreach (['Laravel', 'Tailwind CSS', 'a.b', 'Übung'] as $tag) {
            $this->assertContains(route('work.tag', $tag), $urls);
            $this->get(route('work.tag', $tag))->assertOk();
        }
    }

    public function test_a_plain_tag_keeps_its_slug_when_a_dotted_tag_collides_with_it(): void
    {
        Project::create(['name' => 'PlainProject', 'tags' => 'NET 9', 'published' => true, 'sort_order' => 0]);
        Project::create(['name' => 'DottedProject', 'tags' => '.NET 9', 'published' => true, 'sort_order' => 1]);

        $this->get(route('work.tag', 'NET 9'))->assertOk()->assertSee('PlainProject')->assertDontSee('DottedProject');
        $this->get(route('work.tag', 'NET 9-2'))->assertOk()->assertSee('DottedProject')->assertDontSee('PlainProject');
    }

    public function test_the_raw_dotted_name_is_not_a_tag_url(): void
    {
        Project::create(['name' => 'Overdub', 'tags' => '.NET 9', 'published' => true, 'sort_order' => 0]);

        $this->get('/work/tag/.NET%209')->assertNotFound();
    }

    public function test_unknown_tag_is_still_not_found(): void
    {
        Project::create(['name' => 'One', 'tags' => 'Laravel', 'published' => true, 'sort_order' => 0]);

        $this->get('/work/tag/Missing')->assertNotFound();
    }

    public function test_tag_page_links_and_canonical_use_the_safe_slug(): void
    {
        Project::create(['name' => 'Overdub', 'tags' => '.NET 9', 'published' => true, 'sort_order' => 0]);

        $html = (string) $this->get(route('work.tag', 'NET 9'))->getContent();

        $this->assertStringContainsString('<link rel="canonical" href="'.route('work.tag', 'NET 9').'">', $html);
        $this->assertStringContainsString('href="'.route('work.tag', 'NET 9').'" data-tag=".NET 9"', $html);
        $this->assertStringNotContainsString('/work/tag/.', $html);
        $this->assertStringNotContainsString('/work/tag/%2E', $html);

        $this->get(route('work.index'))->assertSee('href="'.route('work.tag', 'NET 9').'"', false);
    }

    private function seedProjectPerTag(): void
    {
        foreach (self::TAGS as $i => $tag) {
            Project::create([
                'name' => $this->projectName($i),
                'tags' => $tag,
                'published' => true,
                'sort_order' => $i,
            ]);
        }
    }

    private function projectName(int $index): string
    {
        return 'Project P'.$index.'X';
    }

    /**
     * @return list<string>
     */
    private function publishedTags(): array
    {
        return Project::published()->get()->flatMap(fn (Project $p): array => $p->tagList())->all();
    }

    /**
     * @return list<string>
     */
    private function sitemapTagUrls(): array
    {
        preg_match_all('#<loc>([^<]*/work/tag/[^<]*)</loc>#', (string) $this->get('/sitemap.xml')->getContent(), $matches);

        return array_map(html_entity_decode(...), $matches[1]);
    }
}
