<?php

namespace App\Models;

use App\Concerns\BustsHomeCache;
use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    use BustsHomeCache;

    protected $fillable = ['quote', 'author_name', 'author_role', 'featured'];

    protected $casts = [
        'featured' => 'boolean',
    ];
}
