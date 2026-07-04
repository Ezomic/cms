@php
    $canonicalParams = $canonicalParams ?? [];
    $canonical = localized_route($canonicalRoute, $canonicalParams);
    $enUrl = localized_route($canonicalRoute, $canonicalParams, 'en');
    $nlUrl = localized_route($canonicalRoute, $canonicalParams, 'nl');
    $ogImage = $ogImage ?? route('og.home');
@endphp
<title>{{ $title }}</title>
<meta name="description" content="{{ $description }}">
<meta property="og:title" content="{{ $title }}">
<meta property="og:description" content="{{ $description }}">
<meta property="og:type" content="{{ $ogType ?? 'website' }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:image" content="{{ $ogImage }}">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:locale" content="{{ app()->getLocale() === 'nl' ? 'nl_NL' : 'en_US' }}">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:image" content="{{ $ogImage }}">
<link rel="canonical" href="{{ $canonical }}">
<link rel="alternate" hreflang="en" href="{{ $enUrl }}">
<link rel="alternate" hreflang="nl" href="{{ $nlUrl }}">
<link rel="alternate" hreflang="x-default" href="{{ $enUrl }}">
