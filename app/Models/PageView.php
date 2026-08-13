<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageView extends Model
{
    const UPDATED_AT = null;

    protected $fillable = ['path', 'referrer_host'];

    /** Visits with no referrer, grouped under one label in reporting. */
    public const DIRECT = 'direct';

    protected $casts = [
        'created_at' => 'datetime',
    ];
}
