@extends('layouts.public')

@section('head')
@include('partials.seo', [
    'title' => $project->metaTitle().' — '.$profile->name,
    'description' => $project->metaDescription(),
    'canonicalRoute' => 'project.show',
    'canonicalParams' => ['project' => $project->slug],
    'ogType' => 'article',
    'ogImage' => route('og.project', $project->slug),
])
@include('partials.schema.creative-work')
@include('partials.schema.breadcrumbs', ['items' => [
    ['name' => __('site.breadcrumb_home'), 'url' => localized_route('home')],
    ['name' => __('site.breadcrumb_work'), 'url' => localized_route('work.index')],
    ['name' => $project->name, 'url' => localized_route('project.show', $project->slug)],
]])
@endsection

@push('styles')
<style>
  .cs{max-width:760px;margin:0 auto;padding:0 28px;}
  .cs-hero{padding:44px 0 4px;}
  .breadcrumb{font-family:var(--mono);font-size:12px;color:var(--muted);margin-bottom:18px;}
  .breadcrumb a:hover{color:var(--primary);}
  .cs-hero .eyebrow{display:block;margin-bottom:14px;}
  .cs-hero h1{font-size:clamp(2.2rem,5vw,3.2rem);line-height:1.04;}
  .cs-meta{display:flex;gap:26px;flex-wrap:wrap;margin-top:22px;padding:16px 0;border-top:1px solid var(--line);border-bottom:1px solid var(--line);}
  .cs-meta dt{font-family:var(--mono);font-size:10px;letter-spacing:.1em;text-transform:uppercase;color:var(--faint);}
  .cs-meta dd{font-family:var(--geo);font-weight:600;font-size:14px;margin-top:4px;}
  .cover{width:100%;aspect-ratio:16/9;max-height:480px;object-fit:cover;border:1px solid var(--line);border-radius:var(--r-lg);margin:26px 0;}
  .github-link{display:inline-block;margin-top:14px;font-family:var(--mono);font-size:13px;color:var(--muted);}
  .github-link:hover{color:var(--primary);}
  .outcome-callout{background:var(--soft);border-radius:var(--r);padding:18px 20px;margin:26px 0;font-size:15px;color:var(--deep);}
  .outcome-callout strong{display:block;font-family:var(--mono);font-size:10.5px;text-transform:uppercase;letter-spacing:.08em;color:var(--primary);margin-bottom:6px;}
  .body-content{padding:8px 0 20px;font-size:17px;color:#3a352d;line-height:1.7;}
  .body-content p{margin-bottom:1.2em;}
  .body-content h2{font-family:var(--geo);font-weight:700;font-size:1.4rem;letter-spacing:-.01em;margin:1.6em 0 .5em;color:var(--ink);}
  .body-content h3{font-family:var(--geo);font-weight:700;font-size:1.12rem;margin:1.5em 0 .4em;color:var(--ink);}
  .body-content ul,.body-content ol{padding-left:1.4em;margin-bottom:1.2em;}
  .body-content li{margin-bottom:.5em;}
  .body-content code{font-family:var(--mono);font-size:.88em;background:var(--soft);color:var(--deep);padding:2px 6px;border-radius:4px;}
  .body-content strong{font-weight:600;color:var(--ink);}
  .gallery-label{font-family:var(--mono);font-size:11px;text-transform:uppercase;letter-spacing:.08em;color:var(--primary);margin:0 0 14px;}
  .gallery{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:14px;margin:0 0 40px;}
  .gallery img{width:100%;aspect-ratio:16/10;object-fit:cover;border:1px solid var(--line);border-radius:var(--r);}
  .cs-cta{padding:32px 0 72px;border-top:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;}
  .cs-cta .lab{font-family:var(--mono);font-size:12px;color:var(--muted);margin-bottom:6px;}
  .cs-cta h3{font-size:1.2rem;}
</style>
@endpush

@section('content')
<div class="cs">
  <header class="cs-hero">
    <div class="breadcrumb"><a href="{{ localized_route('work.index') }}">{{ __('site.breadcrumb_work') }}</a> / {{ $project->name }}</div>
    <span class="eyebrow">{{ __('site.project_eyebrow') }}</span>
    <h1>{{ $project->name }}</h1>
    <dl class="cs-meta">
      @if ($project->client_name)<div><dt>{{ __('site.breadcrumb_work') }}</dt><dd>{{ $project->client_name }}</dd></div>@endif
      @if ($project->year)<div><dt>Year</dt><dd>{{ $project->year }}</dd></div>@endif
      @if ($project->tagList())<div><dt>Stack</dt><dd>{{ implode(' · ', array_slice($project->tagList(), 0, 4)) }}</dd></div>@endif
    </dl>
    @if ($project->github_url)
      <a class="github-link" href="{{ $project->github_url }}" target="_blank" rel="noopener">{{ __('site.project_github_link') }}</a>
    @endif
  </header>

  @if ($project->image)
    <img class="cover" src="{{ $project->imageUrl() }}" alt="{{ $project->imageAlt() }}" fetchpriority="high" decoding="async">
  @endif

  @if ($project->localizedOutcome())
    <div class="outcome-callout">
      <strong>{{ __('site.project_result_label') }}</strong>
      {{ $project->localizedOutcome() }}
    </div>
  @endif

  <div class="body-content">
    {!! $project->localizedBody() !!}
  </div>

  @if ($project->images->isNotEmpty())
    <div class="gallery-label">{{ __('site.project_gallery_label') }}</div>
    <div class="gallery">
      @foreach ($project->images as $image)
        <img src="{{ $image->imageUrl() }}" alt="{{ $project->name }}" loading="lazy" decoding="async">
      @endforeach
    </div>
  @endif

  <div class="cs-cta">
    <div>
      <div class="lab">{{ __('site.project_cta_lead') }}</div>
      <h3>{{ __('site.project_cta_headline') }}</h3>
    </div>
    <a class="btn pri" href="{{ localized_route('home') }}?ref={{ urlencode($project->slug) }}#contact">{{ __('site.project_cta_button') }}</a>
  </div>
</div>
@endsection
