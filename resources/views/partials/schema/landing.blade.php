@php
$areaServed = \App\Support\ServiceArea::places();
$landingUrl = localized_route('landing.enschede');
$serviceId = $landingUrl.'#service';
$landingSchema = [
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type'       => 'ProfessionalService',
            '@id'         => $serviceId,
            'name'        => $profile->name.', '.__('landing.breadcrumb'),
            'url'         => $landingUrl,
            'description' => __('landing.meta_description'),
            'image'       => route('og.home'),
            'email'       => $profile->email,
            'address'     => ['@type' => 'PostalAddress', 'addressLocality' => $profile->city, 'addressCountry' => 'NL'],
            'areaServed'  => $areaServed,
            'knowsAbout'  => ['Laravel', 'PHP', 'Vue.js', 'REST APIs', 'System integrations'],
            'founder'     => ['@type' => 'Person', 'name' => $profile->name, 'url' => route('home')],
            'hasOfferCatalog' => [
                '@type' => 'OfferCatalog',
                'name'  => __('landing.build_headline'),
                'itemListElement' => collect(__('landing.build_items'))->map(fn ($item) => [
                    '@type' => 'Offer',
                    'areaServed' => $areaServed,
                    'itemOffered' => ['@type' => 'Service', 'name' => $item['title'], 'description' => $item['body']],
                ])->all(),
            ],
        ],
        [
            '@type'      => 'WebPage',
            '@id'        => $landingUrl,
            'url'        => $landingUrl,
            'name'       => __('landing.meta_title'),
            'description' => __('landing.meta_description'),
            'inLanguage' => app()->getLocale(),
            'isPartOf'   => ['@type' => 'WebSite', 'name' => $profile->name, 'url' => route('home')],
            'about'      => ['@id' => $serviceId],
        ],
    ],
];
$sameAs = array_values(array_filter([$profile->linkedin_url, $profile->github_url]));
if ($sameAs) { $landingSchema['@graph'][0]['sameAs'] = $sameAs; }
@endphp
<script type="application/ld+json">{!! json_encode($landingSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>
