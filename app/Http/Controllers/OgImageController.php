<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Profile;
use App\Models\Project;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class OgImageController extends Controller
{
    // Which render path produced the last image; surfaced via X-OG-Renderer so
    // a rendering failure on a server we can't SSH into is diagnosable by curl.
    private ?string $renderer = null;

    public function project(Project $project): Response
    {
        abort_unless($project->published, 404);

        return $this->respond(
            'og.project.'.$project->id.'.'.$project->updated_at->timestamp,
            fn (): array => [
                $project->name,
                $project->client_name.' - '.$project->year,
                implode('   -   ', $project->tagList()),
                Profile::current()->name,
            ],
        );
    }

    public function post(Post $post): Response
    {
        abort_unless($post->published, 404);

        return $this->respond(
            'og.post.'.$post->id.'.'.$post->updated_at->timestamp,
            fn (): array => [
                $post->title,
                $post->published_at?->format('F j, Y') ?? '',
                Str::limit(strip_tags((string) $post->excerpt), 60),
                Profile::current()->name,
            ],
        );
    }

    public function home(): Response
    {
        $profile = Profile::current();

        return $this->respond(
            'og.home.'.$profile->updated_at?->timestamp,
            function () use ($profile): array {
                $availability = $profile->available ? 'Available for new projects' : 'Currently booked';

                return [
                    $profile->name,
                    $profile->tagline,
                    $profile->city.', Netherlands   -   '.$availability,
                    $profile->name,
                ];
            },
        );
    }

    /**
     * Cache and serve a generated OG PNG. If anything in generation fails —
     * including GD itself being unavailable, which faults before generate()'s
     * own guards — fall back to the committed static image so the endpoint
     * never 500s, and surface the cause in X-OG-Error headers (prod has no
     * SSH and hides errors, so this is how the server-side fix is diagnosed).
     *
     * The PNG is base64-encoded before caching: the database cache store on
     * prod (MySQL, utf8mb4 `value` column) rejects raw PNG bytes with a 1366
     * "Incorrect string value" error, so the value must be ASCII-safe.
     *
     * @param  \Closure(): array{string, string, string, string}  $args
     */
    private function respond(string $cacheKey, \Closure $args): Response
    {
        try {
            $encoded = Cache::rememberForever($cacheKey, fn (): string => base64_encode($this->generate(...$args())));
            $png = base64_decode($encoded);

            return response($png, 200, [
                'Content-Type' => 'image/png',
                'Cache-Control' => 'public, max-age=604800',
                'X-OG-Renderer' => $this->renderer ?? 'cached',
            ]);
        } catch (\Throwable $e) {
            report($e);

            $static = @file_get_contents(public_path('og-default.png'));

            return response($static === false ? '' : $static, 200, [
                'Content-Type' => 'image/png',
                'Cache-Control' => 'no-store',
                'X-OG-Renderer' => 'static',
                'X-OG-Error' => class_basename($e),
                'X-OG-Error-Message' => Str::limit($e->getMessage(), 180),
            ]);
        }
    }

    private function generate(string $title, string $subtitle, string $detail, string $owner): string
    {
        $w = 1200;
        $h = 630;
        $img = imagecreatetruecolor($w, $h);

        $bg = imagecolorallocate($img, 247, 247, 244);
        $ink = imagecolorallocate($img, 23, 24, 26);
        $accent = imagecolorallocate($img, 232, 89, 12);
        $soft = imagecolorallocate($img, 99, 100, 95);
        $line = imagecolorallocate($img, 221, 221, 214);

        if ($bg === false || $ink === false || $accent === false || $soft === false || $line === false) {
            throw new \RuntimeException('Failed to allocate OG image colors.');
        }

        $this->drawBase($img, $bg, $accent, $line, $w, $h);

        // TrueType needs GD+FreeType and readable font files; either can fail at
        // render time (not just the capability check), so guard the actual draw
        // and degrade to bitmap, then to the bare base card, rather than 500.
        try {
            if ($this->trueTypeAvailable()) {
                $this->drawTrueTypeText($img, $title, $subtitle, $detail, $owner, $ink, $soft, $w, $h);
                $this->renderer = 'truetype';
            } else {
                $this->drawBitmapText($img, $title, $subtitle, $detail, $owner, $soft, $w, $h);
                $this->renderer = 'bitmap:no-freetype';
            }
        } catch (\Throwable $e) {
            report($e);
            $this->renderer = 'bitmap:'.class_basename($e);

            try {
                $this->drawBase($img, $bg, $accent, $line, $w, $h);
                $this->drawBitmapText($img, $title, $subtitle, $detail, $owner, $soft, $w, $h);
            } catch (\Throwable $inner) {
                report($inner);
                $this->renderer = 'base:'.class_basename($inner);
                $this->drawBase($img, $bg, $accent, $line, $w, $h);
            }
        }

        ob_start();
        imagepng($img);
        $png = ob_get_clean();
        imagedestroy($img);

        return $png;
    }

    private function drawBase(\GdImage $img, int $bg, int $accent, int $line, int $w, int $h): void
    {
        imagefilledrectangle($img, 0, 0, $w, $h, $bg);
        imagerectangle($img, 0, 0, $w - 1, $h - 1, $line);
        imagefilledrectangle($img, 0, 0, $w, 6, $accent);
        imagefilledellipse($img, 52, 52, 10, 10, $accent);
        imageline($img, 72, $h - 72, $w - 72, $h - 72, $line);
    }

    /**
     * TrueType text needs GD compiled with FreeType and the shipped .ttf files
     * readable on disk. When the capability check passes but a draw still throws
     * at runtime, generate() catches it and degrades to bitmap.
     */
    protected function trueTypeAvailable(): bool
    {
        return function_exists('imagettftext')
            && function_exists('imagettfbbox')
            && ((gd_info()['FreeType Support'] ?? false) === true);
    }

    protected function drawTrueTypeText(\GdImage $img, string $title, string $subtitle, string $detail, string $owner, int $ink, int $soft, int $w, int $h): void
    {
        $display = resource_path('fonts/SpaceGrotesk-Bold.ttf');
        $body = resource_path('fonts/Inter-Regular.ttf');
        $bodyBold = resource_path('fonts/Inter-Bold.ttf');
        $marginX = 72;
        $maxWidth = $w - ($marginX * 2);

        imagettftext($img, 15, 0, $marginX, 58, $soft, $bodyBold, strtoupper($owner));

        $titleSize = 50;
        $titleLeading = 64;
        $y = 236;
        foreach ($this->wrapText($title, $titleSize, $display, $maxWidth) as $lineText) {
            imagettftext($img, $titleSize, 0, $marginX, $y, $ink, $display, $lineText);
            $y += $titleLeading;
        }

        $y += 8;
        imagettftext($img, 26, 0, $marginX, $y, $soft, $body, $subtitle);

        $y += 44;
        imagettftext($img, 20, 0, $marginX, $y, $soft, $body, $detail);

        imagettftext($img, 16, 0, $marginX, $h - 44, $soft, $body, route('home'));
    }

    /**
     * Degraded rendering for GD builds without FreeType: built-in bitmap fonts
     * (imagestring), with the title scaled up for legibility. Keeps link
     * previews working with real per-page text instead of a hard 500.
     */
    private function drawBitmapText(\GdImage $img, string $title, string $subtitle, string $detail, string $owner, int $soft, int $w, int $h): void
    {
        $marginX = 72;
        $font = 5;

        imagestring($img, $font, $marginX, 42, strtoupper($owner), $soft);

        $scale = 4;
        $charsPerLine = (int) floor(($w - $marginX * 2) / (imagefontwidth($font) * $scale));
        $lineHeight = imagefontheight($font) * $scale + 12;
        $y = 200;
        foreach (explode("\n", wordwrap($title, max(1, $charsPerLine), "\n", true)) as $lineText) {
            $this->drawScaledString($img, $lineText, $marginX, $y, $scale);
            $y += $lineHeight;
        }

        $y += 12;
        imagestring($img, $font, $marginX, $y, $subtitle, $soft);
        imagestring($img, $font, $marginX, $y + 28, $detail, $soft);

        imagestring($img, $font, $marginX, $h - 52, route('home'), $soft);
    }

    private function drawScaledString(\GdImage $img, string $text, int $x, int $y, int $scale): void
    {
        if ($text === '') {
            return;
        }

        $font = 5;
        $srcW = max(1, imagefontwidth($font) * strlen($text));
        $srcH = max(1, imagefontheight($font));

        $tmp = imagecreatetruecolor($srcW, $srcH);
        $tmpBg = imagecolorallocate($tmp, 247, 247, 244);
        $tmpInk = imagecolorallocate($tmp, 23, 24, 26);

        if ($tmpBg === false || $tmpInk === false) {
            imagedestroy($tmp);

            return;
        }

        imagefill($tmp, 0, 0, $tmpBg);
        imagestring($tmp, $font, 0, 0, $text, $tmpInk);
        imagecopyresampled($img, $tmp, $x, $y, 0, 0, $srcW * $scale, $srcH * $scale, $srcW, $srcH);
        imagedestroy($tmp);
    }

    /**
     * Word-wrap to a maximum pixel width for the given TrueType font/size.
     *
     * @return array<int, string>
     */
    private function wrapText(string $text, float $size, string $font, int $maxWidth): array
    {
        $lines = [];
        $current = '';

        foreach (explode(' ', $text) as $word) {
            $trial = $current === '' ? $word : $current.' '.$word;

            if ($current !== '' && $this->textWidth($trial, $size, $font) > $maxWidth) {
                $lines[] = $current;
                $current = $word;
            } else {
                $current = $trial;
            }
        }

        if ($current !== '') {
            $lines[] = $current;
        }

        return $lines;
    }

    private function textWidth(string $text, float $size, string $font): int
    {
        $box = imagettfbbox($size, 0, $font, $text);

        return $box === false ? 0 : abs($box[2] - $box[0]);
    }
}
