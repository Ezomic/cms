<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\Project;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

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

        // Dot + owner label top-left
        imagefilledellipse($img, 52, 52, 10, 10, $accent);
        imagestring($img, 3, 68, 46, strtoupper($owner), $soft);

        // Title — wrap long text manually
        $titleLines = $this->wrapText($title, 36);
        $y = 200;
        foreach ($titleLines as $line_text) {
            // Use built-in font 5 (largest GD built-in)
            imagestring($img, 5, 72, $y, $line_text, $ink);
            $y += 22;
        }

        // Subtitle
        imagestring($img, 4, 72, $y + 20, $subtitle, $soft);

        // Detail / tags
        imagestring($img, 3, 72, $y + 50, $detail, $soft);

        // Bottom rule
        imageline($img, 72, $h - 72, $w - 72, $h - 72, $line);
        imagestring($img, 2, 72, $h - 58, route('home'), $soft);

        ob_start();
        imagepng($img);
        $png = ob_get_clean();
        imagedestroy($img);

        return $png;
    }

    /**
     * @return array<int, string>
     */
    private function wrapText(string $text, int $maxChars): array
    {
        $words = explode(' ', $text);
        $lines = [];
        $current = '';

        foreach ($words as $word) {
            if (strlen($current) + strlen($word) + 1 > $maxChars) {
                $lines[] = $current;
                $current = $word;
            } else {
                $current = $current === '' ? $word : $current.' '.$word;
            }
        }

        if ($current !== '') {
            $lines[] = $current;
        }

        return $lines;
    }
}
