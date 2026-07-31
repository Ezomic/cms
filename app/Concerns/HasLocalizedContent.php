<?php

namespace App\Concerns;

trait HasLocalizedContent
{
    protected function localized(string $field): ?string
    {
        if (app()->getLocale() === 'nl') {
            $dutch = $this->getAttribute("{$field}_nl");

            if (is_string($dutch) && $dutch !== '') {
                return $dutch;
            }
        }

        $value = $this->getAttribute($field);

        return is_string($value) ? $value : null;
    }
}
