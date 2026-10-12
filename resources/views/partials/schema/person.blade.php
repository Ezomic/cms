@php
$areaServed = \App\Support\ServiceArea::places();
$personSchema = [
    '@context' => 'https://schema.org',
    '@type'    => 'Person',
    '@id'      => route('home').'#person',
    'name'     => $profile->name,
    'jobTitle' => $profile->tagline,
    'url'      => route('home'),
    'email'    => $profile->email,
    'address'  => ['@type' => 'PostalAddress', 'addressLocality' => $profile->city, 'addressCountry' => 'NL'],
    'knowsAbout' => ['Laravel', 'PHP', 'Vue.js', 'REST APIs', 'System integrations'],
    'hasOfferCatalog' => [
        '@type' => 'OfferCatalog',
        'name'  => 'Freelance web development services',
        'itemListElement' => [
            ['@type' => 'Offer', 'areaServed' => $areaServed, 'itemOffered' => ['@type' => 'Service', 'name' => 'Full-stack web application development', 'areaServed' => $areaServed]],
            ['@type' => 'Offer', 'areaServed' => $areaServed, 'itemOffered' => ['@type' => 'Service', 'name' => 'API design and integration', 'areaServed' => $areaServed]],
            ['@type' => 'Offer', 'areaServed' => $areaServed, 'itemOffered' => ['@type' => 'Service', 'name' => 'Technical advisory and code review', 'areaServed' => $areaServed]],
        ],
    ],
];
$sameAs = array_values(array_filter([$profile->linkedin_url, $profile->github_url]));
if ($sameAs) { $personSchema['sameAs'] = $sameAs; }
@endphp
<script type="application/ld+json">{!! json_encode($personSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>
