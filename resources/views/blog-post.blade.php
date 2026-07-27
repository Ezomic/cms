@extends('layouts.public')

@section('head')
@include('partials.seo', [
    'title' => $post->metaTitle().' — '.$profile->name,
    'description' => $post->metaDescription(),
    'canonicalRoute' => 'blog.show',
    'canonicalParams' => ['post' => $post->slug],
    'ogType' => 'article',
    'ogImage' => route('og.post', $post->slug),
])
@include('partials.schema.breadcrumbs', ['items' => [
    ['name' => __('site.breadcrumb_home'), 'url' => localized_route('home')],
    ['name' => __('site.breadcrumb_blog'), 'url' => localized_route('blog.index')],
    ['name' => $post->localizedTitle(), 'url' => localized_route('blog.show', $post->slug)],
]])
@endsection

@push('styles')
<style>
  .cs{max-width:720px;margin:0 auto;padding:0 28px;}
  .cs-hero{padding:44px 0 4px;}
  .breadcrumb{font-family:var(--mono);font-size:12px;color:var(--muted);margin-bottom:18px;}
  .breadcrumb a:hover{color:var(--primary);}
  .cs-hero h1{font-size:clamp(2rem,4.6vw,3rem);line-height:1.05;max-width:22ch;}
  .cs-meta{display:flex;gap:20px;flex-wrap:wrap;margin-top:18px;padding:14px 0;border-top:1px solid var(--line);border-bottom:1px solid var(--line);font-family:var(--mono);font-size:12px;color:var(--muted);}
  .body-content{padding:26px 0 20px;font-size:17px;color:#3a352d;line-height:1.7;}
  .body-content p{margin-bottom:1.2em;}
  .body-content h2{font-family:var(--geo);font-weight:700;font-size:1.4rem;letter-spacing:-.01em;margin:1.6em 0 .5em;color:var(--ink);}
  .body-content h3{font-family:var(--geo);font-weight:700;font-size:1.12rem;margin:1.5em 0 .4em;color:var(--ink);}
  .body-content ul,.body-content ol{padding-left:1.4em;margin-bottom:1.2em;}
  .body-content li{margin-bottom:.5em;}
  .body-content code{font-family:var(--mono);font-size:.88em;background:var(--soft);color:var(--deep);padding:2px 6px;border-radius:4px;}
  .body-content strong{font-weight:600;color:var(--ink);}
  .cs-cta{padding:32px 0 72px;border-top:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;}
  .cs-cta .lab{font-family:var(--mono);font-size:12px;color:var(--muted);margin-bottom:6px;}
  .cs-cta h3{font-size:1.2rem;}
</style>
@endpush

@section('content')
<div class="cs">
  <header class="cs-hero">
    <div class="breadcrumb"><a href="{{ localized_route('blog.index') }}">{{ __('site.breadcrumb_blog') }}</a> / {{ $post->localizedTitle() }}</div>
    <span class="eyebrow">{{ __('site.blog_eyebrow') }}</span>
    <h1>{{ $post->localizedTitle() }}</h1>
    @if ($post->published_at)
      <div class="cs-meta"><span>{{ $post->published_at->locale(app()->getLocale())->translatedFormat('F j, Y') }}</span></div>
    @endif
  </header>

  <div class="body-content">
    {!! $post->localizedBody() !!}
  </div>

  <div class="cs-cta">
    <div>
      <div class="lab">{{ __('site.project_cta_lead') }}</div>
      <h3>{{ __('site.project_cta_headline') }}</h3>
    </div>
    <a class="btn pri" href="{{ localized_route('home') }}?ref={{ urlencode($post->slug) }}#contact">{{ __('site.project_cta_button') }}</a>
  </div>
</div>
@endsection
