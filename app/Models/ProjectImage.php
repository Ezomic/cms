<?php

namespace App\Models;

use App\Concerns\HasLocalizedContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProjectImage extends Model
{
    use HasLocalizedContent;

    protected $fillable = [
        'path', 'sort_order', 'caption', 'caption_nl',
    ];

    public function localizedCaption(): ?string
    {
        return $this->localized('caption');
    }

    /**
     * Falls back to the project name, matching how a project's own image_alt
     * behaves, so a gallery image is never left with empty alt text.
     */
    public function altText(): string
    {
        return $this->localizedCaption() ?: (string) $this->project?->name;
    }

    /**
     * @return BelongsTo<Project, $this>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function imageUrl(): string
    {
        return asset('storage/'.$this->path);
    }
}
