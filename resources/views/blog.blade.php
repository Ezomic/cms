<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
@include('partials.seo', [
    'title' => __('site.blog_meta_title').' — '.$profile->name,
    'description' => __('site.blog_meta_description', ['name' => $profile->name]),
    'canonicalRoute' => 'blog.index',
])
@include('partials.schema.breadcrumbs', ['items' => [
    ['name' => __('site.breadcrumb_home'), 'url' => localized_route('home')],
    ['name' => __('site.breadcrumb_blog'), 'url' => localized_route('blog.index')],
]])
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Inter:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
  :root{
    --bg:#F7F7F4; --ink:#17181A; --ink-soft:#63645F; --line:#DDDDD6;
    --accent:#E8590C; --accent-soft:#FCE6D8; --white:#FFFFFF;
    --display:'Space Grotesk',sans-serif; --body:'Inter',sans-serif; --mono:'IBM Plex Mono',monospace;
  }
  *{margin:0;padding:0;box-sizing:border-box;}
  html{scroll-behavior:smooth;}
  @media (prefers-reduced-motion:reduce){html{scroll-behavior:auto;}*{transition-duration:.01ms !important;animation-duration:.01ms !important;}}
  body{background:var(--bg);color:var(--ink);font-family:var(--body);line-height:1.5;-webkit-font-smoothing:antialiased;}
  ::selection{background:var(--accent);color:var(--white);}
  a{color:inherit;}
  a:focus-visible,button:focus-visible{outline:2px solid var(--accent);outline-offset:2px;}
  .skip-link{position:absolute;left:-9999px;top:0;z-index:100;background:var(--ink);color:var(--white);font-family:var(--mono);font-size:13px;padding:10px 16px;text-decoration:none;}
  .skip-link:focus{left:0;}
  .wrap{max-width:1120px;margin:0 auto;padding:0 32px;}
  nav{position:sticky;top:0;z-index:50;background:rgba(247,247,244,.88);backdrop-filter:blur(8px);border-bottom:1px solid var(--line);}
  nav .wrap{display:flex;align-items:center;justify-content:space-between;height:64px;}
  .logo{font-family:var(--mono);font-weight:500;font-size:14px;display:flex;align-items:center;gap:8px;text-decoration:none;color:var(--ink);white-space:nowrap;}
  .logo .dot{width:6px;height:6px;background:var(--accent);border-radius:50%;flex-shrink:0;}
  nav .wrap{flex-wrap:wrap;height:auto;min-height:64px;row-gap:8px;}
  @media (max-width:420px){.logo .locale-suffix{display:none;}}
  .back-link{font-family:var(--mono);font-size:13px;text-decoration:none;color:var(--ink-soft);}
  .back-link:hover{color:var(--ink);}
  .nav-right{display:flex;align-items:center;gap:16px;}
  .lang-toggle{font-family:var(--mono);font-size:12px;color:var(--ink-soft);border:1px solid var(--line);padding:4px 10px;text-decoration:none;transition:color .15s,border-color .15s;}
  .lang-toggle:hover{color:var(--ink);border-color:var(--ink);}

  .page-header{padding:72px 0 56px;border-bottom:1px solid var(--line);}
  .eyebrow{font-family:var(--mono);font-size:13px;color:var(--accent);text-transform:uppercase;letter-spacing:.08em;margin-bottom:20px;}
  h1{font-family:var(--display);font-weight:600;font-size:clamp(2rem,5vw,3.2rem);line-height:1.08;letter-spacing:-.02em;}

  .post-list{padding:0;}
  .post-item{display:grid;grid-template-columns:1fr 3fr;gap:24px;padding:32px 0;border-top:1px solid var(--line);align-items:start;}
  .post-list .post-item:first-child{border-top:none;}
  .post-date{font-family:var(--mono);font-size:13px;color:var(--ink-soft);}
  .post-title{font-family:var(--display);font-weight:600;font-size:22px;margin-bottom:8px;}
  .post-title a{text-decoration:none;}
  .post-title a:hover{color:var(--accent);}
  .post-excerpt{color:var(--ink-soft);font-size:15px;max-width:60ch;}
  @media (max-width:720px){.post-item{grid-template-columns:1fr;}}

  footer{padding:32px 0;font-family:var(--mono);font-size:12px;color:var(--ink-soft);border-top:1px solid var(--line);}
  footer .wrap{display:flex;justify-content:space-between;flex-wrap:wrap;gap:12px;}
</style>
</head>
<body>

<a class="skip-link" href="#main">{{ __('site.skip_to_content') }}</a>

<nav>
  <div class="wrap">
    <a class="logo" href="{{ localized_route('home') }}"><span class="dot"></span>{{ strtoupper($profile->name) }}<span class="locale-suffix"> / NL</span></a>
    <div class="nav-right">
      <a class="lang-toggle" href="{{ alternate_locale_url(app()->getLocale() === 'en' ? 'nl' : 'en') }}">{{ __('site.lang_toggle') }}</a>
      <a class="back-link" href="{{ localized_route('home') }}">{{ __('site.back_to_site') }}</a>
    </div>
  </div>
</nav>

<main id="main" tabindex="-1" class="wrap">
  <div class="page-header">
    <div class="eyebrow">{{ __('site.blog_page_label') }}</div>
    <h1>{{ __('site.blog_page_headline') }}</h1>
  </div>

  <div class="post-list">
    @forelse ($posts as $post)
      <div class="post-item">
        <div class="post-date">{{ $post->published_at?->format('M j, Y') }}</div>
        <div>
          <div class="post-title"><a href="{{ localized_route('blog.show', $post->slug) }}">{{ $post->localizedTitle() }} →</a></div>
          <div class="post-excerpt">{{ $post->localizedExcerpt() }}</div>
        </div>
      </div>
    @empty
      <p style="padding:48px 0;color:var(--ink-soft);">{{ __('site.blog_no_posts') }}</p>
    @endforelse
  </div>
</main>

<footer>
  <div class="wrap">
    <span>© {{ date('Y') }} {{ $profile->name }}. {{ __('site.footer_built') }}</span>
    <a href="{{ localized_route('home') }}" style="font-family:var(--mono);font-size:12px;color:var(--ink-soft);text-decoration:none;">{{ __('site.back_to_site') }}</a>
  </div>
</footer>

</body>
</html>
