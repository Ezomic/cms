<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PageViewTotal extends Model
{
    public $timestamps = false;

    protected $primaryKey = 'path';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['path', 'views'];

    protected $casts = [
        'views' => 'integer',
    ];
}
