@extends('layouts.public')

@section('head')
@include('partials.seo', [
    'title' => ($activeTag ? __('site.work_meta_title_tag', ['tag' => $activeTag]) : __('site.work_meta_title')).' — '.$profile->name,
    'description' => $activeTag
        ? __('site.work_meta_description_tag', ['tag' => $activeTag, 'name' => $profile->name])
        : __('site.work_meta_description', ['name' => $profile->name]),
    'canonicalRoute' => $activeTag ? 'work.tag' : 'work.index',
    'canonicalParams' => $activeTag ? ['tag' => $activeTag] : [],
])
@if($activeTag)
@include('partials.schema.breadcrumbs', ['items' => [
    ['name' => __('site.breadcrumb_home'), 'url' => localized_route('home')],
    ['name' => __('site.breadcrumb_work'), 'url' => localized_route('work.index')],
    ['name' => $activeTag, 'url' => localized_route('work.tag', $activeTag)],
]])
@endif
@endsection

@push('styles')
<style>
  .page-header{padding:56px 0 30px;}
  .page-header .eyebrow{display:block;margin-bottom:12px;}
  .page-header h1{font-size:clamp(2rem,4.4vw,3rem);line-height:1.05;max-width:18ch;}
  .filters{display:flex;gap:8px;flex-wrap:wrap;align-items:center;margin:4px 0 26px;}
  .filter-label{font-family:var(--mono);font-size:12px;color:var(--muted);margin-right:6px;}
  .filter-btn{font-family:var(--mono);font-size:12px;color:var(--muted);border:1px solid var(--line-strong);border-radius:20px;padding:6px 13px;cursor:pointer;background:transparent;transition:all .15s;}
  .filter-btn:hover{color:var(--ink);border-color:var(--ink);}
  .filter-btn.active{background:var(--primary);color:#fff;border-color:var(--primary);}
  .empty-state{padding:56px 0;text-align:center;font-family:var(--mono);font-size:13px;color:var(--muted);display:none;}
</style>
@endpush

@section('content')
<div class="wrap">
  <div class="page-header">
    <span class="eyebrow">{{ __('site.work_page_label') }}</span>
    <h1>{{ __('site.work_page_headline') }}</h1>
  </div>

  @if ($tags->isNotEmpty())
  <div class="filters">
    <span class="filter-label">{{ __('site.work_filter_label') }}</span>
    <a class="filter-btn {{ !$activeTag ? 'active' : '' }}" href="{{ localized_route('work.index') }}" data-tag="all">{{ __('site.work_filter_all') }}</a>
    @foreach ($tags as $tag)
      <a class="filter-btn {{ $activeTag === $tag ? 'active' : '' }}" href="{{ localized_route('work.tag', $tag) }}" data-tag="{{ $tag }}">{{ $tag }}</a>
    @endforeach
  </div>
  @endif

  <div class="card-grid" id="work-list" style="padding-bottom:56px;">
    @forelse ($projects as $project)
      @php $clickable = (bool) $project->body; $tag = $clickable ? 'a' : 'div'; @endphp
      <{{ $tag }} class="pcard work-item" data-tags="{{ implode(',', $project->tag_list) }}" @if($clickable) href="{{ localized_route('project.show', $project->slug) }}" @endif>
        @if ($project->image_url)
          <img class="thumb" src="{{ $project->image_url }}" alt="{{ $project->image_alt ?: $project->name }}" loading="lazy" decoding="async">
        @else
          <div class="thumb ph"><span class="k">{{ $project->year }}</span></div>
        @endif
        <div class="in">
          <h3>{{ $project->name }}</h3>
          <div class="meta">{{ $project->year }}{{ $project->year && $project->client_name ? ' · ' : '' }}{{ $project->client_name }}</div>
          <p>{{ $project->description }}</p>
          @if (count($project->tag_list))
            <div class="tags">@foreach ($project->tag_list as $t)<span class="tag">{{ $t }}</span>@endforeach</div>
          @endif
        </div>
      </{{ $tag }}>
    @empty
      <p style="padding:48px 0;color:var(--muted);">{{ __('site.work_no_projects') }}</p>
    @endforelse
  </div>

  <p class="empty-state" id="empty-state">{{ __('site.work_no_match') }}</p>
</div>
@endsection

@push('scripts')
<script>
  var btns = document.querySelectorAll('.filter-btn');
  var items = document.querySelectorAll('.work-item');
  var empty = document.getElementById('empty-state');
  btns.forEach(function(btn) {
    btn.addEventListener('click', function(e) {
      e.preventDefault();
      var tag = btn.dataset.tag;
      btns.forEach(function(b){ b.classList.remove('active'); });
      btn.classList.add('active');
      var visible = 0;
      items.forEach(function(item) {
        var tags = item.dataset.tags ? item.dataset.tags.split(',').map(function(t){ return t.trim(); }) : [];
        var show = tag === 'all' || tags.indexOf(tag) !== -1;
        item.style.display = show ? '' : 'none';
        if (show) visible++;
      });
      empty.style.display = visible === 0 ? 'block' : 'none';
      history.replaceState(null, '', btn.href);
    });
  });
</script>
@endpush
