<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
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
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
  :root{
    --bg:#F7F7F4; --ink:#17181A; --ink-soft:#63645F; --line:#DDDDD6;
    --accent:#E8590C; --accent-soft:#FCE6D8; --white:#FFFFFF;
    --display:'Space Grotesk',sans-serif; --body:'Inter',sans-serif; --mono:'IBM Plex Mono',monospace;
  }
  *{margin:0;padding:0;box-sizing:border-box;}
  html{scroll-behavior:smooth;}
  body{background:var(--bg);color:var(--ink);font-family:var(--body);line-height:1.5;-webkit-font-smoothing:antialiased;}
  ::selection{background:var(--accent);color:var(--white);}
  a{color:inherit;}
  .wrap{max-width:1120px;margin:0 auto;padding:0 32px;}
  nav{position:sticky;top:0;z-index:50;background:rgba(247,247,244,.88);backdrop-filter:blur(8px);border-bottom:1px solid var(--line);}
  nav .wrap{display:flex;align-items:center;justify-content:space-between;height:64px;}
  .logo{font-family:var(--mono);font-weight:500;font-size:14px;display:flex;align-items:center;gap:8px;text-decoration:none;color:var(--ink);}
  .logo .dot{width:6px;height:6px;background:var(--accent);border-radius:50%;}
  .back-link{font-family:var(--mono);font-size:13px;text-decoration:none;color:var(--ink-soft);}
  .back-link:hover{color:var(--ink);}

  .page-header{padding:72px 0 56px;border-bottom:1px solid var(--line);}
  .eyebrow{font-family:var(--mono);font-size:13px;color:var(--accent);text-transform:uppercase;letter-spacing:.08em;margin-bottom:20px;}
  h1{font-family:var(--display);font-weight:600;font-size:clamp(2rem,5vw,3.2rem);line-height:1.08;letter-spacing:-.02em;}

  .filters{padding:24px 0;border-bottom:1px solid var(--line);display:flex;gap:8px;flex-wrap:wrap;align-items:center;}
  .filter-label{font-family:var(--mono);font-size:12px;color:var(--ink-soft);margin-right:8px;}
  .filter-btn{font-family:var(--mono);font-size:12px;color:var(--ink-soft);border:1px solid var(--line);padding:5px 12px;border-radius:20px;cursor:pointer;background:transparent;transition:all .15s;}
  .filter-btn:hover,.filter-btn.active{background:var(--ink);color:var(--white);border-color:var(--ink);}
  .filter-btn.active{background:var(--accent);border-color:var(--accent);}

  .work-list{padding:0;}
  .work-item{display:grid;grid-template-columns:1fr 2fr 1fr;gap:24px;padding:32px 0;border-top:1px solid var(--line);align-items:start;}
  .work-list .work-item:first-child{border-top:none;}
  .work-item[data-hidden]{display:none;}
  .work-year{font-family:var(--mono);font-size:13px;color:var(--ink-soft);}
  .work-image{width:100%;aspect-ratio:16/10;object-fit:cover;border:1px solid var(--line);margin-bottom:12px;}
  .work-name{font-family:var(--display);font-weight:600;font-size:22px;margin-bottom:8px;}
  .work-name a{text-decoration:none;}
  .work-name a:hover{color:var(--accent);}
  .work-desc{color:var(--ink-soft);font-size:15px;max-width:48ch;}
  .work-tags{display:flex;flex-wrap:wrap;gap:8px;justify-content:flex-end;}
  .tag{font-family:var(--mono);font-size:12px;color:var(--ink-soft);border:1px solid var(--line);padding:4px 10px;border-radius:20px;white-space:nowrap;}
  @media (max-width:720px){.work-item{grid-template-columns:1fr;}.work-tags{justify-content:flex-start;}}

  .empty-state{padding:64px 0;text-align:center;font-family:var(--mono);font-size:13px;color:var(--ink-soft);display:none;}
  footer{padding:32px 0;font-family:var(--mono);font-size:12px;color:var(--ink-soft);border-top:1px solid var(--line);}
  footer .wrap{display:flex;justify-content:space-between;flex-wrap:wrap;gap:12px;}
</style>
</head>
<body>

<nav>
  <div class="wrap">
    <a class="logo" href="{{ localized_route('home') }}"><span class="dot"></span>{{ strtoupper($profile->name) }} / NL</a>
    <a class="back-link" href="{{ localized_route('home') }}">{{ __('site.back_to_site') }}</a>
  </div>
</nav>

<div class="wrap">
  <div class="page-header">
    <div class="eyebrow">{{ __('site.work_page_label') }}</div>
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

  <div class="work-list" id="work-list">
    @forelse ($projects as $project)
      <div class="work-item" data-tags="{{ implode(',', $project->tag_list) }}">
        <div>
          <div class="work-year">{{ $project->year }}</div>
          <div style="font-family:var(--mono);font-size:12px;color:var(--ink-soft);margin-top:4px;">{{ $project->client_name }}</div>
        </div>
        <div>
          @if ($project->image_url)
            <img class="work-image" src="{{ $project->image_url }}" alt="{{ $project->name }}" loading="lazy" decoding="async">
          @endif
          <div class="work-name">
            @if ($project->body)
              <a href="{{ localized_route('project.show', $project->slug) }}">{{ $project->name }} →</a>
            @else
              {{ $project->name }}
            @endif
          </div>
          <div class="work-desc">{{ $project->description }}</div>
        </div>
        <div class="work-tags">
          @foreach ($project->tag_list as $tag)
            <span class="tag">{{ $tag }}</span>
          @endforeach
        </div>
      </div>
    @empty
      <p style="padding:48px 0;color:var(--ink-soft);">{{ __('site.work_no_projects') }}</p>
    @endforelse
  </div>

  <p class="empty-state" id="empty-state">{{ __('site.work_no_match') }}</p>
</div>

<footer>
  <div class="wrap">
    <span>© {{ date('Y') }} {{ $profile->name }}. {{ __('site.footer_built') }}</span>
    <a href="{{ localized_route('home') }}" style="font-family:var(--mono);font-size:12px;color:var(--ink-soft);text-decoration:none;">{{ __('site.back_to_site') }}</a>
  </div>
</footer>

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

</body>
</html>
