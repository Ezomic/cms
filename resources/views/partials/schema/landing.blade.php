@php
$areaServed = \App\Support\ServiceArea::places();
$landingUrl = localized_route('landing.enschede');
$serviceId = $landingUrl.'#service';
$landingSchema = [
    '@context' => 'https://schema.org',
    '@graph' => [
        [
            '@type'       => 'Service',
            '@id'         => $serviceId,
            'name'        => __('landing.breadcrumb'),
            'serviceType' => 'Laravel development',
            'url'         => $landingUrl,
            'description' => __('landing.meta_description'),
            'provider'    => ['@id' => route('home').'#organization'],
            'areaServed'  => $areaServed,
            'hasOfferCatalog' => [
                '@type' => 'OfferCatalog',
                'name'  => __('landing.build_headline'),
                'itemListElement' => collect(__('landing.build_items'))->map(fn ($item) => [
                    '@type' => 'Offer',
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
@endphp
<script type="application/ld+json">{!! json_encode($landingSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>
