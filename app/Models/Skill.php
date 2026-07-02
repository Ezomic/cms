<?php

namespace App\Models;

use App\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    use LogsActivity;

    protected $fillable = ['category', 'name', 'sort_order'];

    public function scopeOrdered($query)
    {
        return $query->orderBy('category')->orderBy('sort_order');
    }
}
