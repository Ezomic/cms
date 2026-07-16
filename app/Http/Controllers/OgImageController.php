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
    public function project(Project $project): Response
    {
        abort_unless($project->published, 404);

        $cacheKey = 'og.project.'.$project->id.'.'.$project->updated_at->timestamp;

        $png = Cache::rememberForever($cacheKey, function () use ($project) {
            return $this->generate(
                $project->name,
                $project->client_name.' - '.$project->year,
                implode('   -   ', $project->tagList()),
                Profile::current()->name,
            );
        });

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=604800',
        ]);
    }

    public function post(Post $post): Response
    {
        abort_unless($post->published, 404);

        $cacheKey = 'og.post.'.$post->id.'.'.$post->updated_at->timestamp;

        $png = Cache::rememberForever($cacheKey, function () use ($post) {
            return $this->generate(
                $post->title,
                $post->published_at?->format('F j, Y') ?? '',
                Str::limit(strip_tags((string) $post->excerpt), 60),
                Profile::current()->name,
            );
        });

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=604800',
        ]);
    }

    public function home(): Response
    {
        $profile = Profile::current();
        $cacheKey = 'og.home.'.$profile->updated_at?->timestamp;

        $png = Cache::rememberForever($cacheKey, function () use ($profile) {
            $availability = $profile->available ? 'Available for new projects' : 'Currently booked';

            return $this->generate(
                $profile->name,
                $profile->tagline,
                $profile->city.', Netherlands   -   '.$availability,
                $profile->name,
            );
        });

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=604800',
        ]);
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

        imagefill($img, 0, 0, $bg);

        // Border
        imagerectangle($img, 0, 0, $w - 1, $h - 1, $line);

        // Accent bar top
        imagefilledrectangle($img, 0, 0, $w, 6, $accent);

        $display = resource_path('fonts/SpaceGrotesk-Bold.ttf');
        $body = resource_path('fonts/Inter-Regular.ttf');
        $bodyBold = resource_path('fonts/Inter-Bold.ttf');
        $marginX = 72;
        $maxWidth = $w - ($marginX * 2);

        // Dot + owner label top-left
        imagefilledellipse($img, 52, 52, 10, 10, $accent);
        imagettftext($img, 15, 0, $marginX, 58, $soft, $bodyBold, strtoupper($owner));

        // Title — TrueType, wrapped to the content width
        $titleSize = 50;
        $titleLeading = 64;
        $y = 236;
        foreach ($this->wrapText($title, $titleSize, $display, $maxWidth) as $lineText) {
            imagettftext($img, $titleSize, 0, $marginX, $y, $ink, $display, $lineText);
            $y += $titleLeading;
        }

        // Subtitle
        $y += 8;
        imagettftext($img, 26, 0, $marginX, $y, $soft, $body, $subtitle);

        // Detail / tags
        $y += 44;
        imagettftext($img, 20, 0, $marginX, $y, $soft, $body, $detail);

        // Bottom rule + site URL
        imageline($img, $marginX, $h - 72, $w - $marginX, $h - 72, $line);
        imagettftext($img, 16, 0, $marginX, $h - 44, $soft, $body, route('home'));

        ob_start();
        imagepng($img);
        $png = ob_get_clean();
        imagedestroy($img);

        return $png;
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
