<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    ['name' => __('site.breadcrumb_home'), 'url' => route('home')],
    ['name' => __('site.breadcrumb_work'), 'url' => route('work.index')],
    ['name' => $project->name, 'url' => route('project.show', $project->slug)],
]])
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
  :root{
    --bg:#F7F7F4; --ink:#17181A; --ink-soft:#63645F; --line:#DDDDD6;
    --accent:#E8590C; --accent-soft:#FCE6D8; --white:#FFFFFF;
    --display:'Space Grotesk',sans-serif; --body:'Inter',sans-serif; --mono:'IBM Plex Mono',monospace;
  }
  *{margin:0;padding:0;box-sizing:border-box;}
  body{background:var(--bg);color:var(--ink);font-family:var(--body);line-height:1.6;-webkit-font-smoothing:antialiased;}
  a{color:inherit;}
  .wrap{max-width:820px;margin:0 auto;padding:0 32px;}
  nav{position:sticky;top:0;z-index:50;background:rgba(247,247,244,.88);backdrop-filter:blur(8px);border-bottom:1px solid var(--line);}
  nav .wrap{max-width:1120px;display:flex;align-items:center;justify-content:space-between;height:64px;}
  .logo{font-family:var(--mono);font-weight:500;font-size:14px;display:flex;align-items:center;gap:8px;}
  .logo .dot{width:6px;height:6px;background:var(--accent);border-radius:50%;}
  .back-link{font-family:var(--mono);font-size:13px;text-decoration:none;color:var(--ink-soft);}
  .back-link:hover{color:var(--ink);}
  header.hero{padding:64px 0 40px;border-bottom:1px solid var(--line);}
  .eyebrow{font-family:var(--mono);font-size:13px;color:var(--accent);text-transform:uppercase;letter-spacing:.08em;margin-bottom:16px;}
  h1{font-family:var(--display);font-weight:600;font-size:clamp(2rem,5vw,3rem);line-height:1.1;letter-spacing:-.02em;margin-bottom:20px;}
  .meta-row{display:flex;gap:24px;flex-wrap:wrap;font-family:var(--mono);font-size:13px;color:var(--ink-soft);margin-bottom:24px;}
  .work-tags{display:flex;flex-wrap:wrap;gap:8px;}
  .tag{font-family:var(--mono);font-size:12px;color:var(--ink-soft);border:1px solid var(--line);padding:4px 10px;border-radius:20px;white-space:nowrap;}
  .cover{width:100%;aspect-ratio:16/10;max-height:480px;object-fit:cover;border:1px solid var(--line);margin:40px 0;}
  .outcome-callout{background:var(--accent-soft);border-left:3px solid var(--accent);padding:20px 24px;margin:32px 0;font-family:var(--mono);font-size:14px;color:var(--ink);}
  .outcome-callout strong{display:block;font-size:11px;text-transform:uppercase;letter-spacing:.08em;color:var(--accent);margin-bottom:6px;}
  .body-content{padding:24px 0 96px;font-size:17px;color:var(--ink);}
  .body-content p{margin-bottom:1.2em;}
  .body-content h2{font-family:var(--display);font-weight:600;font-size:1.35rem;letter-spacing:-.01em;margin:2.2em 0 .6em;}
  .body-content h3{font-family:var(--display);font-weight:600;font-size:1.1rem;margin:1.8em 0 .4em;}
  .body-content ul,.body-content ol{padding-left:1.4em;margin-bottom:1.2em;}
  .body-content li{margin-bottom:.5em;}
  .body-content code{font-family:var(--mono);font-size:.88em;background:var(--accent-soft);padding:2px 6px;border-radius:3px;}
  .body-content strong{font-weight:600;}
  footer{padding:32px 0;font-family:var(--mono);font-size:12px;color:var(--ink-soft);border-top:1px solid var(--line);}
  footer .wrap{max-width:1120px;}
</style>
</head>
<body>

<nav>
  <div class="wrap">
    <div class="logo"><span class="dot"></span>{{ strtoupper($profile->name) }} / NL</div>
    <a class="back-link" href="{{ route('home') }}">{{ __('site.back_to_site') }}</a>
  </div>
</nav>

<header class="hero">
  <div class="wrap">
    <div class="eyebrow">{{ __('site.project_eyebrow') }}</div>
    <h1>{{ $project->name }}</h1>
    <div class="meta-row">
      @if ($project->client_name)<span>{{ $project->client_name }}</span>@endif
      @if ($project->year)<span>{{ $project->year }}</span>@endif
    </div>
    <div class="work-tags">
      @foreach ($project->tagList() as $tag)
        <span class="tag">{{ $tag }}</span>
      @endforeach
    </div>
  </div>
</header>

<div class="wrap">
  @if ($project->image)
    <img class="cover" src="{{ $project->imageUrl() }}" alt="{{ $project->name }}" fetchpriority="high" decoding="async">
  @endif

  @if ($project->outcome)
    <div class="outcome-callout">
      <strong>Result</strong>
      {{ $project->outcome }}
    </div>
  @endif

  <div class="body-content">
    {!! $project->body !!}
  </div>

  <div style="padding:48px 0;border-top:1px solid var(--line);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;">
    <div>
      <div style="font-family:var(--mono);font-size:13px;color:var(--ink-soft);margin-bottom:8px;">{{ __('site.project_cta_lead') }}</div>
      <div style="font-family:var(--display);font-weight:600;font-size:1.2rem;">{{ __('site.project_cta_headline') }}</div>
    </div>
    <a href="{{ route('home') }}?ref={{ urlencode($project->slug) }}#contact" style="font-family:var(--mono);font-size:14px;background:var(--ink);color:var(--white);padding:14px 24px;text-decoration:none;display:inline-flex;align-items:center;gap:8px;transition:background .15s;" onmouseover="this.style.background='var(--accent)'" onmouseout="this.style.background='var(--ink)'">{{ __('site.project_cta_button') }}</a>
  </div>
</div>

<footer>
  <div class="wrap">
    <span>© {{ date('Y') }} {{ $profile->name }}.</span>
    <a class="footer-cta" href="{{ route('home') }}?ref={{ urlencode($project->slug) }}#contact">{{ __('site.project_cta_button') }}</a>
  </div>
</footer>
<style>
  footer{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;}
  .footer-cta{font-family:var(--mono);font-size:13px;background:var(--ink);color:var(--white);padding:10px 20px;text-decoration:none;transition:background .15s;}
  .footer-cta:hover{background:var(--accent);}
</style>

</body>
</html>
