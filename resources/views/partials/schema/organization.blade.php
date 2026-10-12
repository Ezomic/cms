@php
$organizationSchema = [
    '@context' => 'https://schema.org',
    '@type'    => 'ProfessionalService',
    '@id'      => route('home').'#organization',
    'name'     => 'Thijssen Software',
    'url'      => route('home'),
    'logo'     => ['@type' => 'ImageObject', 'url' => url('/icon-512.png'), 'width' => 512, 'height' => 512],
    'founder'  => ['@id' => route('home').'#person'],
    'address'  => ['@type' => 'PostalAddress', 'addressLocality' => $profile->city, 'addressCountry' => 'NL'],
];
@endphp
<script type="application/ld+json">{!! json_encode($organizationSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}</script>
