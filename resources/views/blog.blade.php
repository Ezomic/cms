@extends('layouts.public')

@section('head')
@include('partials.seo', [
    'title' => __('site.blog_meta_title').' · '.$profile->name,
    'description' => __('site.blog_meta_description', ['name' => $profile->name]),
    'canonicalRoute' => 'blog.index',
])
@include('partials.schema.breadcrumbs', ['items' => [
    ['name' => __('site.breadcrumb_home'), 'url' => localized_route('home')],
    ['name' => __('site.breadcrumb_blog'), 'url' => localized_route('blog.index')],
]])
@endsection

@push('styles')
<style>
  .page-header{padding:56px 0 30px;}
  .page-header .eyebrow{display:block;margin-bottom:12px;}
  .page-header h1{font-size:clamp(2rem,4.4vw,3rem);line-height:1.05;max-width:18ch;}
  .post-list{padding-bottom:64px;}
  .post-item{display:grid;grid-template-columns:130px 1fr;gap:24px;padding:22px 0;border-top:1px solid var(--line);align-items:baseline;}
  .post-item:hover .post-title{color:var(--primary);}
  .post-date{font-family:var(--mono);font-size:12px;color:var(--muted);}
  .post-title{font-family:var(--geo);font-weight:700;font-size:20px;letter-spacing:-.01em;margin-bottom:6px;transition:color .15s;}
  .post-excerpt{color:var(--muted);font-size:14px;max-width:62ch;}
  @media (max-width:620px){.post-item{grid-template-columns:1fr;gap:6px;}}
</style>
@endpush

@section('content')
<div class="wrap">
  <div class="page-header">
    <span class="eyebrow">{{ __('site.blog_page_label') }}</span>
    <h1>{{ __('site.blog_page_headline') }}</h1>
  </div>

  <div class="post-list">
    @forelse ($posts as $post)
      <a class="post-item" href="{{ localized_route('blog.show', $post->slug) }}">
        <div class="post-date">{{ $post->published_at?->locale(app()->getLocale())->translatedFormat('M j, Y') }}</div>
        <div>
          <div class="post-title">{{ $post->localizedTitle() }}</div>
          <div class="post-excerpt">{{ $post->localizedExcerpt() }}</div>
        </div>
      </a>
    @empty
      <p style="padding:48px 0;color:var(--muted);">{{ __('site.blog_no_posts') }}</p>
    @endforelse
  </div>
</div>
@endsection
