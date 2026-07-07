<?php

namespace App\Models;

use App\Concerns\BustsHomeCache;
use App\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Skill extends Model
{
    use BustsHomeCache, LogsActivity, SoftDeletes;

    protected $fillable = ['category', 'name', 'sort_order'];

    /**
     * @param  Builder<Skill>  $query
     * @return Builder<Skill>
     */
    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('category')->orderBy('sort_order');
    }
}
