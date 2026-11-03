@php
$route = $landingRoute ?? 'landing.enschede';
$lang = $landingLang ?? 'landing';
$areaServed = \App\Support\ServiceArea::places();
$landingUrl = localized_route($route);
$serviceId = $landingUrl.'#service';
$landingSchema = [
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type'       => 'Service',
            '@id'         => $serviceId,
            'name'        => __($lang.'.breadcrumb'),
            'serviceType' => $landingServiceType ?? 'Laravel development',
            'url'         => $landingUrl,
            'description' => __($lang.'.meta_description'),
            'provider'    => ['@id' => route('home').'#organization'],
            'areaServed'  => $areaServed,
            'availableChannel' => [
                '@type' => 'ServiceChannel',
                'serviceLocation' => [
                    '@type'   => 'Place',
                    'name'    => $profile->city,
                    'address' => ['@type' => 'PostalAddress', 'addressLocality' => $profile->city, 'addressCountry' => 'NL'],
                ],
            ],
            'hasOfferCatalog' => [
                '@type' => 'OfferCatalog',
                'name'  => __($lang.'.build_headline'),
                'itemListElement' => collect(__($lang.'.build_items'))->map(fn ($item) => [
                    '@type' => 'Offer',
                    'itemOffered' => ['@type' => 'Service', 'name' => $item['title'], 'description' => $item['body']],
                ])->all(),
            ],
        ],
        [
            '@type'      => 'WebPage',
            '@id'        => $landingUrl,
            'url'        => $landingUrl,
            'name'       => __($lang.'.meta_title'),
            'description' => __($lang.'.meta_description'),
            'inLanguage' => app()->getLocale(),
            'isPartOf'   => ['@type' => 'WebSite', 'name' => $profile->name, 'url' => route('home')],
            'about'      => ['@id' => $serviceId],
        ],
    ],
];
@endphp
<script type="application/ld+json">{!! json_encode($landingSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>
