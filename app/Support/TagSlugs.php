<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Collection;

final class TagSlugs
{
    private const STRIPPED = ". \t\n\r\0\x0B";

    private const FALLBACK = 'tag';

    /**
     * @param  array<string, string>  $slugByTag
     * @param  array<string, string>  $tagBySlug
     */
    private function __construct(private readonly array $slugByTag, private readonly array $tagBySlug) {}

    /**
     * @param  Collection<int, string>  $tags
     */
    public static function from(Collection $tags): self
    {
        $names = $tags->unique()->sort(SORT_STRING)->values()->all();

        $slugByTag = [];
        $tagBySlug = [];

        $claim = function (string $tag, string $base) use (&$slugByTag, &$tagBySlug): void {
            $slug = $base;

            for ($n = 2; isset($tagBySlug[$slug]); $n++) {
                $slug = $base.'-'.$n;
            }

            $slugByTag[$tag] = $slug;
            $tagBySlug[$slug] = $tag;
        };

        $unchanged = fn (string $tag): bool => $tag !== '' && self::base($tag) === $tag;

        foreach (array_filter($names, $unchanged) as $tag) {
            $claim($tag, $tag);
        }

        foreach (array_filter($names, fn (string $tag): bool => ! $unchanged($tag)) as $tag) {
            $claim($tag, self::candidate($tag));
        }

        return new self($slugByTag, $tagBySlug);
    }

    public function slug(string $tag): string
    {
        return $this->slugByTag[$tag] ?? self::candidate($tag);
    }

    public function tag(string $slug): ?string
    {
        return $this->tagBySlug[$slug] ?? null;
    }

    private static function base(string $tag): string
    {
        return ltrim($tag, self::STRIPPED);
    }

    private static function candidate(string $tag): string
    {
        $base = self::base($tag);

        return $base === '' ? self::FALLBACK : $base;
    }
}
