@extends('layouts.public')

@section('head')
@include('partials.seo', [
    'title' => __('docs.page_title').' · '.$profile->name,
    'description' => __('docs.page_description'),
    'canonicalRoute' => 'docs',
])
@endsection

@push('styles')
<style>
  .wrap-narrow{max-width:760px;margin:0 auto;padding:0 28px;}
  .page-header{padding:56px 0 40px;border-bottom:1px solid var(--line);}
  .page-header .eyebrow{display:block;margin-bottom:14px;}
  .page-header h1{font-size:clamp(2rem,4.6vw,3rem);line-height:1.05;margin-bottom:18px;}
  .lead{font-size:17px;color:var(--muted);max-width:54ch;}

  .toc{padding:34px 0;border-bottom:1px solid var(--line);}
  .toc-list{display:flex;flex-direction:column;gap:11px;}
  .toc-list a{font-family:var(--mono);font-size:13px;color:var(--muted);display:flex;align-items:center;gap:12px;}
  .toc-list a:hover{color:var(--ink);}
  .toc-num{color:var(--primary);min-width:24px;}

  .doc-section{padding:52px 0;border-bottom:1px solid var(--line);scroll-margin-top:82px;}
  .section-num{font-family:var(--mono);font-size:12px;color:var(--primary);margin-bottom:14px;}
  .doc-section h2{font-size:1.55rem;margin-bottom:16px;}
  .doc-section h3{font-size:1.05rem;margin-bottom:8px;}
  .doc-section p{color:var(--muted);margin-bottom:1em;max-width:62ch;}
  .doc-section p:last-child{margin-bottom:0;}

  .pricing-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:28px;}
  .pricing-cell{border:1px solid var(--line);border-radius:var(--r-lg);padding:22px;background:var(--surface);}
  .label{font-family:var(--mono);font-size:11px;color:var(--primary);text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px;}

  .callout{background:var(--soft);border-radius:var(--r);padding:18px 20px;margin-top:28px;}
  .callout p{color:var(--deep);max-width:none;margin:0;}

  .two-col{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:28px;}
  .card{border:1px solid var(--line);border-radius:var(--r-lg);padding:22px;background:var(--surface);}
  .card ul{list-style:none;margin-top:12px;}
  .card ul li{font-size:14px;color:var(--muted);padding:8px 0;border-bottom:1px solid var(--line);}
  .card ul li:last-child{border-bottom:none;}

  .check-list{list-style:none;margin-top:22px;}
  .check-list li{display:flex;gap:12px;padding:10px 0;border-bottom:1px solid var(--line);font-size:14px;color:var(--muted);}
  .check-list li:last-child{border-bottom:none;}
  .check{color:var(--primary);font-family:var(--mono);font-size:13px;min-width:16px;}

  .stack-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:20px;margin-top:28px;}
  .stack-col h3{font-family:var(--mono);font-size:11px;text-transform:uppercase;letter-spacing:.06em;color:var(--primary);margin-bottom:12px;}
  .stack-col ul{list-style:none;}
  .stack-col ul li{font-size:14px;color:var(--muted);padding:5px 0;}

  .project-list{border-top:1px solid var(--line);}
  .project-row{display:flex;justify-content:space-between;align-items:flex-start;padding:16px 0;border-bottom:1px solid var(--line);}
  .project-tags{display:flex;flex-wrap:wrap;gap:6px;}
  .proj-meta{font-family:var(--mono);font-size:12px;color:var(--muted);white-space:nowrap;margin-left:16px;}

  .timeline{margin-top:28px;}
  .tl-item{display:grid;grid-template-columns:120px 1fr;gap:24px;padding:22px 0;border-bottom:1px solid var(--line);}
  .tl-item:last-child{border-bottom:none;}
  .tl-day{font-family:var(--mono);font-size:12px;color:var(--muted);padding-top:4px;}

  .stack-row{display:flex;justify-content:space-between;padding:12px 0;border-bottom:1px solid var(--line);font-size:14px;}
  .stack-row:last-child{border-bottom:none;}
  .why{color:var(--muted);text-align:right;max-width:55%;}

  @media(max-width:720px){
    .pricing-grid,.two-col,.stack-grid{grid-template-columns:1fr;}
    .tl-item{grid-template-columns:1fr;}
  }
</style>
@endpush

@section('content')
<div class="wrap-narrow">
  <div class="page-header">
    <span class="eyebrow">{{ __('docs.page_eyebrow') }}</span>
    <h1>{{ __('docs.page_headline') }}</h1>
    <p class="lead">{{ $profile->docsIntro() ?: __('docs.page_lead') }}</p>
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
      <a href="#communication"><span class="toc-num">09</span>{{ __('docs.toc_10') }}</a>
      <a href="#start"><span class="toc-num">10</span>{{ __('docs.toc_11') }}</a>
    </div>
  </div>

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
        <div class="label">{{ __('docs.s01_day_label') }}</div>
        <h3>{{ __('docs.s01_day_title') }}</h3>
        <p>{{ __('docs.s01_day_body') }}</p>
      </div>
    </div>
    <div class="callout"><p>{{ __('docs.s01_callout') }}</p></div>
  </div>

  <div class="doc-section" id="deliverables">
    <div class="section-num">02</div>
    <h2>{{ __('docs.s02_headline') }}</h2>
    <p>{{ __('docs.s02_lead') }}</p>
    <div class="two-col">
      <div class="card">
        <h3>{{ __('docs.s02_code_title') }}</h3>
        <ul><li>{{ __('docs.s02_code_1') }}</li><li>{{ __('docs.s02_code_2') }}</li><li>{{ __('docs.s02_code_3') }}</li><li>{{ __('docs.s02_code_4') }}</li></ul>
      </div>
      <div class="card">
        <h3>{{ __('docs.s02_docs_title') }}</h3>
        <ul><li>{{ __('docs.s02_docs_1') }}</li><li>{{ __('docs.s02_docs_2') }}</li><li>{{ __('docs.s02_docs_3') }}</li><li>{{ __('docs.s02_docs_4') }}</li></ul>
      </div>
    </div>
    <ul class="check-list" style="margin-top:36px;">
      <li><span class="check">&checkmark;</span>{{ __('docs.s02_check_1') }}</li>
      <li><span class="check">&checkmark;</span>{{ __('docs.s02_check_2') }}</li>
      <li><span class="check">&checkmark;</span>{{ __('docs.s02_check_3') }}</li>
      <li><span class="check">&checkmark;</span>{{ __('docs.s02_check_4') }}</li>
      <li><span class="check">&checkmark;</span>{{ __('docs.s02_check_5') }}</li>
    </ul>
  </div>

  <div class="doc-section" id="tech">
    <div class="section-num">03</div>
    <h2>{{ __('docs.s03_headline') }}</h2>
    <p>{{ __('docs.s03_lead') }}</p>
    <div class="stack-grid">
      @foreach ($skills as $category => $items)
        <div class="stack-col"><h3>{{ $category }}</h3><ul>@foreach ($items as $skill)<li>{{ $skill->name }}</li>@endforeach</ul></div>
      @endforeach
    </div>
    <p style="margin-top:28px;">{{ __('docs.s03_avoid') }}</p>
    @if ($projects->isNotEmpty())
      <div style="margin-top:44px;">
        <div style="font-family:var(--mono);font-size:12px;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:16px;">{{ __('docs.s03_recent_label') }}</div>
        <div class="project-list">
          @foreach ($projects as $project)
            <div class="project-row">
              <div>
                <div style="font-family:var(--geo);font-weight:600;">
                  @if ($project->body)<a href="{{ localized_route('project.show', $project->slug) }}" style="color:var(--ink);">{{ $project->name }}</a>@else{{ $project->name }}@endif
                </div>
                @if ($project->tagList())
                  <div class="project-tags" style="margin-top:6px;">@foreach ($project->tagList() as $tag)<span class="tag">{{ $tag }}</span>@endforeach</div>
                @endif
              </div>
              <div class="proj-meta">{{ $project->client_name }}{{ $project->client_name && $project->year ? ' · ' : '' }}{{ $project->year }}</div>
            </div>
          @endforeach
        </div>
      </div>
    @endif
  </div>

  <div class="doc-section" id="revisions">
    <div class="section-num">04</div>
    <h2>{{ __('docs.s04_headline') }}</h2>
    <p>{{ __('docs.s04_lead') }}</p>
    <div class="timeline">
      @foreach (range(1, 4) as $i)
        <div class="tl-item">
          <div class="tl-day">{{ __("docs.s04_tl{$i}_when") }}</div>
          <div><h3>{{ __("docs.s04_tl{$i}_title") }}</h3><p>{{ __("docs.s04_tl{$i}_body") }}</p></div>
        </div>
      @endforeach
    </div>
  </div>

  <div class="doc-section" id="after-launch">
    <div class="section-num">05</div>
    <h2>{{ __('docs.s05_headline') }}</h2>
    <p>{{ __('docs.s05_lead') }}</p>
    <div class="two-col" style="margin-top:28px;">
      <div class="card"><h3>{{ __('docs.s05_inc_title') }}</h3><ul><li>{{ __('docs.s05_inc_1') }}</li><li>{{ __('docs.s05_inc_2') }}</li><li>{{ __('docs.s05_inc_3') }}</li><li>{{ __('docs.s05_inc_4') }}</li></ul></div>
      <div class="card"><h3>{{ __('docs.s05_ret_title') }}</h3><ul><li>{{ __('docs.s05_ret_1') }}</li><li>{{ __('docs.s05_ret_2') }}</li><li>{{ __('docs.s05_ret_3') }}</li><li>{{ __('docs.s05_ret_4') }}</li></ul></div>
    </div>
    <div class="callout"><p>{{ __('docs.s05_callout') }}</p></div>
  </div>

  <div class="doc-section" id="contract">
    <div class="section-num">06</div>
    <h2>{{ __('docs.s06_headline') }}</h2>
    <p>{{ __('docs.s06_lead') }}</p>
    <div class="two-col" style="margin-top:28px;">
      <div><h3>{{ __('docs.s06_contract_title') }}</h3><p>{!! __('docs.s06_contract_body') !!}</p><p style="margin-top:14px;">{{ __('docs.s06_contract_nda') }}</p></div>
      <div><h3>{{ __('docs.s06_ip_title') }}</h3><p>{{ __('docs.s06_ip_body') }}</p><p style="margin-top:14px;">{{ __('docs.s06_ip_portfolio') }}</p></div>
    </div>
    <div class="callout"><p>{{ __('docs.s06_callout') }}</p></div>
  </div>

  <div class="doc-section" id="payment">
    <div class="section-num">07</div>
    <h2>{{ __('docs.s07_headline') }}</h2>
    <p>{{ __('docs.s07_lead') }}</p>
    <div class="timeline">
      @foreach (range(1, 3) as $i)
        <div class="tl-item">
          <div class="tl-day">{{ __("docs.s07_tl{$i}_when") }}</div>
          <div><h3>{{ __("docs.s07_tl{$i}_title") }}</h3><p>{{ __("docs.s07_tl{$i}_body") }}</p></div>
        </div>
      @endforeach
    </div>
    <div style="margin-top:28px;">
      <div class="stack-row"><span><strong>{{ __('docs.s07_methods') }}</strong></span><span class="why">{{ __('docs.s07_methods_val') }}</span></div>
      <div class="stack-row"><span><strong>{{ __('docs.s07_currency') }}</strong></span><span class="why">{{ __('docs.s07_currency_val') }}</span></div>
      <div class="stack-row"><span><strong>{{ __('docs.s07_vat') }}</strong></span><span class="why">{{ __('docs.s07_vat_val') }}</span></div>
      <div class="stack-row"><span><strong>{{ __('docs.s07_late') }}</strong></span><span class="why">{{ __('docs.s07_late_val') }}</span></div>
    </div>
  </div>

  <div class="doc-section" id="privacy">
    <div class="section-num">08</div>
    <h2>{{ __('docs.s08_headline') }}</h2>
    <p>{{ __('docs.s08_lead') }}</p>
    <ul class="check-list">
      <li><span class="check">&checkmark;</span>{{ __('docs.s08_check_1') }}</li>
      <li><span class="check">&checkmark;</span>{{ __('docs.s08_check_2') }}</li>
      <li><span class="check">&checkmark;</span>{{ __('docs.s08_check_3') }}</li>
      <li><span class="check">&checkmark;</span>{{ __('docs.s08_check_4') }}</li>
      <li><span class="check">&checkmark;</span>{{ __('docs.s08_check_5') }}</li>
    </ul>
    <div class="callout"><p>{{ __('docs.s08_callout') }}</p></div>
  </div>

  {{-- FAQ (section 09) hidden pre-launch (CMS-88); needs updating before it goes back. --}}

  <div class="doc-section" id="communication">
    <div class="section-num">09</div>
    <h2>{{ __('docs.s10_headline') }}</h2>
    <p>{{ __('docs.s10_lead') }}</p>
    <div style="margin-top:28px;">
      <div class="stack-row"><span><strong>{{ __('docs.s10_hours') }}</strong></span><span class="why">{{ __('docs.s10_hours_val') }}</span></div>
      <div class="stack-row"><span><strong>{{ __('docs.s10_response') }}</strong></span><span class="why">{{ __('docs.s10_response_val') }}</span></div>
      <div class="stack-row"><span><strong>{{ __('docs.s10_updates') }}</strong></span><span class="why">{{ __('docs.s10_updates_val') }}</span></div>
      <div class="stack-row"><span><strong>{{ __('docs.s10_channels') }}</strong></span><span class="why">{{ __('docs.s10_channels_val') }}</span></div>
      <div class="stack-row"><span><strong>{{ __('docs.s10_tracking') }}</strong></span><span class="why">{{ __('docs.s10_tracking_val') }}</span></div>
      <div class="stack-row"><span><strong>{{ __('docs.s10_review') }}</strong></span><span class="why">{{ __('docs.s10_review_val') }}</span></div>
      <div class="stack-row"><span><strong>{{ __('docs.s10_lang') }}</strong></span><span class="why">{{ __('docs.s10_lang_val') }}</span></div>
    </div>
    <div class="callout"><p>{{ __('docs.s10_callout') }}</p></div>
  </div>

  <div class="doc-section" id="start">
    <div class="section-num">10</div>
    <h2>{{ __('docs.s11_headline') }}</h2>
    <p>{{ __('docs.s11_lead') }}</p>
    <ul class="check-list" style="margin-top:28px;">
      @foreach (range(1, 8) as $i)
        <li><span class="check">&rarr;</span>{!! __("docs.s11_check_{$i}") !!}</li>
      @endforeach
    </ul>
    <div style="margin-top:44px;padding-top:44px;border-top:1px solid var(--line);">
      <h3 style="font-size:1.4rem;margin-bottom:12px;">{{ __('docs.s11_ready_title') }}</h3>
      <p style="color:var(--muted);margin-bottom:22px;">{{ __('docs.s11_ready_lead') }}</p>
      <a class="btn pri" href="{{ localized_route('home') }}#contact">{{ __('docs.s11_ready_cta') }}</a>
    </div>
  </div>
</div>
@endsection
