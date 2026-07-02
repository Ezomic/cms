<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Project extends Model
{
    protected $fillable = [
        'name', 'slug', 'client_name', 'year', 'description', 'body', 'tags', 'sort_order',
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

    public function tagList(): array
    {
        if (! $this->tags) {
            return [];
        }

        return array_map('trim', explode(',', $this->tags));
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
