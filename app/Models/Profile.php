<?php

namespace App\Models;

use App\Concerns\BustsHomeCache;
use App\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use BustsHomeCache, LogsActivity;

    protected $table = 'profile';

    protected $fillable = [
        'name', 'city', 'tagline', 'hero_headline', 'hero_subtext',
        'available', 'email', 'linkedin_url', 'github_url',
        'rate', 'availability_from', 'kvk_number',
        'meta_title', 'meta_description',
    ];

    protected $casts = [
        'available' => 'boolean',
    ];

    /**
     * There is always exactly one profile row (id = 1).
     * This creates it with defaults the first time it's needed.
     */
    public static function current(): self
    {
        return static::firstOrCreate(['id' => 1]);
    }

    public function activityLabel(): string
    {
        return 'Profile';
    }
}
