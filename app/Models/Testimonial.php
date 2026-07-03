<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Testimonial extends Model
{
    use SoftDeletes;

    protected $fillable = ['quote', 'author_name', 'author_role', 'featured'];

    protected $casts = [
        'featured' => 'boolean',
    ];
}
