<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    protected $fillable = ['category', 'name', 'sort_order'];

    public function scopeOrdered($query)
    {
        return $query->orderBy('category')->orderBy('sort_order');
    }
}
