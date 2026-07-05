<?php

namespace App\Models;

use App\Concerns\BustsHomeCache;
use App\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Post extends Model
{
    use BustsHomeCache, LogsActivity, SoftDeletes;

    protected $fillable = [
        'title', 'slug', 'excerpt', 'body', 'published', 'published_at', 'meta_title', 'meta_description',
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

    public function metaTitle(): string
    {
        return $this->meta_title ?: $this->title;
    }

    public function metaDescription(): string
    {
        if ($this->meta_description) {
            return $this->meta_description;
        }

        if ($this->excerpt) {
            return $this->excerpt;
        }

        return $this->body ? Str::limit(trim(strip_tags($this->body)), 160) : '';
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
