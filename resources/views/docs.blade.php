<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
@include('partials.seo', [
    'title' => __('docs.page_title').' — '.$profile->name,
    'description' => __('docs.page_description'),
    'canonicalRoute' => 'docs',
])
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
  body{background:var(--bg);color:var(--ink);font-family:var(--body);line-height:1.6;-webkit-font-smoothing:antialiased;}
  ::selection{background:var(--accent);color:var(--white);}
  a{color:inherit;}
  a:focus-visible,button:focus-visible{outline:2px solid var(--accent);outline-offset:2px;}
  .skip-link{position:absolute;left:-9999px;top:0;z-index:100;background:var(--ink);color:var(--white);font-family:var(--mono);font-size:13px;padding:10px 16px;text-decoration:none;}
  .skip-link:focus{left:0;}
  .wrap{max-width:1120px;margin:0 auto;padding:0 32px;}
  .wrap-narrow{max-width:720px;margin:0 auto;padding:0 32px;}
  nav{position:sticky;top:0;z-index:50;background:rgba(247,247,244,.88);backdrop-filter:blur(8px);border-bottom:1px solid var(--line);}
  nav .inner{max-width:1120px;margin:0 auto;padding:0 32px;display:flex;align-items:center;justify-content:space-between;height:64px;}
  .logo{font-family:var(--mono);font-weight:500;font-size:14px;display:flex;align-items:center;gap:8px;text-decoration:none;color:var(--ink);}
  .logo .dot{width:6px;height:6px;background:var(--accent);border-radius:50%;}
  .back-link{font-family:var(--mono);font-size:13px;text-decoration:none;color:var(--ink-soft);}
  .back-link:hover{color:var(--ink);}
  .nav-right{display:flex;align-items:center;gap:16px;}
  .lang-toggle{font-family:var(--mono);font-size:12px;color:var(--ink-soft);border:1px solid var(--line);padding:4px 10px;text-decoration:none;transition:color .15s,border-color .15s;}
  .lang-toggle:hover{color:var(--ink);border-color:var(--ink);}

  .page-header{padding:72px 0 56px;border-bottom:1px solid var(--line);}
  .eyebrow{font-family:var(--mono);font-size:13px;color:var(--accent);text-transform:uppercase;letter-spacing:.08em;margin-bottom:20px;}
  h1{font-family:var(--display);font-weight:600;font-size:clamp(2rem,5vw,3rem);line-height:1.08;letter-spacing:-.02em;margin-bottom:20px;}
  .lead{font-size:17px;color:var(--ink-soft);max-width:52ch;line-height:1.6;}

  .toc{padding:40px 0;border-bottom:1px solid var(--line);}
  .toc-list{display:flex;flex-direction:column;gap:12px;}
  .toc-list a{font-family:var(--mono);font-size:13px;color:var(--ink-soft);text-decoration:none;display:flex;align-items:center;gap:12px;}
  .toc-list a:hover{color:var(--ink);}
  .toc-num{color:var(--accent);min-width:24px;}

  .doc-section{padding:56px 0;border-bottom:1px solid var(--line);scroll-margin-top:72px;}
  .section-num{font-family:var(--mono);font-size:13px;color:var(--accent);margin-bottom:16px;}
  h2{font-family:var(--display);font-weight:600;font-size:1.6rem;margin-bottom:16px;letter-spacing:-.01em;}
  h3{font-family:var(--display);font-weight:600;font-size:1.05rem;margin-bottom:8px;}
  p{color:var(--ink-soft);margin-bottom:1em;max-width:60ch;}
  p:last-child{margin-bottom:0;}

  .pricing-grid{display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-top:32px;}
  .pricing-cell{border:1px solid var(--line);padding:24px;}
  .label{font-family:var(--mono);font-size:11px;color:var(--accent);text-transform:uppercase;letter-spacing:.08em;margin-bottom:12px;}

  .callout{background:var(--accent-soft);border-left:3px solid var(--accent);padding:20px 24px;margin-top:32px;}
  .callout p{color:var(--ink);max-width:none;}

  .two-col{display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-top:32px;}
  .card{border:1px solid var(--line);padding:24px;}
  .card ul{list-style:none;margin-top:12px;}
  .card ul li{font-size:14px;color:var(--ink-soft);padding:6px 0;border-bottom:1px solid var(--line);}
  .card ul li:last-child{border-bottom:none;}

  .check-list{list-style:none;margin-top:24px;}
  .check-list li{display:flex;gap:12px;padding:10px 0;border-bottom:1px solid var(--line);font-size:14px;color:var(--ink-soft);}
  .check-list li:last-child{border-bottom:none;}
  .check{color:var(--accent);font-family:var(--mono);font-size:13px;min-width:16px;}

  .stack-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:24px;margin-top:32px;}
  .stack-col h3{font-size:.9rem;margin-bottom:12px;}
  .stack-col ul{list-style:none;}
  .stack-col ul li{font-size:14px;color:var(--ink-soft);padding:4px 0;}

  .project-list{border-top:1px solid var(--line);}
  .project-row{display:flex;justify-content:space-between;align-items:flex-start;padding:16px 0;border-bottom:1px solid var(--line);}
  .project-tags{display:flex;flex-wrap:wrap;gap:6px;}
  .tag{font-family:var(--mono);font-size:11px;color:var(--ink-soft);border:1px solid var(--line);padding:3px 8px;border-radius:20px;}
  .proj-meta{font-family:var(--mono);font-size:12px;color:var(--ink-soft);white-space:nowrap;margin-left:16px;}

  .timeline{margin-top:32px;}
  .tl-item{display:grid;grid-template-columns:120px 1fr;gap:24px;padding:24px 0;border-bottom:1px solid var(--line);}
  .tl-item:last-child{border-bottom:none;}
  .tl-day{font-family:var(--mono);font-size:12px;color:var(--ink-soft);padding-top:4px;}

  .stack-row{display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid var(--line);font-size:14px;}
  .stack-row:last-child{border-bottom:none;}
  .why{color:var(--ink-soft);text-align:right;max-width:55%;}

  footer{padding:32px 0;font-family:var(--mono);font-size:12px;color:var(--ink-soft);border-top:1px solid var(--line);}
  footer .wrap{display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px;}
  .footer-cta{font-family:var(--mono);font-size:13px;background:var(--ink);color:var(--white);padding:10px 20px;text-decoration:none;transition:background .15s;}
  .footer-cta:hover{background:var(--accent);}
  .btn-primary{font-family:var(--mono);font-size:14px;background:var(--ink);color:var(--white);padding:14px 24px;text-decoration:none;display:inline-flex;align-items:center;gap:8px;transition:background .15s;}
  .btn-primary:hover{background:var(--accent);}

  @media(max-width:720px){
    .pricing-grid,.two-col,.stack-grid{grid-template-columns:1fr;}
    .tl-item{grid-template-columns:1fr;}
  }
</style>
</head>
<body>

<a class="skip-link" href="#main">{{ __('site.skip_to_content') }}</a>

<nav>
  <div class="inner">
    <a class="logo" href="{{ localized_route('home') }}"><span class="dot"></span>{{ strtoupper($profile->name) }}</a>
    <div class="nav-right">
      <a class="lang-toggle" href="{{ alternate_locale_url(app()->getLocale() === 'en' ? 'nl' : 'en') }}">{{ __('site.lang_toggle') }}</a>
      <a class="back-link" href="{{ localized_route('home') }}">{{ __('docs.back_to_site') }}</a>
    </div>
  </div>
</nav>

<main id="main" tabindex="-1" class="wrap-narrow">

  <div class="page-header">
    <div class="eyebrow">{{ __('docs.page_eyebrow') }}</div>
    <h1>{{ __('docs.page_headline') }}</h1>
    <p class="lead">{{ $profile->docs_intro ?: __('docs.page_lead') }}</p>
  </div>

  <div class="toc">
    <div class="toc-list">
      <a href="#engagement"><span class="toc-num">01</span>{{ __('docs.toc_01') }}</a>
      <a href="#deliverables"><span class="toc-num">02</span>{{ __('docs.toc_02') }}</a>
      <a href="#tech"><span class="toc-num">03</span>{{ __('docs.toc_03') }}</a>
      <a href="#revisions"><span class="toc-num">04</span>{{ __('docs.toc_04') }}</a>
      <a href="#after-launch"><span class="toc-num">05</span>{{ __('docs.toc_05') }}</a>
      <a href="#contract"><span class="toc-num">06</span>{{ __('docs.toc_06') }}</a>
      <a href="#payment"><span class="toc-num">07</span>{{ __('docs.toc_07') }}</a>
      <a href="#privacy"><span class="toc-num">08</span>{{ __('docs.toc_08') }}</a>
      <a href="#faq"><span class="toc-num">09</span>{{ __('docs.toc_09') }}</a>
      <a href="#communication"><span class="toc-num">10</span>{{ __('docs.toc_10') }}</a>
      <a href="#start"><span class="toc-num">11</span>{{ __('docs.toc_11') }}</a>
    </div>
  </div>

  <!-- 01 -->
  <div class="doc-section" id="engagement">
    <div class="section-num">01</div>
    <h2>{{ __('docs.s01_headline') }}</h2>
    <p>{{ __('docs.s01_lead') }}</p>
    <div class="pricing-grid">
      <div class="pricing-cell">
        <div class="label">{{ __('docs.s01_fixed_label') }}</div>
        <h3>{{ __('docs.s01_fixed_title') }}</h3>
        <p>{{ __('docs.s01_fixed_body') }}</p>
      </div>
      <div class="pricing-cell">
        <div class="label">{{ __('docs.s01_day_label') }}{{ $profile->rate ? ' — '.$profile->rate : '' }}</div>
        <h3>{{ __('docs.s01_day_title') }}</h3>
        <p>{{ __('docs.s01_day_body') }}</p>
      </div>
    </div>
    <div class="callout" style="margin-top:32px;">
      <p>{{ __('docs.s01_callout') }}</p>
    </div>
  </div>

  <!-- 02 -->
  <div class="doc-section" id="deliverables">
    <div class="section-num">02</div>
    <h2>{{ __('docs.s02_headline') }}</h2>
    <p>{{ __('docs.s02_lead') }}</p>
    <div class="two-col">
      <div class="card">
        <h3>{{ __('docs.s02_code_title') }}</h3>
        <ul>
          <li>{{ __('docs.s02_code_1') }}</li>
          <li>{{ __('docs.s02_code_2') }}</li>
          <li>{{ __('docs.s02_code_3') }}</li>
          <li>{{ __('docs.s02_code_4') }}</li>
        </ul>
      </div>
      <div class="card">
        <h3>{{ __('docs.s02_docs_title') }}</h3>
        <ul>
          <li>{{ __('docs.s02_docs_1') }}</li>
          <li>{{ __('docs.s02_docs_2') }}</li>
          <li>{{ __('docs.s02_docs_3') }}</li>
          <li>{{ __('docs.s02_docs_4') }}</li>
        </ul>
      </div>
    </div>
    <ul class="check-list" style="margin-top:40px;">
      <li><span class="check">✓</span>{{ __('docs.s02_check_1') }}</li>
      <li><span class="check">✓</span>{{ __('docs.s02_check_2') }}</li>
      <li><span class="check">✓</span>{{ __('docs.s02_check_3') }}</li>
      <li><span class="check">✓</span>{{ __('docs.s02_check_4') }}</li>
      <li><span class="check">✓</span>{{ __('docs.s02_check_5') }}</li>
    </ul>
  </div>

  <!-- 03 -->
  <div class="doc-section" id="tech">
    <div class="section-num">03</div>
    <h2>{{ __('docs.s03_headline') }}</h2>
    <p>{{ __('docs.s03_lead') }}</p>
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
    <p style="margin-top:32px;">{{ __('docs.s03_avoid') }}</p>
    @if ($projects->isNotEmpty())
      <div style="margin-top:48px;">
        <div style="font-family:var(--mono);font-size:12px;color:var(--ink-soft);text-transform:uppercase;letter-spacing:.06em;margin-bottom:16px;">{{ __('docs.s03_recent_label') }}</div>
        <div class="project-list">
          @foreach ($projects as $project)
            <div class="project-row">
              <div>
                <div style="font-weight:500;">
                  @if ($project->body)
                    <a href="{{ localized_route('project.show', $project->slug) }}" style="text-decoration:none;color:var(--ink);">{{ $project->name }}</a>
                  @else
                    {{ $project->name }}
                  @endif
                </div>
                @if ($project->tagList())
                  <div class="project-tags" style="margin-top:6px;">
                    @foreach ($project->tagList() as $tag)
                      <span class="tag">{{ $tag }}</span>
                    @endforeach
                  </div>
                @endif
              </div>
              <div class="proj-meta">{{ $project->client_name }}@if($project->year) · {{ $project->year }}@endif</div>
            </div>
          @endforeach
        </div>
      </div>
    @endif
  </div>

  <!-- 04 -->
  <div class="doc-section" id="revisions">
    <div class="section-num">04</div>
    <h2>{{ __('docs.s04_headline') }}</h2>
    <p>{{ __('docs.s04_lead') }}</p>
    <div class="timeline">
      <div class="tl-item">
        <div class="tl-day">{{ __('docs.s04_tl1_when') }}</div>
        <div>
          <h3>{{ __('docs.s04_tl1_title') }}</h3>
          <p>{{ __('docs.s04_tl1_body') }}</p>
        </div>
      </div>
      <div class="tl-item">
        <div class="tl-day">{{ __('docs.s04_tl2_when') }}</div>
        <div>
          <h3>{{ __('docs.s04_tl2_title') }}</h3>
          <p>{{ __('docs.s04_tl2_body') }}</p>
        </div>
      </div>
      <div class="tl-item">
        <div class="tl-day">{{ __('docs.s04_tl3_when') }}</div>
        <div>
          <h3>{{ __('docs.s04_tl3_title') }}</h3>
          <p>{{ __('docs.s04_tl3_body') }}</p>
        </div>
      </div>
      <div class="tl-item">
        <div class="tl-day">{{ __('docs.s04_tl4_when') }}</div>
        <div>
          <h3>{{ __('docs.s04_tl4_title') }}</h3>
          <p>{{ __('docs.s04_tl4_body') }}</p>
        </div>
      </div>
    </div>
  </div>

  <!-- 05 -->
  <div class="doc-section" id="after-launch">
    <div class="section-num">05</div>
    <h2>{{ __('docs.s05_headline') }}</h2>
    <p>{{ __('docs.s05_lead') }}</p>
    <div class="two-col" style="margin-top:32px;">
      <div class="card">
        <h3>{{ __('docs.s05_inc_title') }}</h3>
        <ul>
          <li>{{ __('docs.s05_inc_1') }}</li>
          <li>{{ __('docs.s05_inc_2') }}</li>
          <li>{{ __('docs.s05_inc_3') }}</li>
          <li>{{ __('docs.s05_inc_4') }}</li>
        </ul>
      </div>
      <div class="card">
        <h3>{{ __('docs.s05_ret_title') }}</h3>
        <ul>
          <li>{{ __('docs.s05_ret_1') }}</li>
          <li>{{ __('docs.s05_ret_2') }}</li>
          <li>{{ __('docs.s05_ret_3') }}</li>
          <li>{{ __('docs.s05_ret_4') }}</li>
        </ul>
      </div>
    </div>
    <div class="callout">
      <p>{{ __('docs.s05_callout') }}</p>
    </div>
  </div>

  <!-- 06 -->
  <div class="doc-section" id="contract">
    <div class="section-num">06</div>
    <h2>{{ __('docs.s06_headline') }}</h2>
    <p>{{ __('docs.s06_lead') }}</p>
    <div class="two-col" style="margin-top:32px;">
      <div>
        <h3>{{ __('docs.s06_contract_title') }}</h3>
        <p>{!! __('docs.s06_contract_body') !!}</p>
        <p style="margin-top:16px;">{{ __('docs.s06_contract_nda') }}</p>
      </div>
      <div>
        <h3>{{ __('docs.s06_ip_title') }}</h3>
        <p>{{ __('docs.s06_ip_body') }}</p>
        <p style="margin-top:16px;">{{ __('docs.s06_ip_portfolio') }}</p>
      </div>
    </div>
    <div class="callout" style="margin-top:32px;">
      <p>{{ __('docs.s06_callout') }}</p>
    </div>
  </div>

  <!-- 07 -->
  <div class="doc-section" id="payment">
    <div class="section-num">07</div>
    <h2>{{ __('docs.s07_headline') }}</h2>
    <p>{{ __('docs.s07_lead') }}</p>
    <div class="timeline">
      <div class="tl-item">
        <div class="tl-day">{{ __('docs.s07_tl1_when') }}</div>
        <div>
          <h3>{{ __('docs.s07_tl1_title') }}</h3>
          <p>{{ __('docs.s07_tl1_body') }}</p>
        </div>
      </div>
      <div class="tl-item">
        <div class="tl-day">{{ __('docs.s07_tl2_when') }}</div>
        <div>
          <h3>{{ __('docs.s07_tl2_title') }}</h3>
          <p>{{ __('docs.s07_tl2_body') }}</p>
        </div>
      </div>
      <div class="tl-item">
        <div class="tl-day">{{ __('docs.s07_tl3_when') }}</div>
        <div>
          <h3>{{ __('docs.s07_tl3_title') }}</h3>
          <p>{{ __('docs.s07_tl3_body') }}</p>
        </div>
      </div>
    </div>
    <div style="margin-top:32px;">
      <div class="stack-row">
        <span><strong>{{ __('docs.s07_methods') }}</strong></span>
        <span class="why">{{ __('docs.s07_methods_val') }}</span>
      </div>
      <div class="stack-row">
        <span><strong>{{ __('docs.s07_currency') }}</strong></span>
        <span class="why">{{ __('docs.s07_currency_val') }}</span>
      </div>
      <div class="stack-row">
        <span><strong>{{ __('docs.s07_vat') }}</strong></span>
        <span class="why">{{ __('docs.s07_vat_val') }}</span>
      </div>
      <div class="stack-row">
        <span><strong>{{ __('docs.s07_late') }}</strong></span>
        <span class="why">{{ __('docs.s07_late_val') }}</span>
      </div>
    </div>
  </div>

  <!-- 08 -->
  <div class="doc-section" id="privacy">
    <div class="section-num">08</div>
    <h2>{{ __('docs.s08_headline') }}</h2>
    <p>{{ __('docs.s08_lead') }}</p>
    <ul class="check-list">
      <li><span class="check">✓</span>{{ __('docs.s08_check_1') }}</li>
      <li><span class="check">✓</span>{{ __('docs.s08_check_2') }}</li>
      <li><span class="check">✓</span>{{ __('docs.s08_check_3') }}</li>
      <li><span class="check">✓</span>{{ __('docs.s08_check_4') }}</li>
      <li><span class="check">✓</span>{{ __('docs.s08_check_5') }}</li>
    </ul>
    <div class="callout">
      <p>{{ __('docs.s08_callout') }}</p>
    </div>
  </div>

  <!-- 09 -->
  <div class="doc-section" id="faq">
    <div class="section-num">09</div>
    <h2>{{ __('docs.s09_headline') }}</h2>
    <div style="margin-top:32px;">
      @foreach (range(1, 7) as $i)
        <div style="padding:24px 0;border-top:1px solid var(--line);">
          <h3>{{ __("docs.s09_q{$i}") }}</h3>
          <p>{{ __("docs.s09_a{$i}") }}</p>
        </div>
      @endforeach
    </div>
  </div>

  <!-- 10 -->
  <div class="doc-section" id="communication">
    <div class="section-num">10</div>
    <h2>{{ __('docs.s10_headline') }}</h2>
    <p>{{ __('docs.s10_lead') }}</p>
    <div style="margin-top:32px;">
      <div class="stack-row">
        <span><strong>{{ __('docs.s10_hours') }}</strong></span>
        <span class="why">{{ __('docs.s10_hours_val') }}</span>
      </div>
      <div class="stack-row">
        <span><strong>{{ __('docs.s10_response') }}</strong></span>
        <span class="why">{{ __('docs.s10_response_val') }}</span>
      </div>
      <div class="stack-row">
        <span><strong>{{ __('docs.s10_updates') }}</strong></span>
        <span class="why">{{ __('docs.s10_updates_val') }}</span>
      </div>
      <div class="stack-row">
        <span><strong>{{ __('docs.s10_channels') }}</strong></span>
        <span class="why">{{ __('docs.s10_channels_val') }}</span>
      </div>
      <div class="stack-row">
        <span><strong>{{ __('docs.s10_tracking') }}</strong></span>
        <span class="why">{{ __('docs.s10_tracking_val') }}</span>
      </div>
      <div class="stack-row">
        <span><strong>{{ __('docs.s10_review') }}</strong></span>
        <span class="why">{{ __('docs.s10_review_val') }}</span>
      </div>
      <div class="stack-row">
        <span><strong>{{ __('docs.s10_lang') }}</strong></span>
        <span class="why">{{ __('docs.s10_lang_val') }}</span>
      </div>
    </div>
    <div class="callout">
      <p>{{ __('docs.s10_callout') }}</p>
    </div>
  </div>

  <!-- 11 -->
  <div class="doc-section" id="start">
    <div class="section-num">11</div>
    <h2>{{ __('docs.s11_headline') }}</h2>
    <p>{{ __('docs.s11_lead') }}</p>
    <ul class="check-list" style="margin-top:32px;">
      <li><span class="check">→</span>{!! __('docs.s11_check_1') !!}</li>
      <li><span class="check">→</span>{!! __('docs.s11_check_2') !!}</li>
      <li><span class="check">→</span>{!! __('docs.s11_check_3') !!}</li>
      <li><span class="check">→</span>{!! __('docs.s11_check_4') !!}</li>
      <li><span class="check">→</span>{!! __('docs.s11_check_5') !!}</li>
      <li><span class="check">→</span>{!! __('docs.s11_check_6') !!}</li>
      <li><span class="check">→</span>{!! __('docs.s11_check_7') !!}</li>
      <li><span class="check">→</span>{!! __('docs.s11_check_8') !!}</li>
    </ul>
    <div style="margin-top:48px;padding-top:48px;border-top:1px solid var(--line);">
      <h3 style="font-family:var(--display);font-weight:600;font-size:1.4rem;margin-bottom:12px;">{{ __('docs.s11_ready_title') }}</h3>
      <p style="margin-bottom:24px;">{{ __('docs.s11_ready_lead') }}</p>
      <a class="btn-primary" href="{{ localized_route('home') }}#contact">{{ __('docs.s11_ready_cta') }}</a>
    </div>
  </div>

</main>

<footer>
  <div class="wrap">
    <span>© {{ date('Y') }} {{ $profile->name }}. {{ __('site.footer_built') }}</span>
    <a class="footer-cta" href="{{ localized_route('home') }}">{{ __('docs.footer_back') }}</a>
  </div>
</footer>

</body>
</html>
