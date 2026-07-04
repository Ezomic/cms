@php
$websiteSchema = [
    '@context' => 'https://schema.org',
    '@type'    => 'WebSite',
    'name'     => $profile->name,
    'url'      => route('home'),
];
@endphp
<script type="application/ld+json">{!! json_encode($websiteSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>
