@php
$personSchema = [
    '@context' => 'https://schema.org',
    '@type'    => 'Person',
    'name'     => $profile->name,
    'jobTitle' => $profile->tagline,
    'url'      => route('home'),
    'email'    => $profile->email,
    'address'  => ['@type' => 'PostalAddress', 'addressLocality' => $profile->city, 'addressCountry' => 'NL'],
    'hasOfferCatalog' => [
        '@type' => 'OfferCatalog',
        'name'  => 'Freelance web development services',
        'itemListElement' => [
            ['@type' => 'Offer', 'itemOffered' => ['@type' => 'Service', 'name' => 'Full-stack web application development']],
            ['@type' => 'Offer', 'itemOffered' => ['@type' => 'Service', 'name' => 'API design and integration']],
            ['@type' => 'Offer', 'itemOffered' => ['@type' => 'Service', 'name' => 'Technical advisory and code review']],
        ],
    ],
];
$sameAs = array_values(array_filter([$profile->linkedin_url, $profile->github_url]));
if ($sameAs) { $personSchema['sameAs'] = $sameAs; }
@endphp
<script type="application/ld+json">{!! json_encode($personSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>
