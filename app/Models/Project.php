<?php

namespace App\Models;

use App\Concerns\BustsHomeCache;
use App\Concerns\HasLocalizedContent;
use App\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Project extends Model
{
    use BustsHomeCache, HasLocalizedContent, LogsActivity, SoftDeletes;

    protected $fillable = [
        'name', 'image', 'image_alt', 'slug', 'github_url', 'client_name', 'year', 'description', 'outcome', 'body', 'published', 'tags', 'sort_order', 'meta_title', 'meta_description',
        'description_nl', 'outcome_nl', 'body_nl', 'image_alt_nl', 'meta_title_nl', 'meta_description_nl',
    ];

    protected $casts = [
        'published' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Project $project) {
            if (! $project->slug) {
                $project->slug = $project->uniqueSlugFrom($project->name);
            }
        });
    }

    /**
     * @param  Builder<Project>  $query
     * @return Builder<Project>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderByDesc('year');
    }

    /**
     * @param  Builder<Project>  $query
     * @return Builder<Project>
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('published', true);
    }

    /**
     * @return array<int, string>
     */
    public function tagList(): array
    {
        if (! $this->tags) {
            return [];
        }

        return array_map('trim', explode(',', $this->tags));
    }

    public function imageUrl(): ?string
    {
        return $this->image ? asset('storage/'.$this->image) : null;
    }

    public function imageAlt(): string
    {
        return $this->localized('image_alt') ?: $this->name;
    }

    /**
     * Named localizedX rather than description()/outcome()/body() — Eloquent's
     * __get() treats an undefined attribute access as a possible relationship
     * method call when a same-named method exists, which throws for models
     * missing that attribute (e.g. `new Project(['name' => 'X'])`).
     */
    public function localizedDescription(): ?string
    {
        return $this->localized('description');
    }

    public function localizedOutcome(): ?string
    {
        return $this->localized('outcome');
    }

    public function localizedBody(): ?string
    {
        return $this->localized('body');
    }

    /**
     * @return HasMany<ProjectImage, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProjectImage::class)->orderBy('sort_order');
    }

    public function metaTitle(): string
    {
        return $this->localized('meta_title') ?: $this->name;
    }

    public function metaDescription(): string
    {
        if ($this->localized('meta_description')) {
            return $this->localized('meta_description');
        }

        if ($this->localizedDescription()) {
            return $this->localizedDescription();
        }

        return $this->localizedBody() ? Str::limit(trim(strip_tags($this->localizedBody())), 160) : '';
    }

    protected function uniqueSlugFrom(string $name): string
    {
        $base = Str::slug($name) ?: 'project';
        $slug = $base;
        $i = 2;

        while (static::where('slug', $slug)->where('id', '!=', $this->id ?? 0)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
