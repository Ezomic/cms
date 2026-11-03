<?php

namespace App\Support;

use Carbon\CarbonImmutable;

/**
 * The keyword and audience landing pages that share one controller action and
 * one view. Copy lives in lang/{locale}/pages/{key}.php; this holds only what
 * is not translated: the slugs, the proof links and the copy date.
 *
 * The Enschede and remote pages predate this and keep their own views.
 */
final class LandingPages
{
    /**
     * The landing copy lives in lang files, so no updated_at tracks it.
     * Bump this date whenever a page's copy changes (sitemap lastmod).
     */
    public const COPY_UPDATED_AT = '2026-11-03';

    /**
     * Proof entries are keyed into `proof_items` of the page's lang file. A
     * `url` links to a public repository, a `project` slug links to the case
     * study, and an entry with neither is private work described generically.
     *
     * @return array<string, array{slug: array{en: string, nl: string}, proof: list<array{key: string, url?: string, project?: string}>}>
     */
    public static function all(): array
    {
        return [
            'go' => [
                'slug' => ['en' => 'freelance-golang-developer', 'nl' => 'freelance-go-ontwikkelaar'],
                'proof' => [
                    ['key' => 'filebox', 'url' => 'https://github.com/Ezomic/filebox'],
                    ['key' => 'streambox', 'url' => 'https://github.com/Ezomic/streambox'],
                ],
            ],
            'python' => [
                'slug' => ['en' => 'freelance-python-developer', 'nl' => 'freelance-python-ontwikkelaar'],
                'proof' => [
                    ['key' => 'sync'],
                    ['key' => 'transcripts'],
                    ['key' => 'tooling'],
                ],
            ],
            'dotnet' => [
                'slug' => ['en' => 'freelance-csharp-dotnet-developer', 'nl' => 'freelance-csharp-dotnet-ontwikkelaar'],
                'proof' => [
                    ['key' => 'overdub', 'url' => 'https://github.com/Ezomic/overdub'],
                    ['key' => 'core', 'url' => 'https://github.com/Ezomic/valheim-core'],
                ],
            ],
        ];
    }

    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return array_keys(self::all());
    }

    /**
     * @return list<string>
     */
    public static function projectSlugs(string $key): array
    {
        return array_values(array_filter(array_column(self::all()[$key]['proof'], 'project')));
    }

    public static function copyUpdatedAt(): CarbonImmutable
    {
        return CarbonImmutable::parse(self::COPY_UPDATED_AT);
    }
}
