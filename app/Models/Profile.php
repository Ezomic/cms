<?php

namespace App\Models;

use App\Concerns\BustsHomeCache;
use App\Concerns\HasLocalizedContent;
use App\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use BustsHomeCache, HasLocalizedContent, LogsActivity;

    protected $table = 'profile';

    protected $fillable = [
        'name', 'city', 'tagline', 'hero_headline', 'hero_subtext',
        'available', 'email', 'linkedin_url', 'github_url',
        'rate', 'availability_from', 'kvk_number',
        'meta_title', 'meta_description', 'docs_intro',
        'tagline_nl', 'hero_headline_nl', 'hero_subtext_nl',
        'docs_intro_nl', 'meta_title_nl', 'meta_description_nl',
    ];

    protected $casts = [
        'available' => 'boolean',
    ];

    /**
     * There is always exactly one profile row (id = 1).
     * This creates it with defaults the first time it's needed.
     *
     * Refreshes after a fresh creation so every column is present in the
     * model's attributes (not just 'id'); otherwise code that reads the
     * model as a plain array (e.g. for caching) sees missing keys instead
     * of nulls, since Eloquent only refetches attributes on ->refresh().
     */
    public static function current(): self
    {
        $profile = static::firstOrCreate(['id' => 1]);

        if ($profile->wasRecentlyCreated) {
            $profile->refresh();
        }

        return $profile;
    }

    public function activityLabel(): string
    {
        return 'Profile';
    }

    /**
     * Named localizedTagline rather than tagline() — Eloquent's __get() treats
     * an undefined attribute access as a possible relationship method call
     * when a same-named method exists, which throws for models missing that
     * attribute (e.g. `new Profile(['name' => 'X'])`).
     */
    public function localizedTagline(): ?string
    {
        return $this->localized('tagline');
    }

    public function heroHeadline(): ?string
    {
        return $this->localized('hero_headline');
    }

    public function heroSubtext(): ?string
    {
        return $this->localized('hero_subtext');
    }

    public function docsIntro(): ?string
    {
        return $this->localized('docs_intro');
    }

    public function metaTitle(): ?string
    {
        return $this->localized('meta_title');
    }

    public function metaDescription(): ?string
    {
        return $this->localized('meta_description');
    }
}
