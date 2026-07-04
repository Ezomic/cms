<?php

use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Str;

if (! function_exists('localized_route')) {
    function localized_route(string $name, mixed $parameters = [], ?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return route(($locale === 'nl' ? 'nl.' : '').$name, $parameters);
    }
}

if (! function_exists('alternate_locale_url')) {
    function alternate_locale_url(string $locale): string
    {
        $route = request()->route();

        if (! $route instanceof RoutingRoute || $route->getName() === null) {
            return localized_route('home', [], $locale);
        }

        return localized_route(Str::after($route->getName(), 'nl.'), $route->parameters(), $locale);
    }
}
