<?php

namespace App\Models;

use App\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Testimonial extends Model
{
    use LogsActivity, SoftDeletes;

    protected $fillable = ['quote', 'author_name', 'author_role', 'featured'];

    protected $casts = [
        'featured' => 'boolean',
    ];

    public function activityLabel(): string
    {
        return $this->author_name ?: Str::limit($this->quote, 40);
    }
}
