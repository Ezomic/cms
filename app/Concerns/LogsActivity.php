<?php

namespace App\Concerns;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::created(fn ($model) => $model->recordActivity('created'));
        static::updated(fn ($model) => $model->recordActivity('updated'));
        static::deleted(fn ($model) => $model->recordActivity('deleted'));
    }

    protected function recordActivity(string $action): void
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'subject_type' => class_basename($this),
            'subject_label' => $this->activityLabel(),
        ]);
    }

    public function activityLabel(): string
    {
        foreach (['name', 'author_name', 'title'] as $attribute) {
            if (! empty($this->{$attribute})) {
                return (string) $this->{$attribute};
            }
        }

        return class_basename($this).' #'.$this->getKey();
    }
}
