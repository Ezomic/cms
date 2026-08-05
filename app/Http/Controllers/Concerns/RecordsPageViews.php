<?php

namespace App\Http\Controllers\Concerns;

use App\Models\PageView;
use Illuminate\Support\Str;

trait RecordsPageViews
{
    /**
     * robots.txt and the sitemap actively invite crawlers, so a large share of
     * public traffic is automated. Counting it makes the dashboard totals, the
     * top paths and the page_view_totals rollup measure bots as much as people,
     * and the rollup makes that permanent.
     */
    protected function recordPageView(): void
    {
        if ($this->isAutomatedTraffic(request()->userAgent())) {
            return;
        }

        PageView::create(['path' => '/'.ltrim(request()->path(), '/')]);
    }

    /**
     * A missing user agent counts as automated: browsers always send one, so
     * an absent header means a script, a health check or a scraper hiding.
     */
    private function isAutomatedTraffic(?string $userAgent): bool
    {
        if ($userAgent === null || trim($userAgent) === '') {
            return true;
        }

        $signatures = [
            'bot', 'crawl', 'spider', 'slurp', 'scrape', 'fetcher', 'monitor', 'uptime',
            'curl', 'wget', 'python-requests', 'http-client', 'httpx', 'axios', 'okhttp',
            'headless', 'lighthouse', 'pagespeed', 'phantomjs', 'puppeteer', 'playwright',
            'facebookexternalhit', 'whatsapp', 'telegram', 'discord', 'embedly', 'preview',
            'ahrefs', 'semrush', 'mj12', 'dotbot', 'petal', 'yandex', 'baidu', 'applebot',
        ];

        // Spaces stripped so "Google Bot" is caught alongside "Googlebot".
        $normalised = str_replace(' ', '', mb_strtolower($userAgent));

        return Str::contains($normalised, $signatures);
    }
}
