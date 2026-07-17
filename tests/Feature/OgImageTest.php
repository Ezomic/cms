<?php

namespace Tests\Feature;

use App\Http\Controllers\OgImageController;
use App\Models\Post;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Response;
use Tests\TestCase;

class OgImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_og_image_renders_a_valid_png(): void
    {
        $response = $this->get('/og/home.png');

        $response->assertOk();
        $response->assertHeader('content-type', 'image/png');

        $size = getimagesizefromstring($response->getContent());
        $this->assertSame(1200, $size[0]);
        $this->assertSame(630, $size[1]);
    }

    public function test_project_og_image_renders_a_valid_png(): void
    {
        $project = Project::create([
            'name' => 'Test Project',
            'client_name' => 'Acme BV',
            'year' => '2026',
            'tags' => 'Laravel, Vue',
            'body' => 'Some case study body.',
            'published' => true,
            'sort_order' => 0,
        ]);

        $response = $this->get("/og/work/{$project->slug}.png");

        $response->assertOk();
        $response->assertHeader('content-type', 'image/png');

        $size = getimagesizefromstring($response->getContent());
        $this->assertSame(1200, $size[0]);
        $this->assertSame(630, $size[1]);
    }

    public function test_post_og_image_renders_a_valid_png(): void
    {
        $post = Post::create([
            'title' => 'Test Post',
            'excerpt' => 'A short summary of the post.',
            'body' => 'Some blog post body.',
            'published' => true,
        ]);

        $response = $this->get("/og/blog/{$post->slug}.png");

        $response->assertOk();
        $response->assertHeader('content-type', 'image/png');

        $size = getimagesizefromstring($response->getContent());
        $this->assertSame(1200, $size[0]);
        $this->assertSame(630, $size[1]);
    }

    public function test_post_og_image_404s_for_unpublished_post(): void
    {
        $post = Post::create([
            'title' => 'Draft Post',
            'body' => 'Draft body.',
            'published' => false,
        ]);

        $this->get("/og/blog/{$post->slug}.png")->assertNotFound();
    }

    public function test_og_image_renders_truetype_title_text(): void
    {
        $project = Project::create([
            'name' => 'Café Résumé',
            'client_name' => 'Zürich BV',
            'year' => '2026',
            'tags' => 'Laravel',
            'published' => true,
            'sort_order' => 0,
        ]);

        $response = $this->get("/og/work/{$project->slug}.png");
        $response->assertOk();

        $img = imagecreatefromstring($response->getContent());
        $this->assertNotFalse($img);

        // The title is drawn in ink #17181A via imagettftext. Finding those
        // pixels proves the TrueType text actually rendered (a GD build without
        // FreeType would silently produce a blank card that still passes the
        // dimension checks).
        $found = false;
        for ($x = 60; $x < 720 && ! $found; $x += 2) {
            for ($y = 180; $y < 245; $y += 2) {
                $rgb = imagecolorat($img, $x, $y);
                if ((($rgb >> 16) & 0xFF) === 23 && (($rgb >> 8) & 0xFF) === 24 && ($rgb & 0xFF) === 26) {
                    $found = true;
                    break;
                }
            }
        }

        $this->assertTrue($found, 'Expected ink-colored TrueType title pixels in the OG image.');
    }

    public function test_bitmap_fallback_renders_a_valid_png_without_truetype(): void
    {
        // Simulate a GD build without FreeType by forcing the bitmap path.
        $controller = new class extends OgImageController
        {
            protected function trueTypeAvailable(): bool
            {
                return false;
            }
        };

        $generate = new \ReflectionMethod(OgImageController::class, 'generate');
        $generate->setAccessible(true);

        /** @var string $png */
        $png = $generate->invoke($controller, 'Fallback Title', 'Subtitle here', 'Some detail', 'Owner Name');

        $this->assertNotSame('', $png);
        $size = getimagesizefromstring($png);
        $this->assertSame(1200, $size[0]);
        $this->assertSame(630, $size[1]);
        $this->assertSame('image/png', $size['mime']);

        // The scaled title is drawn in ink #17181A; finding those pixels proves
        // the bitmap fallback actually rendered text rather than a blank card.
        $img = imagecreatefromstring($png);
        $found = false;
        for ($x = 60; $x < 720 && ! $found; $x += 2) {
            for ($y = 195; $y < 260; $y += 2) {
                $rgb = imagecolorat($img, $x, $y);
                if ((($rgb >> 16) & 0xFF) === 23 && (($rgb >> 8) & 0xFF) === 24 && ($rgb & 0xFF) === 26) {
                    $found = true;
                    break;
                }
            }
        }

        $this->assertTrue($found, 'Expected ink-colored bitmap title pixels in the fallback OG image.');
    }

    public function test_render_falls_back_to_bitmap_when_truetype_draw_throws(): void
    {
        // Capability check passes, but the actual TrueType draw throws at runtime
        // (the real production failure mode). generate() must catch and degrade.
        $controller = new class extends OgImageController
        {
            protected function trueTypeAvailable(): bool
            {
                return true;
            }

            protected function drawTrueTypeText(\GdImage $img, string $title, string $subtitle, string $detail, string $owner, int $ink, int $soft, int $w, int $h): void
            {
                throw new \RuntimeException('simulated FreeType/font failure');
            }
        };

        $generate = new \ReflectionMethod(OgImageController::class, 'generate');
        $generate->setAccessible(true);

        /** @var string $png */
        $png = $generate->invoke($controller, 'Fallback Title', 'Subtitle', 'Detail', 'Owner');

        $size = getimagesizefromstring($png);
        $this->assertSame(1200, $size[0]);
        $this->assertSame(630, $size[1]);
        $this->assertSame('image/png', $size['mime']);
    }

    public function test_serves_static_fallback_and_error_header_when_generation_fails(): void
    {
        // Whatever breaks generation (e.g. GD unavailable on the server),
        // respond() must still return 200 with the committed static image and
        // name the cause in a header rather than 500. Throwing from the args
        // closure stands in for any failure inside the cached generation.
        $controller = new OgImageController;

        $respond = new \ReflectionMethod(OgImageController::class, 'respond');
        $respond->setAccessible(true);

        /** @var Response $response */
        $response = $respond->invoke($controller, 'og.test.key', fn (): array => throw new \RuntimeException('simulated GD failure'));

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('image/png', $response->headers->get('Content-Type'));
        $this->assertSame('static', $response->headers->get('X-OG-Renderer'));
        $this->assertNotNull($response->headers->get('X-OG-Error'));

        $size = getimagesizefromstring((string) $response->getContent());
        $this->assertSame(1200, $size[0]);
        $this->assertSame(630, $size[1]);
    }
}
