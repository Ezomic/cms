<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Skill extends Model
{
    use SoftDeletes;

    protected $fillable = ['category', 'name', 'sort_order'];

    public function scopeOrdered($query)
    {
        return $query->orderBy('category')->orderBy('sort_order');
    }
}
