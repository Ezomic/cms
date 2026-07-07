<?php

namespace App\Models;

use App\Concerns\BustsHomeCache;
use App\Concerns\HasLocalizedContent;
use App\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Post extends Model
{
    use BustsHomeCache, HasLocalizedContent, LogsActivity, SoftDeletes;

    protected $fillable = [
        'title', 'slug', 'excerpt', 'body', 'published', 'published_at', 'meta_title', 'meta_description',
        'title_nl', 'excerpt_nl', 'body_nl', 'meta_title_nl', 'meta_description_nl',
    ];

    protected $casts = [
        'published' => 'boolean',
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (Post $post) {
            if (! $post->slug) {
                $post->slug = $post->uniqueSlugFrom($post->title);
            }

            if ($post->published && ! $post->published_at) {
                $post->published_at = now();
            }
        });
    }

    public function scopePublished($query)
    {
        return $query->where('published', true);
    }

    /**
     * Named localizedX rather than title()/excerpt()/body() — Eloquent's
     * __get() treats an undefined attribute access as a possible relationship
     * method call when a same-named method exists, which throws for models
     * missing that attribute (e.g. `new Post(['title' => 'X'])`).
     */
    public function localizedTitle(): ?string
    {
        return $this->localized('title');
    }

    public function localizedExcerpt(): ?string
    {
        return $this->localized('excerpt');
    }

    public function localizedBody(): ?string
    {
        return $this->localized('body');
    }

    public function metaTitle(): string
    {
        return $this->localized('meta_title') ?: $this->localizedTitle();
    }

    public function metaDescription(): string
    {
        if ($this->localized('meta_description')) {
            return $this->localized('meta_description');
        }

        if ($this->localizedExcerpt()) {
            return $this->localizedExcerpt();
        }

        return $this->localizedBody() ? Str::limit(trim(strip_tags($this->localizedBody())), 160) : '';
    }

    protected function uniqueSlugFrom(string $title): string
    {
        $base = Str::slug($title) ?: 'post';
        $slug = $base;
        $i = 2;

        while (static::where('slug', $slug)->where('id', '!=', $this->id ?? 0)->exists()) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
