<?= '<?xml version="1.0" encoding="UTF-8"?>' ?>

<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
@foreach ($entries as $entry)
@foreach (['en', 'nl'] as $locale)
<url>
<loc>{{ localized_route($entry['route'], $entry['params'], $locale) }}</loc>
@if ($entry['lastmod'])
<lastmod>{{ $entry['lastmod']->toAtomString() }}</lastmod>
@endif
<xhtml:link rel="alternate" hreflang="en" href="{{ localized_route($entry['route'], $entry['params'], 'en') }}"/>
<xhtml:link rel="alternate" hreflang="nl" href="{{ localized_route($entry['route'], $entry['params'], 'nl') }}"/>
<xhtml:link rel="alternate" hreflang="x-default" href="{{ localized_route($entry['route'], $entry['params'], 'en') }}"/>
</url>
@endforeach
@endforeach
</urlset>
