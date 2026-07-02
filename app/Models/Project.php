<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'name', 'client_name', 'year', 'description', 'published', 'tags', 'sort_order',
    ];

    protected $casts = [
        'published' => 'boolean',
    ];

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
}
