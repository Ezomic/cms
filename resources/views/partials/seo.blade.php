@php
    $canonicalParams = $canonicalParams ?? [];
    $canonical = route($canonicalRoute, $canonicalParams);
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
<link rel="alternate" hreflang="en" href="{{ $canonical }}">
<link rel="alternate" hreflang="nl" href="{{ $canonical }}">
<link rel="alternate" hreflang="x-default" href="{{ $canonical }}">
