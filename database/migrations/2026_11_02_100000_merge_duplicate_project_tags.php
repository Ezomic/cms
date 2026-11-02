<?php

use App\Models\Project;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;

// Content migration (CMS-142). Tags are a free-text comma list on projects, so
// "Vue 3" and "Vue.js" were two tags with two thin tag pages. This rewrites
// every project (published, draft and trashed alike) to one canonical spelling.
// The three known pairs use the product's own name; any other spelling that
// differs only by case or spacing is folded to its most common form. Dotted and
// numbered tags such as ".NET 9" are never merged with their dotless lookalikes.
// The model is used so BustsHomeCache clears the cached home page. down() is a
// no-op: the original spellings are not recoverable.
return new class extends Migration
{
    /** @var array<string, string> letters-and-digits form => canonical tag */
    private const CANONICAL = [
        'inertia' => 'Inertia.js',
        'inertiajs' => 'Inertia.js',
        'tailwind' => 'Tailwind CSS',
        'tailwindcss' => 'Tailwind CSS',
        'vue' => 'Vue.js',
        'vue3' => 'Vue.js',
        'vuejs' => 'Vue.js',
    ];

    public function up(): void
    {
        $projects = Project::withTrashed()->whereNotNull('tags')->get();
        $spellings = $this->mostCommonSpellings($projects);

        foreach ($projects as $project) {
            $before = $this->split((string) $project->tags);
            $after = collect($before)
                ->map(fn (string $tag): string => $this->canonical($tag, $spellings))
                ->unique()
                ->values()
                ->all();

            if ($after !== $before) {
                $project->update(['tags' => implode(', ', $after)]);
            }
        }
    }

    public function down(): void {}

    /**
     * @return array<int, string>
     */
    private function split(string $tags): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $tags)), fn (string $tag): bool => $tag !== ''));
    }

    /**
     * @param  array<string, string>  $spellings
     */
    private function canonical(string $tag, array $spellings): string
    {
        return self::CANONICAL[$this->alphanumeric($tag)] ?? $spellings[$this->foldKey($tag)] ?? $tag;
    }

    /**
     * @param  Collection<int, Project>  $projects
     * @return array<string, string> case and spacing folded key => most common spelling
     */
    private function mostCommonSpellings(Collection $projects): array
    {
        $counts = [];

        foreach ($projects as $project) {
            foreach ($this->split((string) $project->tags) as $tag) {
                $spelling = $this->collapseSpaces($tag);
                $counts[$this->foldKey($tag)][$spelling] = ($counts[$this->foldKey($tag)][$spelling] ?? 0) + 1;
            }
        }

        return array_map(function (array $bySpelling): string {
            uksort($bySpelling, fn (string $a, string $b): int => ($bySpelling[$b] <=> $bySpelling[$a]) ?: strcmp($a, $b));

            return array_key_first($bySpelling);
        }, $counts);
    }

    private function collapseSpaces(string $tag): string
    {
        return (string) preg_replace('/\s+/', ' ', $tag);
    }

    private function foldKey(string $tag): string
    {
        return mb_strtolower($this->collapseSpaces($tag));
    }

    private function alphanumeric(string $tag): string
    {
        return (string) preg_replace('/[^a-z0-9]/', '', mb_strtolower($tag));
    }
};
