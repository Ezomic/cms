<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Profile;
use App\Models\Project;
use App\Models\Testimonial;
use Illuminate\Support\Collection;

/**
 * Finds content gaps that are invisible from the site itself.
 *
 * HasLocalizedContent falls back to the English column whenever the _nl one is
 * empty, so a half-translated site serves English to Dutch visitors and looks
 * completely fine doing it. The same silence applies to SEO fields, which fall
 * back to a description, and to image_alt, which falls back to the project
 * name. Nothing surfaces any of it, so it never gets fixed.
 *
 * Read-only reporting: no writes, no auto-translation. Checks live here rather
 * than being scattered per model so adding a field is a one-line change.
 */
class ContentCompleteness
{
    /**
     * Translatable pairs, as base column => human label for the missing Dutch
     * side. A pair is only a gap when the English side is filled: with no
     * source there is nothing to translate.
     */
    private const PROJECT_TRANSLATABLE = [
        'description' => 'Dutch description',
        'outcome' => 'Dutch outcome',
        'body' => 'Dutch case study body',
        'meta_title' => 'Dutch meta title',
        'meta_description' => 'Dutch meta description',
        'image_alt' => 'Dutch image alt text',
    ];

    private const PROFILE_TRANSLATABLE = [
        'tagline' => 'Dutch tagline',
        'hero_headline' => 'Dutch hero headline',
        'hero_subtext' => 'Dutch hero subtext',
        'docs_intro' => 'Dutch docs intro',
        'meta_title' => 'Dutch meta title',
        'meta_description' => 'Dutch meta description',
    ];

    private const SEO_REQUIRED = [
        'meta_title' => 'Meta title',
        'meta_description' => 'Meta description',
    ];

    /**
     * Only published records are checked. A draft being incomplete is the
     * normal state of a draft, and flagging them would bury the real gaps.
     *
     * @return Collection<int, ContentGap>
     */
    public function report(): Collection
    {
        /** @var Collection<int, ContentGap> $gaps */
        $gaps = collect([
            ...$this->projects(),
            ...$this->testimonials(),
            ...$this->profile(),
        ]);

        return $gaps->values();
    }

    public function count(): int
    {
        return $this->report()->count();
    }

    /**
     * @return list<ContentGap>
     */
    private function projects(): array
    {
        $rows = [];

        foreach (Project::published()->ordered()->with('images')->get() as $project) {
            $gaps = $this->missingTranslations($project, self::PROJECT_TRANSLATABLE);

            foreach (self::SEO_REQUIRED as $column => $label) {
                if (blank($project->{$column})) {
                    $gaps[] = $label;
                }
            }

            if (blank($project->image)) {
                $gaps[] = 'Cover image';
            } elseif (blank($project->image_alt)) {
                // Alt text is meaningless without an image, so it is not also
                // reported when the cover itself is missing.
                $gaps[] = 'Image alt text';
            }

            foreach ($project->images as $image) {
                if (blank($image->caption)) {
                    $gaps[] = 'Gallery caption';
                    break;
                }
            }

            if ($gaps !== []) {
                $rows[] = new ContentGap('Project', (string) $project->name, route('admin.projects.edit', $project), $gaps);
            }
        }

        return $rows;
    }

    /**
     * @return list<ContentGap>
     */
    private function testimonials(): array
    {
        $rows = [];

        foreach (Testimonial::all() as $testimonial) {
            if (blank($testimonial->quote_nl) && filled($testimonial->quote)) {
                $rows[] = new ContentGap(
                    'Testimonial',
                    (string) $testimonial->author_name,
                    route('admin.testimonials.edit', $testimonial),
                    ['Dutch quote'],
                );
            }
        }

        return $rows;
    }

    /**
     * @return list<ContentGap>
     */
    private function profile(): array
    {
        $profile = Profile::current();

        $gaps = $this->missingTranslations($profile, self::PROFILE_TRANSLATABLE);

        foreach (self::SEO_REQUIRED as $column => $label) {
            if (blank($profile->{$column})) {
                $gaps[] = $label;
            }
        }

        if ($gaps === []) {
            return [];
        }

        return [new ContentGap('Profile', (string) $profile->name, route('admin.profile.edit'), $gaps)];
    }

    /**
     * @param  array<string, string>  $translatable
     * @return list<string>
     */
    private function missingTranslations(object $model, array $translatable): array
    {
        $gaps = [];

        foreach ($translatable as $column => $label) {
            if (blank($model->{$column.'_nl'}) && filled($model->{$column})) {
                $gaps[] = $label;
            }
        }

        return $gaps;
    }
}
