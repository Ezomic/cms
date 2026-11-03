@extends('layouts.public')

@section('head')
@include('partials.seo', [
    'title' => __($lang.'.meta_title'),
    'description' => __($lang.'.meta_description'),
    'canonicalRoute' => 'landing.'.$page,
])
@include('partials.schema.organization')
@include('partials.schema.landing', ['landingRoute' => 'landing.'.$page, 'landingLang' => $lang, 'landingServiceType' => __($lang.'.service_type')])
@include('partials.schema.breadcrumbs', ['items' => [
    ['name' => __('site.breadcrumb_home'), 'url' => localized_route('home')],
    ['name' => __($lang.'.breadcrumb'), 'url' => localized_route('landing.'.$page)],
]])
@endsection

@push('styles')
<style>
  .hero{padding:74px 0 60px;border-bottom:1px solid var(--line);}
  .hero .grid{display:grid;grid-template-columns:1.55fr .95fr;gap:44px;align-items:center;}
  .hero .eyebrow{display:block;margin:0 0 14px;}
  .hero h1{font-size:clamp(2.2rem,5vw,3.6rem);line-height:1.05;max-width:18ch;}
  .hero .sub{margin-top:22px;font-size:18px;color:var(--muted);max-width:50ch;}
  .hero .hero-book{margin-top:30px;}
  .hero .acts{margin-top:16px;display:flex;gap:12px;flex-wrap:wrap;align-items:center;}
  .facts{background:var(--surface);border:1px solid var(--line);border-radius:var(--r-lg);padding:22px 24px;}
  .facts dt{font-family:var(--mono);font-size:10.5px;letter-spacing:.1em;text-transform:uppercase;color:var(--faint);margin-top:16px;}
  .facts dt:first-child{margin-top:0;}
  .facts dd{font-family:var(--geo);font-weight:600;font-size:15px;margin-top:3px;}
  @media(max-width:820px){.hero{padding:48px 0 44px;}.hero .grid{grid-template-columns:1fr;gap:28px;}}

  .split{display:grid;grid-template-columns:.8fr 1.4fr;gap:44px;align-items:start;}
  .split .section-head{margin-bottom:0;}
  .split h2,.cta h2{line-height:1.15;}
  .prose p{font-size:17px;color:#3a352d;line-height:1.7;margin-bottom:1.1em;}
  .prose p:last-child{margin-bottom:0;}
  .lede{color:var(--muted);max-width:60ch;margin-top:-14px;margin-bottom:30px;}
  @media(max-width:820px){.split{grid-template-columns:1fr;gap:22px;}}

  .svc{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:18px;}
  .sc{background:var(--surface);border:1px solid var(--line);border-radius:var(--r-lg);padding:24px;}
  .sc .n{width:32px;height:32px;border-radius:9px;background:var(--soft);color:var(--deep);font-family:var(--geo);font-weight:700;font-size:15px;display:flex;align-items:center;justify-content:center;margin-bottom:16px;}
  .sc h3{font-size:17px;margin-bottom:9px;}
  .sc p{font-size:14px;color:var(--muted);}

  .proc{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:22px;}
  .step{padding-top:18px;border-top:2px solid var(--primary);}
  .step .num{font-family:var(--mono);font-size:12px;color:var(--primary);margin-bottom:10px;}
  .step h3{font-size:16px;margin-bottom:7px;}
  .step p{font-size:13.5px;color:var(--muted);}

  .proof-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:18px;}
  .proof{background:var(--surface);border:1px solid var(--line);border-radius:var(--r-lg);padding:24px;display:flex;flex-direction:column;gap:10px;}
  .proof h3{font-size:17px;}
  .proof p{font-size:14px;color:var(--muted);}
  .proof .links{margin-top:auto;padding-top:6px;display:flex;flex-wrap:wrap;gap:6px 18px;font-family:var(--mono);font-size:13px;}
  .proof .links a{color:var(--primary);}
  .proof .links a:hover{color:var(--deep);}
  .proof .private{color:var(--faint);}
  .more{display:inline-block;margin-top:26px;font-family:var(--mono);font-size:13px;color:var(--primary);}
  .more:hover{color:var(--deep);}

  section.cta{border-bottom:none;}
  .cta-inner{display:flex;justify-content:space-between;align-items:center;gap:34px;flex-wrap:wrap;}
  .cta h2{font-size:clamp(1.8rem,3.6vw,2.6rem);max-width:18ch;margin:12px 0 16px;}
  .cta .lead{color:var(--muted);font-size:17px;max-width:46ch;}
  .cta .acts{display:flex;gap:12px;flex-wrap:wrap;}
</style>
@endpush

@section('content')

<header class="hero">
  <div class="wrap">
    <div class="grid">
      <div>
        <span class="eyebrow">{{ __($lang.'.hero_eyebrow') }}</span>
        <h1>{{ __($lang.'.hero_headline') }}</h1>
        <p class="sub">{{ __($lang.'.hero_lead') }}</p>
        <div class="hero-book">@include('partials.book-call', ['contactHref' => localized_route('home').'#contact'])</div>
        <div class="acts">
          <a class="btn pri" href="{{ localized_route('home') }}#contact">{{ __($lang.'.hero_primary') }}</a>
          <a class="btn sec" href="{{ localized_route('work.index') }}">{{ __($lang.'.hero_secondary') }}</a>
        </div>
      </div>
      <dl class="facts">
        @foreach (__($lang.'.facts') as $fact)
          <dt>{{ $fact['label'] }}</dt>
          <dd>{{ $fact['value'] }}</dd>
        @endforeach
      </dl>
    </div>
  </div>
</header>

<section class="block">
  <div class="wrap split">
    <div class="section-head"><span class="eyebrow">{{ __($lang.'.who_label') }}</span><h2>{{ __($lang.'.who_headline') }}</h2></div>
    <div class="prose">
      <p>{{ __($lang.'.who_p1') }}</p>
      <p>{{ __($lang.'.who_p2') }}</p>
    </div>
  </div>
</section>

<section class="block">
  <div class="wrap">
    <div class="section-head"><span class="eyebrow">{{ __($lang.'.build_label') }}</span><h2>{{ __($lang.'.build_headline') }}</h2></div>
    <p class="lede">{{ __($lang.'.build_intro') }}</p>
    <div class="svc">
      @foreach (__($lang.'.build_items') as $i => $item)
        <div class="sc">
          <div class="n">{{ $i + 1 }}</div>
          <h3>{{ $item['title'] }}</h3>
          <p>{{ $item['body'] }}</p>
        </div>
      @endforeach
    </div>
    @include('partials.ai-note')
  </div>
</section>

<section class="block">
  <div class="wrap">
    <div class="section-head"><span class="eyebrow">{{ __($lang.'.steps_label') }}</span><h2>{{ __($lang.'.steps_headline') }}</h2></div>
    <p class="lede">{{ __($lang.'.steps_intro') }}</p>
    <div class="proc">
      @foreach (__($lang.'.steps_items') as $i => $item)
        <div class="step"><div class="num">0{{ $i + 1 }}</div><h3>{{ $item['title'] }}</h3><p>{{ $item['body'] }}</p></div>
      @endforeach
    </div>
  </div>
</section>

@if ($proof->isNotEmpty())
<section class="block">
  <div class="wrap">
    <div class="section-head"><span class="eyebrow">{{ __($lang.'.proof_label') }}</span><h2>{{ __($lang.'.proof_headline') }}</h2></div>
    <p class="lede">{{ __($lang.'.proof_intro') }}</p>
    <div class="proof-grid">
      @foreach ($proof as $entry)
        <div class="proof">
          <h3>{{ __($lang.'.proof_items.'.$entry['key'].'.title') }}</h3>
          <p>{{ __($lang.'.proof_items.'.$entry['key'].'.body') }}</p>
          <div class="links">
            @if ($entry['url'])<a href="{{ $entry['url'] }}" target="_blank" rel="noopener noreferrer">{{ __('site.landing_repo_link') }}</a>@endif
            @if ($entry['project'])<a href="{{ localized_route('project.show', $entry['project']) }}">{{ __('site.landing_case_link') }}</a>@endif
            @if (! $entry['url'] && ! $entry['project'])<span class="private">{{ __('site.landing_private_note') }}</span>@endif
          </div>
        </div>
      @endforeach
    </div>
    <a class="more" href="{{ localized_route('work.index') }}">{{ __('site.landing_all_cases') }}</a>
  </div>
</section>
@endif

<section class="block cta">
  <div class="wrap cta-inner">
    <div>
      <span class="eyebrow">{{ __($lang.'.cta_label') }}</span>
      <h2>{{ __($lang.'.cta_headline') }}</h2>
      <p class="lead">{{ __($lang.'.cta_lead') }}</p>
    </div>
    <div class="acts">
      <a class="btn pri" href="{{ localized_route('home') }}#contact">{{ __($lang.'.cta_primary') }}</a>
      <a class="btn sec" href="{{ localized_route('cv') }}">{{ __('site.landing_cv') }}</a>
    </div>
  </div>
</section>

@endsection
