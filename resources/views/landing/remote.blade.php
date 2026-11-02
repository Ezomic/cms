@extends('layouts.public')

@section('head')
@include('partials.seo', [
    'title' => __('remote.meta_title'),
    'description' => __('remote.meta_description'),
    'canonicalRoute' => 'landing.remote',
])
@include('partials.schema.organization')
@include('partials.schema.landing', ['landingRoute' => 'landing.remote', 'landingLang' => 'remote'])
@include('partials.schema.breadcrumbs', ['items' => [
    ['name' => __('site.breadcrumb_home'), 'url' => localized_route('home')],
    ['name' => __('remote.breadcrumb'), 'url' => localized_route('landing.remote')],
]])
@endsection

@push('styles')
<style>
  .hero{padding:74px 0 60px;border-bottom:1px solid var(--line);}
  .hero .grid{display:grid;grid-template-columns:1.55fr .95fr;gap:44px;align-items:center;}
  .hero .eyebrow{display:block;margin:0 0 14px;}
  .hero h1{font-size:clamp(2.2rem,5vw,3.6rem);line-height:1.05;max-width:18ch;}
  .hero .sub{margin-top:22px;font-size:18px;color:var(--muted);max-width:50ch;}
  .hero .acts{margin-top:30px;display:flex;gap:12px;flex-wrap:wrap;align-items:center;}
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

  .svc{display:grid;grid-template-columns:repeat(6,1fr);gap:18px;}
  .sc{grid-column:span 2;}
  .sc:nth-child(n+4){grid-column:span 3;}
  .sc{background:var(--surface);border:1px solid var(--line);border-radius:var(--r-lg);padding:24px;}
  .sc .n{width:32px;height:32px;border-radius:9px;background:var(--soft);color:var(--deep);font-family:var(--geo);font-weight:700;font-size:15px;display:flex;align-items:center;justify-content:center;margin-bottom:16px;}
  .sc h3{font-size:17px;margin-bottom:9px;}
  .sc p{font-size:14px;color:var(--muted);}
  @media(max-width:900px){.svc{grid-template-columns:1fr 1fr;}.sc,.sc:nth-child(n+4){grid-column:auto;}.sc:last-child{grid-column:1/-1;}}
  @media(max-width:600px){.svc{grid-template-columns:1fr;}}

  .proc{display:grid;grid-template-columns:repeat(4,1fr);gap:22px;}
  .step{padding-top:18px;border-top:2px solid var(--primary);}
  .step .num{font-family:var(--mono);font-size:12px;color:var(--primary);margin-bottom:10px;}
  .step h3{font-size:16px;margin-bottom:7px;}
  .step p{font-size:13.5px;color:var(--muted);}
  .after{margin-top:30px;font-size:16px;color:var(--muted);}
  @media(max-width:820px){.proc{grid-template-columns:1fr 1fr;gap:24px;}}
  @media(max-width:460px){.proc{grid-template-columns:1fr;}}

  .proof-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:18px;}
  .proof-grid .pcard .in{padding:22px;}
  .proof-grid .pcard .outcome{font-size:12.5px;line-height:1.5;}
  .more{display:inline-block;margin-top:26px;font-family:var(--mono);font-size:13px;color:var(--primary);}
  .more:hover{color:var(--deep);}
  @media(max-width:900px){.proof-grid{grid-template-columns:1fr;}}

  .areas{list-style:none;margin:22px 0 0;padding:0;display:grid;gap:10px;}
  .areas li{background:var(--surface);border:1px solid var(--line);border-radius:var(--r-lg);padding:14px 18px;font-family:var(--geo);font-weight:600;font-size:15px;}

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
        <span class="eyebrow">{{ __('remote.hero_eyebrow') }}</span>
        <h1>{{ __('remote.hero_headline') }}</h1>
        <p class="sub">{{ __('remote.hero_lead') }}</p>
        <div class="acts">
          <a class="btn pri" href="{{ localized_route('home') }}#contact">{{ __('remote.hero_primary') }}</a>
          <a class="btn sec" href="{{ localized_route('work.index') }}">{{ __('remote.hero_secondary') }}</a>
        </div>
      </div>
      <dl class="facts">
        @foreach (['based', 'works', 'stack', 'experience'] as $fact)
          <dt>{{ __('remote.fact_'.$fact.'_label') }}</dt>
          <dd>{{ __('remote.fact_'.$fact.'_value') }}</dd>
        @endforeach
      </dl>
    </div>
  </div>
</header>

<section class="block">
  <div class="wrap split">
    <div class="section-head"><span class="eyebrow">{{ __('remote.who_label') }}</span><h2>{{ __('remote.who_headline') }}</h2></div>
    <div class="prose">
      <p>{{ __('remote.who_p1') }}</p>
      <p>{{ __('remote.who_p2') }}</p>
    </div>
  </div>
</section>

<section class="block">
  <div class="wrap">
    <div class="section-head"><span class="eyebrow">{{ __('remote.remote_label') }}</span><h2>{{ __('remote.remote_headline') }}</h2></div>
    <p class="lede">{{ __('remote.remote_intro') }}</p>
    <div class="proc">
      @foreach (__('remote.remote_items') as $i => $item)
        <div class="step"><div class="num">0{{ $i + 1 }}</div><h3>{{ $item['title'] }}</h3><p>{{ $item['body'] }}</p></div>
      @endforeach
    </div>
  </div>
</section>

<section class="block">
  <div class="wrap">
    <div class="section-head"><span class="eyebrow">{{ __('remote.build_label') }}</span><h2>{{ __('remote.build_headline') }}</h2></div>
    <p class="lede">{{ __('remote.build_intro') }}</p>
    <div class="svc">
      @foreach (__('remote.build_items') as $i => $item)
        <div class="sc">
          <div class="n">{{ $i + 1 }}</div>
          <h3>{{ $item['title'] }}</h3>
          <p>{{ $item['body'] }}</p>
        </div>
      @endforeach
    </div>
  </div>
</section>

@if ($proof->isNotEmpty())
<section class="block">
  <div class="wrap">
    <div class="section-head"><span class="eyebrow">{{ __('remote.proof_label') }}</span><h2>{{ __('remote.proof_headline') }}</h2></div>
    <p class="lede">{{ __('remote.proof_intro') }}</p>
    <div class="proof-grid">
      @foreach ($proof as $project)
        <a class="pcard" href="{{ localized_route('project.show', $project->slug) }}">
          <div class="in">
            <h3>{{ $project->name }}</h3>
            <div class="meta">{{ $project->year }}{{ $project->year && $project->client_name ? ' · ' : '' }}{{ $project->client_name }}</div>
            @if ($project->localizedOutcome())<div class="outcome">↳ {{ $project->localizedOutcome() }}</div>@endif
            @if (count($project->tagList()))
              <div class="tags">@foreach ($project->tagList() as $t)<span class="tag">{{ $t }}</span>@endforeach</div>
            @endif
          </div>
        </a>
      @endforeach
    </div>
    <a class="more" href="{{ localized_route('work.index') }}">{{ __('remote.proof_all') }}</a>
  </div>
</section>
@endif

<section class="block">
  <div class="wrap split">
    <div class="section-head"><span class="eyebrow">{{ __('remote.where_label') }}</span><h2>{{ __('remote.where_headline') }}</h2></div>
    <div>
      <div class="prose">
        <p>{{ __('remote.where_p1') }}</p>
      </div>
      <ul class="areas">@foreach (__('remote.where_list') as $area)<li>{{ $area }}</li>@endforeach</ul>
    </div>
  </div>
</section>

<section class="block cta">
  <div class="wrap cta-inner">
    <div>
      <span class="eyebrow">{{ __('remote.cta_label') }}</span>
      <h2>{{ __('remote.cta_headline') }}</h2>
      <p class="lead">{{ __('remote.cta_lead') }}</p>
    </div>
    <div class="acts">
      <a class="btn pri" href="{{ localized_route('home') }}#contact">{{ __('remote.cta_primary') }}</a>
      <a class="btn sec" href="{{ localized_route('cv') }}">{{ __('remote.cta_secondary') }}</a>
    </div>
  </div>
</section>

@endsection
