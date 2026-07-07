<?php

namespace App\Concerns;

trait HasLocalizedContent
{
    protected function localized(string $field): ?string
    {
        if (app()->getLocale() === 'nl' && filled($this->{"{$field}_nl"} ?? null)) {
            return $this->{"{$field}_nl"};
        }

        return $this->{$field};
    }
}
