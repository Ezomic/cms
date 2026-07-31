<?php

namespace App\Concerns;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::created(fn (self $model) => $model->recordActivity('created'));
        static::updated(fn (self $model) => $model->recordActivity('updated'));
        static::deleted(fn (self $model) => $model->recordActivity('deleted'));
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
            $value = $this->getAttribute($attribute);

            if (is_scalar($value) && (string) $value !== '') {
                return (string) $value;
            }
        }

        $key = $this->getKey();

        return class_basename($this).' #'.(is_scalar($key) ? (string) $key : '');
    }
}
