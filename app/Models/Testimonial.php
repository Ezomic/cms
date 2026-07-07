<?php

namespace App\Models;

use App\Concerns\BustsHomeCache;
use App\Concerns\HasLocalizedContent;
use App\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Testimonial extends Model
{
    use BustsHomeCache, HasLocalizedContent, LogsActivity, SoftDeletes;

    protected $fillable = ['quote', 'author_name', 'author_role', 'company_name', 'featured', 'quote_nl'];

    protected $casts = [
        'featured' => 'boolean',
    ];

    public function activityLabel(): string
    {
        return $this->author_name ?: Str::limit($this->quote, 40);
    }

    /**
     * Named localizedQuote rather than quote() — Eloquent's __get() treats an
     * undefined attribute access as a possible relationship method call when
     * a same-named method exists, which throws for models missing that
     * attribute (e.g. `new Testimonial(['author_name' => 'X'])`).
     */
    public function localizedQuote(): ?string
    {
        return $this->localized('quote');
    }
}
