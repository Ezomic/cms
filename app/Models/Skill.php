<?php

namespace App\Models;

use App\Concerns\BustsHomeCache;
use App\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Skill extends Model
{
    use BustsHomeCache, LogsActivity, SoftDeletes;

    protected $fillable = ['category', 'name', 'sort_order'];

    public function scopeOrdered($query)
    {
        return $query->orderBy('category')->orderBy('sort_order');
    }
}
