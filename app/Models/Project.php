<?php

namespace App\Models;

use App\Concerns\BustsHomeCache;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use BustsHomeCache;

    protected $fillable = [
        'name', 'client_name', 'year', 'description', 'tags', 'sort_order',
    ];

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
}
