<?php

namespace App\Models;

use App\Concerns\BustsHomeCache;
use App\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Project extends Model
{
    use BustsHomeCache, LogsActivity, SoftDeletes;

    protected $fillable = [
        'name', 'image', 'slug', 'client_name', 'year', 'description', 'outcome', 'body', 'published', 'tags', 'sort_order', 'meta_title', 'meta_description',
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

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderByDesc('year');
    }

    public function scopePublished($query)
    {
        return $query->where('published', true);
    }

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

    public function metaTitle(): string
    {
        return $this->meta_title ?: $this->name;
    }

    public function metaDescription(): string
    {
        if ($this->meta_description) {
            return $this->meta_description;
        }

        if ($this->description) {
            return $this->description;
        }

        return $this->body ? Str::limit(trim(strip_tags($this->body)), 160) : '';
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
