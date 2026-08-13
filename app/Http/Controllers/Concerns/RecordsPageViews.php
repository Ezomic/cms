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

        PageView::create([
            'path' => '/'.ltrim(request()->path(), '/'),
            'referrer_host' => $this->referrerHost(request()->headers->get('referer')),
        ]);
    }

    /**
     * The host, never the full referrer.
     *
     * A referrer URL can carry search terms, session ids and usernames in its
     * query string, so storing it whole would put personal data in the
     * database and drag the site into consent-banner territory. The host alone
     * answers the only question being asked: where did this visit come from.
     * Own-domain referrals are internal navigation, not a traffic source, and
     * are recorded as direct.
     */
    private function referrerHost(?string $referrer): string
    {
        if ($referrer === null || trim($referrer) === '') {
            return PageView::DIRECT;
        }

        $host = parse_url(trim($referrer), PHP_URL_HOST);

        if (! is_string($host) || $host === '') {
            return PageView::DIRECT;
        }

        $host = Str::of($host)->lower()->ltrim('www.')->toString();

        return $host === Str::of((string) parse_url(config()->string('app.url'), PHP_URL_HOST))->lower()->ltrim('www.')->toString()
            ? PageView::DIRECT
            : Str::limit($host, 255, '');
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
