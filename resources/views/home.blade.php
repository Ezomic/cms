<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
@include('partials.seo', [
    'title' => $profile->meta_title ?: $profile->name.' — '.$profile->tagline,
    'description' => $profile->meta_description ?: $profile->hero_subtext,
    'canonicalRoute' => 'home',
])
@include('partials.schema.person')
@include('partials.schema.website')
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
  a:focus-visible,button:focus-visible,input:focus-visible,textarea:focus-visible,select:focus-visible{outline:2px solid var(--accent);outline-offset:2px;}
  section[id]{scroll-margin-top:72px;}
  .skip-link{position:absolute;left:-9999px;top:0;z-index:100;background:var(--ink);color:var(--white);font-family:var(--mono);font-size:13px;padding:10px 16px;text-decoration:none;}
  .skip-link:focus{left:0;}
  .wrap{max-width:1120px;margin:0 auto;padding:0 32px;}
  nav{position:sticky;top:0;z-index:50;background:rgba(247,247,244,.88);backdrop-filter:blur(8px);border-bottom:1px solid var(--line);}
  nav .wrap{display:flex;align-items:center;justify-content:space-between;height:64px;}
  .logo{font-family:var(--mono);font-weight:500;font-size:14px;display:flex;align-items:center;gap:8px;}
  .logo .dot{width:6px;height:6px;background:var(--accent);border-radius:50%;}
  .nav-links{display:flex;gap:32px;font-size:14px;font-family:var(--mono);}
  .nav-links a{text-decoration:none;color:var(--ink-soft);transition:color .15s;}
  .nav-links a:hover{color:var(--ink);}
  .nav-cta{font-family:var(--mono);font-size:13px;border:1px solid var(--ink);padding:8px 16px;text-decoration:none;transition:all .15s;}
  .nav-cta:hover{background:var(--ink);color:var(--white);}
  .lang-toggle{font-family:var(--mono);font-size:12px;color:var(--ink-soft);border:1px solid var(--line);padding:4px 10px;text-decoration:none;transition:color .15s,border-color .15s;}
  .lang-toggle:hover{color:var(--ink);border-color:var(--ink);}
  .nav-burger{display:none;background:none;border:1px solid var(--line);width:38px;height:38px;cursor:pointer;padding:0;flex-direction:column;align-items:center;justify-content:center;gap:4px;transition:border-color .15s;}
  .nav-burger:hover{border-color:var(--ink);}
  .nav-burger span{display:block;width:16px;height:2px;background:var(--ink);transition:transform .2s,opacity .2s;}
  .nav-burger[aria-expanded="true"] span:nth-child(1){transform:translateY(6px) rotate(45deg);}
  .nav-burger[aria-expanded="true"] span:nth-child(2){opacity:0;}
  .nav-burger[aria-expanded="true"] span:nth-child(3){transform:translateY(-6px) rotate(-45deg);}
  .mobile-menu{display:none;border-top:1px solid var(--line);}
  .mobile-menu.open{display:block;}
  .mobile-menu .wrap{display:block;height:auto;padding-top:8px;padding-bottom:8px;}
  .mobile-menu a{display:block;font-family:var(--mono);font-size:14px;color:var(--ink);text-decoration:none;padding:14px 0;border-bottom:1px solid var(--line);}
  .mobile-menu a:last-child{border-bottom:none;color:var(--ink-soft);}
  @media (max-width:720px){.nav-mobile-hide{display:none;}.nav-burger{display:flex;}}
  .hero{position:relative;padding:96px 0 120px;overflow:hidden;border-bottom:1px solid var(--line);}
  .grid-bg{position:absolute;inset:0;background-image:linear-gradient(var(--line) 1px,transparent 1px),linear-gradient(90deg,var(--line) 1px,transparent 1px);background-size:64px 64px;opacity:.35;mask-image:linear-gradient(to bottom,black,transparent 85%);}
  .hero-inner{position:relative;}
  .status-row{display:flex;align-items:center;gap:10px;font-family:var(--mono);font-size:13px;color:var(--ink-soft);margin-bottom:40px;}
  .status-dot{width:8px;height:8px;border-radius:50%;background:#2E9E4B;box-shadow:0 0 0 3px rgba(46,158,75,.15);}
  .status-dot.away{background:#B0AFA8;box-shadow:0 0 0 3px rgba(176,175,168,.15);}
  .eyebrow{font-family:var(--mono);font-size:13px;color:var(--accent);text-transform:uppercase;letter-spacing:.08em;margin-bottom:20px;}
  h1{font-family:var(--display);font-weight:600;font-size:clamp(2.4rem,6vw,4.6rem);line-height:1.04;letter-spacing:-.02em;max-width:16ch;}
  .hero-sub{margin-top:28px;font-size:19px;color:var(--ink-soft);max-width:52ch;}
  .hero-actions{margin-top:44px;display:flex;gap:16px;flex-wrap:wrap;align-items:center;}
  .btn-primary{font-family:var(--mono);font-size:14px;background:var(--ink);color:var(--white);padding:14px 24px;text-decoration:none;display:inline-flex;align-items:center;gap:8px;transition:transform .15s,background .15s;}
  .btn-primary:hover{background:var(--accent);transform:translateY(-1px);}
  .btn-secondary{font-family:var(--mono);font-size:14px;color:var(--ink);padding:14px 8px;text-decoration:none;border-bottom:1px solid var(--ink);}
  section{padding:88px 0;border-bottom:1px solid var(--line);}
  .section-head{margin-bottom:56px;}
  .section-label{font-family:var(--mono);font-size:13px;color:var(--ink-soft);text-transform:uppercase;letter-spacing:.08em;}
  h2{font-family:var(--display);font-weight:600;font-size:clamp(1.6rem,3vw,2.2rem);letter-spacing:-.01em;}
  .stack-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1px;background:var(--line);border:1px solid var(--line);}
  .stack-col{background:var(--bg);padding:32px;}
  .stack-col h3{font-family:var(--mono);font-size:13px;color:var(--accent);text-transform:uppercase;letter-spacing:.06em;margin-bottom:16px;}
  .stack-col ul{list-style:none;}
  .stack-col li{font-size:15px;padding:8px 0;border-top:1px solid var(--line);color:var(--ink-soft);}
  .stack-col li:first-of-type{border-top:none;}
  @media (max-width:720px){.stack-grid{grid-template-columns:1fr;}}
  .work-item{display:grid;grid-template-columns:1fr 2fr 1fr;gap:24px;padding:32px 0;border-top:1px solid var(--line);align-items:start;}
  .work-list .work-item:first-child{border-top:none;}
  .work-year{font-family:var(--mono);font-size:13px;color:var(--ink-soft);}
  .work-image{width:100%;aspect-ratio:16/10;object-fit:cover;border:1px solid var(--line);margin-bottom:12px;}
  .work-name{font-family:var(--display);font-weight:600;font-size:22px;margin-bottom:8px;}
  .work-name a{text-decoration:none;}
  .work-name a:hover{color:var(--accent);}
  .work-desc{color:var(--ink-soft);font-size:15px;max-width:48ch;}
  .work-tags{display:flex;flex-wrap:wrap;gap:8px;justify-content:flex-end;}
  .tag{font-family:var(--mono);font-size:12px;color:var(--ink-soft);border:1px solid var(--line);padding:4px 10px;border-radius:20px;white-space:nowrap;}
  @media (max-width:720px){.work-item{grid-template-columns:1fr;}.work-tags{justify-content:flex-start;}}
  .process-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:32px;}
  .process-num{font-family:var(--mono);font-size:13px;color:var(--accent);margin-bottom:16px;}
  .process-step h3{font-family:var(--display);font-weight:600;font-size:18px;margin-bottom:10px;}
  .process-step p{font-size:14px;color:var(--ink-soft);}
  @media (max-width:900px){.process-grid{grid-template-columns:1fr 1fr;}}
  @media (max-width:560px){.process-grid{grid-template-columns:1fr;}}
  .services-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:1px;background:var(--line);border:1px solid var(--line);}
  .service-card{background:var(--bg);padding:32px;}
  .service-card h3{font-family:var(--display);font-weight:600;font-size:18px;margin-bottom:12px;}
  .service-card p{font-size:14px;color:var(--ink-soft);margin-bottom:20px;}
  .service-card ul{list-style:none;}
  .service-card ul li{font-size:13px;color:var(--ink-soft);padding:6px 0;border-top:1px solid var(--line);}
  .service-card ul li:first-of-type{border-top:none;}
  .service-price{font-family:var(--mono);font-size:13px;color:var(--accent);margin-top:20px;padding-top:20px;border-top:1px solid var(--line);}
  @media (max-width:720px){.services-grid{grid-template-columns:1fr;}}
  blockquote{font-family:var(--display);font-weight:500;font-size:clamp(1.3rem,3vw,2rem);line-height:1.35;letter-spacing:-.01em;max-width:26ch;}
  .testimonial-slides{display:grid;}
  .testimonial-slide{grid-area:1/1;visibility:hidden;opacity:0;transition:opacity .4s ease;}
  .testimonial-slide.active{visibility:visible;opacity:1;}
  .quote-mark{color:var(--accent);font-size:2rem;line-height:0;display:block;margin-bottom:12px;}
  .testimonial-attr{margin-top:28px;font-family:var(--mono);font-size:13px;color:var(--ink-soft);}
  .contact{border-bottom:none;padding-bottom:64px;}
  .contact-inner{display:grid;grid-template-columns:1.4fr 1fr;gap:48px;}
  .contact h2{font-size:clamp(1.8rem,4vw,2.8rem);max-width:14ch;margin-bottom:24px;}
  .contact p.lead{color:var(--ink-soft);font-size:17px;max-width:44ch;margin-bottom:32px;}
  .contact-row{display:flex;justify-content:space-between;align-items:center;padding:16px 0;border-top:1px solid var(--line);text-decoration:none;color:var(--ink);font-family:var(--mono);font-size:14px;transition:padding-left .15s;}
  .contact-row:hover{padding-left:8px;color:var(--accent);}
  .contact-row span{color:var(--ink-soft);font-size:13px;}
  .meta-box{border:1px solid var(--line);padding:24px;font-family:var(--mono);font-size:13px;color:var(--ink-soft);height:fit-content;}
  .meta-box div{display:flex;justify-content:space-between;padding:8px 0;border-top:1px solid var(--line);}
  .meta-box div:first-child{border-top:none;}
  .meta-box strong{color:var(--ink);font-weight:500;}
  @media (max-width:800px){.contact-inner{grid-template-columns:1fr;}}
  .contact-form{margin-top:48px;border-top:1px solid var(--line);padding-top:48px;}
  .contact-form h3{font-family:var(--display);font-weight:600;font-size:18px;margin-bottom:20px;}
  .contact-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;}
  .contact-form label{display:block;font-family:var(--mono);font-size:12px;color:var(--ink-soft);margin-bottom:6px;}
  .contact-form input,.contact-form textarea,.contact-form select{width:100%;border:1px solid var(--line);background:var(--white);padding:12px 14px;font-family:var(--body);font-size:14px;color:var(--ink);}
  .contact-form input:focus,.contact-form textarea:focus,.contact-form select:focus{outline:1px solid var(--ink);}
  .contact-form textarea{margin-bottom:4px;resize:vertical;}
  .field-hint{font-family:var(--mono);font-size:11px;color:var(--ink-soft);margin-bottom:16px;}
  .response-note{font-family:var(--mono);font-size:12px;color:var(--ink-soft);margin-top:12px;}
  .honeypot{position:absolute;left:-9999px;top:-9999px;}
  .form-status{font-family:var(--mono);font-size:13px;padding:12px 16px;margin-bottom:20px;border:1px solid #2E9E4B;color:#20713A;background:rgba(46,158,75,.06);}
  .contact-form input.has-error,.contact-form textarea.has-error{border-color:#C0392B;}
  .field-error{font-family:var(--mono);font-size:12px;color:#C0392B;margin-top:6px;}
  .contact-form-grid > div{margin-bottom:0;}
  .contact-form-grid .field-error{margin-bottom:0;}
  @media (max-width:560px){.contact-form-grid{grid-template-columns:1fr;}}
  footer{padding:32px 0;font-family:var(--mono);font-size:12px;color:var(--ink-soft);}
  footer .wrap{display:flex;justify-content:space-between;flex-wrap:wrap;gap:12px;}
</style>
</head>
<body>

<a class="skip-link" href="#main">{{ __('site.skip_to_content') }}</a>

<nav>
  <div class="wrap">
    <div class="logo"><span class="dot"></span>{{ strtoupper($profile->name) }} / NL</div>
    <div class="nav-links nav-mobile-hide">
      <a href="{{ localized_route('work.index') }}">{{ __('site.nav_work') }}</a>
      <a href="#services">{{ __('site.nav_services') }}</a>
      <a href="#process">{{ __('site.nav_process') }}</a>
      <a href="{{ localized_route('docs') }}">{{ __('site.nav_docs') }}</a>
      <a href="#contact">{{ __('site.nav_contact') }}</a>
    </div>
    <div style="display:flex;align-items:center;gap:12px;">
      <a class="lang-toggle nav-mobile-hide" href="{{ alternate_locale_url(app()->getLocale() === 'en' ? 'nl' : 'en') }}">{{ __('site.lang_toggle') }}</a>
      <a class="nav-cta nav-mobile-hide" href="#contact">{{ __('site.nav_cta') }}</a>
      <button class="nav-burger" type="button" aria-expanded="false" aria-controls="mobile-menu" aria-label="{{ __('site.nav_menu_label') }}">
        <span></span><span></span><span></span>
      </button>
    </div>
  </div>
  <div class="mobile-menu" id="mobile-menu">
    <div class="wrap">
      <a href="{{ localized_route('work.index') }}">{{ __('site.nav_work') }}</a>
      <a href="#services">{{ __('site.nav_services') }}</a>
      <a href="#process">{{ __('site.nav_process') }}</a>
      <a href="{{ localized_route('docs') }}">{{ __('site.nav_docs') }}</a>
      <a href="#contact">{{ __('site.nav_contact') }}</a>
      <a href="{{ alternate_locale_url(app()->getLocale() === 'en' ? 'nl' : 'en') }}">{{ __('site.lang_toggle_long') }}</a>
    </div>
  </div>
</nav>

<main id="main" tabindex="-1">

<header class="hero">
  <div class="grid-bg"></div>
  <div class="wrap hero-inner">
    <div class="status-row">
      <span class="status-dot {{ $profile->available ? '' : 'away' }}"></span>
      @if ($profile->available)
        <span>{{ __('site.status_available') }}</span>
      @else
        <span>{{ __('site.status_booked') }}@if($profile->availability_from) — {{ __('site.status_available_from', ['date' => $profile->availability_from]) }}@endif</span>
      @endif
      <span style="color:var(--line)">·</span>
      <span id="local-time">--:-- {{ __('site.hero_local_time', ['city' => $profile->city]) }}</span>
    </div>

    <div class="eyebrow">{{ $profile->tagline }}</div>
    <h1>{{ $profile->hero_headline }}</h1>
    <p class="hero-sub">{{ $profile->hero_subtext }}</p>

    <div class="hero-actions">
      <a class="btn-primary" href="#contact">{{ __('site.hero_actions_primary') }}</a>
      <a class="btn-secondary" href="{{ localized_route('work.index') }}">{{ __('site.hero_actions_secondary') }}</a>
      <a class="btn-secondary" href="{{ route('cv') }}" style="margin-left:8px;">{{ __('site.hero_download_cv') }}</a>
    </div>
  </div>
</header>

<section id="stack">
  <div class="wrap">
    <div class="section-head">
      <div class="section-label">{{ __('site.stack_label') }}</div>
      <h2>{{ __('site.stack_headline') }}</h2>
    </div>

    <div class="stack-grid">
      @foreach ($skills as $category => $items)
        <div class="stack-col">
          <h3>{{ $category }}</h3>
          <ul>
            @foreach ($items as $skill)
              <li>{{ $skill->name }}</li>
            @endforeach
          </ul>
        </div>
      @endforeach
    </div>
  </div>
</section>

<section id="work">
  <div class="wrap">
    <div class="section-head">
      <div class="section-label">{{ __('site.work_label') }}</div>
      <h2>{{ __('site.work_headline') }}</h2>
    </div>

    <div class="work-list">
      @forelse ($projects as $project)
        <div class="work-item">
          <div class="work-year">{{ $project->year }} — {{ $project->client_name }}</div>
          <div>
            @if ($project->image)
              <img class="work-image" src="{{ $project->image_url }}" alt="{{ $project->image_alt ?: $project->name }}" loading="lazy" decoding="async">
            @endif
            <div class="work-name">
              @if ($project->body)
                <a href="{{ localized_route('project.show', $project->slug) }}">{{ $project->name }} →</a>
              @else
                {{ $project->name }}
              @endif
            </div>
            <div class="work-desc">{{ $project->description }}</div>
            @if ($project->outcome)
              <div style="margin-top:10px;font-family:var(--mono);font-size:12px;color:var(--accent);">↳ {{ $project->outcome }}</div>
            @endif
          </div>
          <div class="work-tags">
            @foreach ($project->tag_list as $tag)
              <span class="tag">{{ $tag }}</span>
            @endforeach
          </div>
        </div>
      @empty
        <p style="color:var(--ink-soft)">{{ __('site.work_no_projects') }}</p>
      @endforelse
    </div>
  </div>
</section>

<section id="process">
  <div class="wrap">
    <div class="section-head">
      <div class="section-label">{{ __('site.process_label') }}</div>
      <h2>{{ __('site.process_headline') }}</h2>
    </div>

    <div class="process-grid">
      <div class="process-step">
        <div class="process-num">01</div>
        <h3>{{ __('site.process_1_title') }}</h3>
        <p>{{ __('site.process_1_body') }}</p>
      </div>
      <div class="process-step">
        <div class="process-num">02</div>
        <h3>{{ __('site.process_2_title') }}</h3>
        <p>{{ __('site.process_2_body') }}</p>
      </div>
      <div class="process-step">
        <div class="process-num">03</div>
        <h3>{{ __('site.process_3_title') }}</h3>
        <p>{{ __('site.process_3_body') }}</p>
      </div>
      <div class="process-step">
        <div class="process-num">04</div>
        <h3>{{ __('site.process_4_title') }}</h3>
        <p>{{ __('site.process_4_body') }}</p>
      </div>
    </div>
  </div>
</section>

<section id="services">
  <div class="wrap">
    <div class="section-head">
      <div class="section-label">{{ __('site.services_label') }}</div>
      <h2>{{ __('site.services_headline') }}</h2>
    </div>

    <div class="services-grid">
      <div class="service-card">
        <h3>{{ __('site.service_1_title') }}</h3>
        <p>{{ __('site.service_1_body') }}</p>
        <ul>
          @foreach (__('site.service_1_items') as $item)
            <li>{{ $item }}</li>
          @endforeach
        </ul>
        <div class="service-price">{{ __('site.service_1_price', ['rate' => $profile->rate]) }}</div>
      </div>
      <div class="service-card">
        <h3>{{ __('site.service_2_title') }}</h3>
        <p>{{ __('site.service_2_body') }}</p>
        <ul>
          @foreach (__('site.service_2_items') as $item)
            <li>{{ $item }}</li>
          @endforeach
        </ul>
        <div class="service-price">{{ __('site.service_2_price', ['rate' => $profile->rate]) }}</div>
      </div>
      <div class="service-card">
        <h3>{{ __('site.service_3_title') }}</h3>
        <p>{{ __('site.service_3_body') }}</p>
        <ul>
          @foreach (__('site.service_3_items') as $item)
            <li>{{ $item }}</li>
          @endforeach
        </ul>
        <div class="service-price">{{ __('site.service_3_price') }}</div>
      </div>
    </div>
  </div>
</section>

@if ($testimonials->isNotEmpty())
<section class="testimonial">
  <div class="wrap">
    <div id="testimonial-carousel" style="position:relative;">
      <div class="testimonial-slides">
        @foreach ($testimonials as $i => $testimonial)
          <div class="testimonial-slide {{ $i === 0 ? 'active' : '' }}" @if($i > 0) aria-hidden="true" @endif>
            <span class="quote-mark">"</span>
            <blockquote>{{ $testimonial->quote }}</blockquote>
            <div class="testimonial-attr">— {{ $testimonial->author_name }}{{ $testimonial->author_role ? ', '.$testimonial->author_role : '' }}{{ $testimonial->company_name ? ' · '.$testimonial->company_name : '' }}</div>
          </div>
        @endforeach
      </div>
      @if ($testimonials->count() > 1)
        <div style="margin-top:32px;display:flex;gap:8px;align-items:center;">
          @foreach ($testimonials as $i => $t)
            <button class="carousel-dot {{ $i === 0 ? 'active' : '' }}" data-index="{{ $i }}" aria-label="{{ __('site.testimonial_aria', ['number' => $i + 1]) }}" style="width:8px;height:8px;border-radius:50%;border:none;cursor:pointer;padding:0;background:{{ $i === 0 ? 'var(--ink)' : 'var(--line)' }};transition:background .2s;"></button>
          @endforeach
        </div>
      @endif
    </div>
  </div>
</section>
@endif

<section id="contact" class="contact">
  <div class="wrap contact-inner">
    <div>
      <div class="section-label" style="margin-bottom:16px;">{{ __('site.contact_label') }}</div>
      <h2>{{ __('site.contact_headline') }}</h2>
      <p class="lead">{{ __('site.contact_lead') }}</p>

      <div class="contact-links">
        @if ($profile->email)
        <a class="contact-row" href="mailto:{{ $profile->email }}"><span>Email</span> {{ $profile->email }}</a>
        @endif
        @if ($profile->linkedin_url)
        <a class="contact-row" href="{{ $profile->linkedin_url }}" target="_blank"><span>LinkedIn</span> {{ $profile->linkedin_url }}</a>
        @endif
        @if ($profile->github_url)
        <a class="contact-row" href="{{ $profile->github_url }}" target="_blank"><span>GitHub</span> {{ $profile->github_url }}</a>
        @endif
      </div>

      <div class="contact-form">
        <h3>{{ __('site.contact_form_title') }}</h3>

        @if (session('status'))
          <div class="form-status" role="status" id="form-feedback">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('contact.store') }}">
          @csrf
          <input type="text" name="website" class="honeypot" tabindex="-1" autocomplete="off">
          <div class="contact-form-grid">
            <div>
              <label for="contact-name">{{ __('site.contact_name') }}</label>
              <input id="contact-name" type="text" name="name" value="{{ old('name') }}" required @error('name') class="has-error" aria-invalid="true" aria-describedby="error-name" @enderror>
              @error('name')<div class="field-error" id="error-name">{{ $message }}</div>@enderror
            </div>
            <div>
              <label for="contact-email">{{ __('site.contact_email') }}</label>
              <input id="contact-email" type="email" name="email" value="{{ old('email') }}" required @error('email') class="has-error" aria-invalid="true" aria-describedby="error-email" @enderror>
              @error('email')<div class="field-error" id="error-email">{{ $message }}</div>@enderror
            </div>
          </div>
          <div class="contact-form-grid">
            <div>
              <label for="contact-company">{{ __('site.contact_company') }}</label>
              <input id="contact-company" type="text" name="company" value="{{ old('company') }}">
            </div>
            <div>
              <label for="contact-budget">{{ __('site.contact_budget') }}</label>
              <select id="contact-budget" name="budget">
                @foreach (__('site.contact_budget_options') as $value => $label)
                  <option value="{{ $value }}" {{ old('budget') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
              </select>
            </div>
          </div>
          <label for="contact-message">{{ __('site.contact_message') }}</label>
          <textarea id="contact-message" name="message" rows="5" required placeholder="{{ __('site.contact_message_hint') }}" @error('message') class="has-error" aria-invalid="true" aria-describedby="error-message" @enderror>{{ old('message') }}</textarea>
          @error('message')<div class="field-error" id="error-message" style="margin-top:0;margin-bottom:12px;">{{ $message }}</div>@enderror
          <div class="field-hint">{{ __('site.contact_message_hint') }}</div>
          <button class="btn-primary" type="submit" style="border:none;cursor:pointer;">{{ __('site.contact_submit') }}</button>
          <p class="response-note">{{ __('site.contact_response_time') }}</p>
        </form>
      </div>
    </div>

    <div class="meta-box">
      <div><span>{{ __('site.meta_based_in') }}</span><strong>{{ $profile->city }}, NL</strong></div>
      <div><span>{{ __('site.meta_rate') }}</span><strong>{{ $profile->rate }}</strong></div>
      <div><span>{{ __('site.meta_availability') }}</span><strong>{{ $profile->available ? __('site.meta_availability_now') : __('site.meta_availability_from', ['date' => $profile->availability_from]) }}</strong></div>
      <div><span>{{ __('site.meta_languages_label') }}</span><strong>{{ __('site.meta_languages') }}</strong></div>
      <div><span>{{ __('site.meta_remote_label') }}</span><strong>{{ __('site.meta_remote') }}</strong></div>
    </div>
  </div>
</section>

</main>

<footer>
  <div class="wrap">
    <span>© {{ date('Y') }} {{ $profile->name }}. {{ __('site.footer_built') }}</span>
    @if ($profile->kvk_number)
      <span>KVK {{ $profile->kvk_number }}</span>
    @endif
  </div>
</footer>

<script>
  @if (session('status') || $errors->any())
  document.getElementById('contact').scrollIntoView({ behavior: 'instant', block: 'start' });
  @endif

  (function(){
    var burger = document.querySelector('.nav-burger');
    var menu = document.getElementById('mobile-menu');
    if (!burger || !menu) return;
    burger.addEventListener('click', function(){
      var open = menu.classList.toggle('open');
      burger.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    menu.querySelectorAll('a').forEach(function(link){
      link.addEventListener('click', function(){
        menu.classList.remove('open');
        burger.setAttribute('aria-expanded', 'false');
      });
    });
  })();

  (function(){
    var ref = new URLSearchParams(window.location.search).get('ref');
    if (!ref) return;
    var ta = document.getElementById('contact-message');
    if (ta && !ta.value) {
      ta.value = "I’m interested in something similar to your “" + ref.replace(/-/g, ' ').replace(/\b\w/g, function(c){ return c.toUpperCase(); }) + "” work.";
    }
  })();

  (function(){
    var slides = document.querySelectorAll('.testimonial-slide');
    var dots = document.querySelectorAll('.carousel-dot');
    if (slides.length < 2) return;
    var current = 0;
    function show(n) {
      slides[current].classList.remove('active');
      slides[current].setAttribute('aria-hidden', 'true');
      dots[current].style.background = 'var(--line)';
      current = n;
      slides[current].classList.add('active');
      slides[current].removeAttribute('aria-hidden');
      dots[current].style.background = 'var(--ink)';
    }
    dots.forEach(function(dot) {
      dot.addEventListener('click', function(){ show(parseInt(dot.dataset.index)); });
    });

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    var timer = setInterval(advance, 6000);
    function advance(){ show((current + 1) % slides.length); }
    function pause(){ clearInterval(timer); }
    function resume(){ clearInterval(timer); timer = setInterval(advance, 6000); }
    var carousel = document.getElementById('testimonial-carousel');
    carousel.addEventListener('mouseenter', pause);
    carousel.addEventListener('mouseleave', resume);
    carousel.addEventListener('focusin', pause);
    carousel.addEventListener('focusout', resume);
  })();

  function updateClock(){
    const el = document.getElementById('local-time');
    const time = new Intl.DateTimeFormat('en-GB', { hour:'2-digit', minute:'2-digit', timeZone:'Europe/Amsterdam' }).format(new Date());
    el.textContent = time + ' ' + @json(__('site.hero_local_time', ['city' => $profile->city]));
  }
  updateClock();
  setInterval(updateClock, 30000);
</script>

</body>
</html>
