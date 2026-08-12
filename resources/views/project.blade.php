@extends('layouts.public')

@section('head')
@include('partials.seo', [
    'title' => $project->metaTitle().' · '.$profile->name,
    'description' => $project->metaDescription(),
    'canonicalRoute' => 'project.show',
    'canonicalParams' => ['project' => $project->slug],
    'ogType' => 'article',
    'ogImage' => route('og.project', $project->slug),
    'noindex' => $preview ?? false,
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
  .gallery img{width:100%;aspect-ratio:16/10;object-fit:cover;border:1px solid var(--line);border-radius:var(--r);display:block;}
  .shot{margin:0;}
  .shot-trigger{display:block;width:100%;padding:0;border:0;background:none;cursor:zoom-in;font:inherit;}
  .shot figcaption{font-size:13px;color:var(--muted);margin-top:8px;line-height:1.45;}
  .lb{position:fixed;inset:0;background:rgba(34,29,23,.88);display:flex;flex-direction:column;align-items:center;justify-content:center;gap:14px;padding:28px;z-index:60;}
  .lb[hidden]{display:none;}
  .lb img{max-width:100%;max-height:80vh;object-fit:contain;border-radius:var(--r);background:var(--surface);}
  .lb-cap{color:#fff;font-size:13px;font-family:var(--mono);text-align:center;max-width:60ch;}
  .lb-close{position:absolute;top:18px;right:20px;background:none;border:1px solid rgba(255,255,255,.5);color:#fff;border-radius:99px;width:34px;height:34px;font-size:17px;cursor:pointer;line-height:1;}
  .cs-cta{padding:32px 0 72px;border-top:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;}
  .cs-cta .lab{font-family:var(--mono);font-size:12px;color:var(--muted);margin-bottom:6px;}
  .cs-cta h3{font-size:1.2rem;}
  .related{margin:34px 0 4px;}
  .related-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px;}
  .related-card{display:block;border:1px solid var(--line);border-radius:var(--r);padding:16px 18px;background:var(--surface);}
  .related-card:hover{border-color:var(--primary);}
  .related-name{display:block;font-family:var(--geo);font-weight:700;font-size:15px;}
  .related-meta{display:block;font-family:var(--mono);font-size:11px;color:var(--muted);margin-top:4px;}
  .preview-bar{background:var(--primary);color:#fff;font-family:var(--mono);font-size:12px;letter-spacing:.04em;text-align:center;padding:10px 20px;}
  .preview-state{margin-left:10px;padding:2px 8px;border:1px solid rgba(255,255,255,.5);border-radius:99px;}
</style>
@endpush

@section('content')
@if ($preview ?? false)
  <div class="preview-bar">
    {{ __('site.preview_banner') }}
    @unless ($project->published)<span class="preview-state">{{ __('site.preview_unpublished') }}</span>@endunless
  </div>
@endif
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
        <figure class="shot">
          {{-- A button, not a bare image: it must be reachable and operable from the keyboard. --}}
          <button type="button" class="shot-trigger" data-full="{{ $image->imageUrl() }}" data-caption="{{ $image->altText() }}">
            <img src="{{ $image->imageUrl() }}" alt="{{ $image->altText() }}" loading="lazy" decoding="async">
          </button>
          @if ($image->localizedCaption())
            <figcaption>{{ $image->localizedCaption() }}</figcaption>
          @endif
        </figure>
      @endforeach
    </div>
  @endif

  @php($related = $project->relatedProjects())
  @if ($related->isNotEmpty())
    <div class="related">
      <div class="gallery-label">{{ __('site.project_related_label') }}</div>
      <div class="related-grid">
        @foreach ($related as $other)
          <a class="related-card" href="{{ localized_route('project.show', $other->slug) }}">
            <span class="related-name">{{ $other->name }}</span>
            @if ($other->client_name || $other->year)
              <span class="related-meta">{{ trim($other->client_name.' '.$other->year) }}</span>
            @endif
          </a>
        @endforeach
      </div>
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

@if ($project->images->isNotEmpty())
@push('scripts')
<div class="lb" id="lb" role="dialog" aria-modal="true" aria-label="{{ __('site.project_gallery_label') }}" hidden>
  <button type="button" class="lb-close" id="lb-close" aria-label="{{ __('site.lightbox_close') }}">&times;</button>
  <img id="lb-img" src="" alt="">
  <p class="lb-cap" id="lb-cap"></p>
</div>
<script>
  (function () {
    var lb = document.getElementById('lb');
    var img = document.getElementById('lb-img');
    var cap = document.getElementById('lb-cap');
    var close = document.getElementById('lb-close');
    var opener = null;

    function open(btn) {
      opener = btn;
      img.src = btn.dataset.full;
      img.alt = btn.dataset.caption || '';
      cap.textContent = btn.dataset.caption || '';
      lb.hidden = false;
      close.focus();
      document.body.style.overflow = 'hidden';
    }

    function shut() {
      lb.hidden = true;
      img.src = '';
      document.body.style.overflow = '';
      // Return focus to the thumbnail the visitor came from, so keyboard
      // users are not dumped back at the top of the document.
      if (opener) { opener.focus(); opener = null; }
    }

    document.querySelectorAll('.shot-trigger').forEach(function (btn) {
      btn.addEventListener('click', function () { open(btn); });
    });
    close.addEventListener('click', shut);
    lb.addEventListener('click', function (e) { if (e.target === lb) shut(); });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && !lb.hidden) shut();
    });
  })();
</script>
@endpush
@endif
