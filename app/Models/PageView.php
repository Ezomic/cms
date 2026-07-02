<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageView extends Model
{
    const UPDATED_AT = null;

    protected $fillable = ['path'];

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
