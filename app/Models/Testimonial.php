<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    protected $fillable = ['quote', 'author_name', 'author_role', 'featured'];

    protected $casts = [
        'featured' => 'boolean',
    ];
}
