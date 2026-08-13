@use('App\Support\ContactFormToken')

@extends('layouts.public')

@section('head')
@include('partials.seo', [
    'title' => $profile->meta_title ?: $profile->name.' · '.$profile->tagline,
    'description' => $profile->meta_description ?: $profile->hero_subtext,
    'canonicalRoute' => 'home',
])
@include('partials.schema.person')
@include('partials.schema.website')
@endsection

@push('styles')
<style>
  .hero{padding:74px 0 60px;border-bottom:1px solid var(--line);}
  .hero .grid{display:grid;grid-template-columns:1.55fr .95fr;gap:44px;align-items:center;}
  .pill{display:inline-flex;align-items:center;gap:8px;font-family:var(--mono);font-size:12px;color:var(--ok);background:var(--ok-soft);border-radius:20px;padding:6px 13px;}
  .pill .dot{width:8px;height:8px;border-radius:50%;background:var(--ok);}
  .pill.away{color:var(--muted);background:var(--raise);}
  .pill.away .dot{background:var(--faint);}
  .hero .clock{font-family:var(--mono);font-size:12px;color:var(--muted);margin-top:12px;}
  .hero .eyebrow{display:block;margin:22px 0 14px;}
  .hero h1{font-size:clamp(2.4rem,5.4vw,4rem);line-height:1.03;max-width:16ch;}
  .hero .sub{margin-top:22px;font-size:18px;color:var(--muted);max-width:48ch;}
  .hero .acts{margin-top:30px;display:flex;gap:12px;flex-wrap:wrap;align-items:center;}
  .facts{background:var(--surface);border:1px solid var(--line);border-radius:var(--r-lg);padding:22px 24px;}
  .facts dt{font-family:var(--mono);font-size:10.5px;letter-spacing:.1em;text-transform:uppercase;color:var(--faint);margin-top:16px;}
  .facts dt:first-child{margin-top:0;}
  .facts dd{font-family:var(--geo);font-weight:600;font-size:15px;margin-top:3px;}
  @media(max-width:820px){.hero .grid{grid-template-columns:1fr;gap:28px;}}

  .stack-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:16px;}
  .stack-col{background:var(--surface);border:1px solid var(--line);border-radius:var(--r-lg);padding:22px;}
  .stack-col h3{font-family:var(--mono);font-size:11px;letter-spacing:.06em;text-transform:uppercase;color:var(--primary);margin-bottom:12px;}
  .stack-col ul{list-style:none;}
  .stack-col li{font-size:14px;color:var(--muted);padding:7px 0;border-top:1px solid var(--line);}
  .stack-col li:first-of-type{border-top:none;}
  @media(max-width:720px){.stack-grid{grid-template-columns:1fr;}}

  .proc{display:grid;grid-template-columns:repeat(4,1fr);gap:22px;}
  .step{padding-top:18px;border-top:2px solid var(--primary);}
  .step .num{font-family:var(--mono);font-size:12px;color:var(--primary);margin-bottom:10px;}
  .step h3{font-size:16px;margin-bottom:7px;}
  .step p{font-size:13.5px;color:var(--muted);}
  @media(max-width:820px){.proc{grid-template-columns:1fr 1fr;gap:24px;}}
  @media(max-width:460px){.proc{grid-template-columns:1fr;}}

  .svc{display:grid;grid-template-columns:repeat(3,1fr);gap:18px;}
  .sc{background:var(--surface);border:1px solid var(--line);border-radius:var(--r-lg);padding:24px;display:flex;flex-direction:column;}
  .sc .n{width:32px;height:32px;border-radius:9px;background:var(--soft);color:var(--deep);font-family:var(--geo);font-weight:700;font-size:15px;display:flex;align-items:center;justify-content:center;margin-bottom:16px;}
  .sc h3{font-size:17px;margin-bottom:9px;}
  .sc p{font-size:13.5px;color:var(--muted);margin-bottom:14px;}
  .sc ul{list-style:none;}
  .sc li{font-size:13px;color:var(--muted);padding:8px 0;border-top:1px solid var(--line);}
  .sc li:first-of-type{border-top:none;}
  .sc .price{margin-top:auto;padding-top:14px;border-top:1px solid var(--line-strong);font-family:var(--geo);font-weight:600;font-size:14px;color:var(--primary);}
  @media(max-width:820px){.svc{grid-template-columns:1fr;}}

  .testi{position:relative;}
  .testi-slides{display:grid;}
  .testi-slide{grid-area:1/1;visibility:hidden;opacity:0;transition:opacity .4s ease;}
  .testi-slide.active{visibility:visible;opacity:1;}
  .quote-mark{font-family:var(--geo);color:var(--primary);font-size:2.4rem;line-height:0;display:block;margin-bottom:16px;}
  .testi blockquote{font-family:var(--geo);font-weight:600;font-size:clamp(1.3rem,2.8vw,1.9rem);line-height:1.32;letter-spacing:-.01em;max-width:30ch;}
  .testi-attr{margin-top:22px;font-family:var(--mono);font-size:12.5px;color:var(--muted);}
  .carousel-dots{margin-top:28px;display:flex;gap:8px;}
  .carousel-dot{width:8px;height:8px;border-radius:50%;border:none;cursor:pointer;padding:0;transition:background .2s;}

  section.contact{border-bottom:none;}
  .contact-inner{display:grid;grid-template-columns:1.3fr .9fr;gap:44px;align-items:start;}
  .contact h2{font-size:clamp(1.8rem,3.6vw,2.6rem);max-width:16ch;margin:12px 0 16px;}
  .contact .lead{color:var(--muted);font-size:17px;max-width:44ch;margin-bottom:26px;}
  .contact-row{display:flex;justify-content:space-between;align-items:center;padding:13px 0;border-top:1px solid var(--line);font-family:var(--mono);font-size:13.5px;transition:padding-left .15s;}
  .contact-row:hover{padding-left:6px;color:var(--primary);}
  .contact-row span{color:var(--faint);font-size:12px;}
  .contact-form{margin-top:30px;border-top:1px solid var(--line);padding-top:28px;}
  .contact-form h3{font-size:17px;margin-bottom:18px;}
  .cf-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px;}
  .cf-grid>div{margin-bottom:0;}
  .contact-form label{display:block;font-family:var(--mono);font-size:11px;letter-spacing:.03em;text-transform:uppercase;color:var(--muted);margin-bottom:6px;}
  .contact-form input,.contact-form textarea,.contact-form select{width:100%;font-family:var(--body);font-size:14px;color:var(--ink);background:var(--surface);border:1px solid var(--line-strong);border-radius:var(--r);padding:11px 13px;}
  .contact-form input:focus,.contact-form textarea:focus,.contact-form select:focus{outline:none;border-color:var(--primary);box-shadow:0 0 0 3px var(--soft);}
  .contact-form textarea{resize:vertical;min-height:120px;}
  .field-hint{font-family:var(--mono);font-size:11px;color:var(--faint);margin:10px 0 16px;}
  .response-note{font-family:var(--mono);font-size:12px;color:var(--muted);margin-top:12px;}
  .honeypot{position:absolute;left:-9999px;top:-9999px;}
  .form-status{font-family:var(--mono);font-size:13px;padding:12px 16px;margin-bottom:20px;border:1px solid var(--ok);color:var(--ok);background:var(--ok-soft);border-radius:var(--r);}
  .has-error{border-color:var(--danger) !important;}
  .field-error{font-family:var(--mono);font-size:12px;color:var(--danger);margin-top:6px;}
  .meta-box{background:var(--raise);border:1px solid var(--line);border-radius:var(--r-lg);padding:20px 24px;font-family:var(--mono);font-size:13px;color:var(--muted);}
  .meta-box div{display:flex;justify-content:space-between;align-items:center;padding:11px 0;border-top:1px solid var(--line);}
  .meta-box div:first-child{border-top:none;}
  .meta-box strong{font-family:var(--geo);color:var(--ink);font-weight:600;}
  @media(max-width:820px){.contact-inner{grid-template-columns:1fr;gap:28px;}}
  @media(max-width:560px){.cf-grid{grid-template-columns:1fr;}}
</style>
@endpush

@section('content')

<header class="hero">
  <div class="wrap">
    <div class="grid">
      <div>
        <span class="pill {{ $profile->available ? '' : 'away' }}">
          <span class="dot"></span>
          @if ($profile->available){{ __('site.status_available') }}@else{{ __('site.status_booked') }}@if($profile->availability_from), {{ __('site.status_available_from', ['date' => $profile->availability_from]) }}@endif @endif
        </span>
        <div class="clock" id="local-time">--:--</div>
        <div class="eyebrow">{{ $profile->tagline }}</div>
        <h1>{{ $profile->hero_headline }}</h1>
        <p class="sub">{{ $profile->hero_subtext }}</p>
        <div class="acts">
          <a class="btn pri" href="#contact">{{ __('site.hero_actions_primary') }}</a>
          <a class="btn sec" href="{{ localized_route('work.index') }}">{{ __('site.hero_actions_secondary') }}</a>
          <a class="btn sec" href="{{ localized_route('cv') }}">{{ __('site.hero_download_cv') }}</a>
        </div>
      </div>
      <dl class="facts">
        <dt>{{ __('site.meta_availability') }}</dt>
        <dd>{{ $profile->available ? __('site.meta_availability_now') : __('site.meta_availability_from', ['date' => $profile->availability_from]) }}</dd>
        <dt>{{ __('site.meta_based_in') }}</dt>
        <dd>{{ $profile->city }}, NL</dd>
        <dt>{{ __('site.meta_rate') }}</dt>
        <dd>{{ __('site.meta_rate_value') }}</dd>
        <dt>{{ __('site.meta_remote_label') }}</dt>
        <dd>{{ __('site.meta_remote') }}</dd>
      </dl>
    </div>
  </div>
</header>

<section id="stack" class="block">
  <div class="wrap">
    <div class="section-head"><span class="eyebrow">{{ __('site.stack_label') }}</span><h2>{{ __('site.stack_headline') }}</h2></div>
    <div class="stack-grid">
      @foreach ($skills as $category => $items)
        <div class="stack-col">
          <h3>{{ $category }}</h3>
          <ul>@foreach ($items as $skill)<li>{{ $skill->name }}</li>@endforeach</ul>
        </div>
      @endforeach
    </div>
  </div>
</section>

<section id="work" class="block">
  <div class="wrap">
    <div class="section-head"><span class="eyebrow">{{ __('site.work_label') }}</span><h2>{{ __('site.work_headline') }}</h2></div>
    <div class="card-grid">
      @forelse ($projects as $project)
        @php $clickable = (bool) $project->body; $tag = $clickable ? 'a' : 'div'; @endphp
        <{{ $tag }} class="pcard" @if($clickable) href="{{ localized_route('project.show', $project->slug) }}" @endif>
          @if ($project->image)
            <img class="thumb" src="{{ $project->image_url }}" alt="{{ $project->image_alt ?: $project->name }}" loading="lazy" decoding="async">
          @else
            <div class="thumb ph"><span class="k">{{ $project->year }}</span></div>
          @endif
          <div class="in">
            <h3>{{ $project->name }}</h3>
            <div class="meta">{{ $project->year }}{{ $project->year && $project->client_name ? ' · ' : '' }}{{ $project->client_name }}</div>
            <p>{{ $project->description }}</p>
            @if ($project->outcome)<div class="outcome">↳ {{ $project->outcome }}</div>@endif
            @if (count($project->tag_list))
              <div class="tags">@foreach ($project->tag_list as $t)<span class="tag">{{ $t }}</span>@endforeach</div>
            @endif
          </div>
        </{{ $tag }}>
      @empty
        <p style="color:var(--muted)">{{ __('site.work_no_projects') }}</p>
      @endforelse
    </div>
  </div>
</section>

<section id="process" class="block">
  <div class="wrap">
    <div class="section-head"><span class="eyebrow">{{ __('site.process_label') }}</span><h2>{{ __('site.process_headline') }}</h2></div>
    <div class="proc">
      @for ($i = 1; $i <= 4; $i++)
        <div class="step"><div class="num">0{{ $i }}</div><h3>{{ __('site.process_'.$i.'_title') }}</h3><p>{{ __('site.process_'.$i.'_body') }}</p></div>
      @endfor
    </div>
  </div>
</section>

<section id="services" class="block">
  <div class="wrap">
    <div class="section-head"><span class="eyebrow">{{ __('site.services_label') }}</span><h2>{{ __('site.services_headline') }}</h2></div>
    <div class="svc">
      @for ($i = 1; $i <= 3; $i++)
        <div class="sc">
          <div class="n">{{ $i }}</div>
          <h3>{{ __('site.service_'.$i.'_title') }}</h3>
          <p>{{ __('site.service_'.$i.'_body') }}</p>
          <ul>@foreach (__('site.service_'.$i.'_items') as $item)<li>{{ $item }}</li>@endforeach</ul>
          <div class="price">{{ __('site.service_'.$i.'_price') }}</div>
        </div>
      @endfor
    </div>
  </div>
</section>

@if ($testimonials->isNotEmpty())
<section class="block">
  <div class="wrap">
    <div class="testi" id="testimonial-carousel">
      <div class="testi-slides">
        @foreach ($testimonials as $i => $testimonial)
          <div class="testi-slide {{ $i === 0 ? 'active' : '' }}" @if($i > 0) aria-hidden="true" @endif>
            <span class="quote-mark">&ldquo;</span>
            <blockquote>{{ $testimonial->quote }}</blockquote>
            <div class="testi-attr">&mdash; {{ $testimonial->author_name }}{{ $testimonial->author_role ? ', '.$testimonial->author_role : '' }}{{ $testimonial->company_name ? ' · '.$testimonial->company_name : '' }}</div>
          </div>
        @endforeach
      </div>
      @if ($testimonials->count() > 1)
        <div class="carousel-dots">
          @foreach ($testimonials as $i => $t)
            <button class="carousel-dot" data-index="{{ $i }}" aria-label="{{ __('site.testimonial_aria', ['number' => $i + 1]) }}" style="background:{{ $i === 0 ? 'var(--primary)' : 'var(--line-strong)' }};"></button>
          @endforeach
        </div>
      @endif
    </div>
  </div>
</section>
@endif

<section id="contact" class="block contact">
  <div class="wrap contact-inner">
    <div>
      <span class="eyebrow">{{ __('site.contact_label') }}</span>
      <h2>{{ __('site.contact_headline') }}</h2>
      <p class="lead">{{ __('site.contact_lead') }}</p>

      <div class="contact-links">
        @if ($profile->email)
        <a class="contact-row" href="mailto:{{ $profile->email }}"><span>Email</span> {{ $profile->email }}</a>
        @endif
        @if ($profile->linkedin_url)
        <a class="contact-row" href="{{ $profile->linkedin_url }}" target="_blank" rel="noopener noreferrer"><span>LinkedIn</span> {{ $profile->linkedin_url }}</a>
        @endif
        @if ($profile->github_url)
        <a class="contact-row" href="{{ $profile->github_url }}" target="_blank" rel="noopener noreferrer"><span>GitHub</span> {{ $profile->github_url }}</a>
        @endif
      </div>

      <div class="contact-form">
        <h3>{{ __('site.contact_form_title') }}</h3>

        @if (session('status'))
          <div class="form-status" role="status" id="form-feedback">{{ session('status') }}</div>
        @endif

        <form method="POST" action="{{ route('contact.store') }}">
          @csrf
          <input type="hidden" name="form_token" value="{{ ContactFormToken::issue() }}">
          <input type="text" name="website" class="honeypot" tabindex="-1" autocomplete="off">
          <div class="cf-grid">
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
          <div class="cf-grid">
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
          <textarea id="contact-message" name="message" rows="5" required @error('message') class="has-error" aria-invalid="true" aria-describedby="error-message" @else aria-describedby="message-hint" @enderror>{{ old('message') }}</textarea>
          @error('message')<div class="field-error" id="error-message">{{ $message }}</div>@enderror
          <div class="field-hint" id="message-hint">{{ __('site.contact_message_hint') }}</div>
          <button class="btn pri" type="submit">{{ __('site.contact_submit') }}</button>
          <p class="response-note">{{ __('site.contact_response_time') }}</p>
        </form>
      </div>
    </div>

    <div class="meta-box">
      <div><span>{{ __('site.meta_based_in') }}</span><strong>{{ $profile->city }}, NL</strong></div>
      <div><span>{{ __('site.meta_rate') }}</span><strong>{{ __('site.meta_rate_value') }}</strong></div>
      <div><span>{{ __('site.meta_availability') }}</span><strong>{{ $profile->available ? __('site.meta_availability_now') : __('site.meta_availability_from', ['date' => $profile->availability_from]) }}</strong></div>
      <div><span>{{ __('site.meta_languages_label') }}</span><strong>{{ __('site.meta_languages') }}</strong></div>
      <div><span>{{ __('site.meta_remote_label') }}</span><strong>{{ __('site.meta_remote') }}</strong></div>
    </div>
  </div>
</section>

@endsection

@push('scripts')
<script>
  @if (session('status') || $errors->any())
  document.getElementById('contact').scrollIntoView({ behavior: 'instant', block: 'start' });
  @endif

  (function(){
    var ref = new URLSearchParams(window.location.search).get('ref');
    if (!ref) return;
    var ta = document.getElementById('contact-message');
    if (ta && !ta.value) {
      ta.value = "I’m interested in something similar to your “" + ref.replace(/-/g, ' ').replace(/\b\w/g, function(c){ return c.toUpperCase(); }) + "” work.";
    }
  })();

  (function(){
    var slides = document.querySelectorAll('.testi-slide');
    var dots = document.querySelectorAll('.carousel-dot');
    if (slides.length < 2) return;
    var current = 0;
    function show(n) {
      slides[current].classList.remove('active');
      slides[current].setAttribute('aria-hidden', 'true');
      dots[current].style.background = 'var(--line-strong)';
      current = n;
      slides[current].classList.add('active');
      slides[current].removeAttribute('aria-hidden');
      dots[current].style.background = 'var(--primary)';
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

  (function(){
    var el = document.getElementById('local-time');
    if (!el) return;
    function updateClock(){
      var time = new Intl.DateTimeFormat('en-GB', { hour:'2-digit', minute:'2-digit', timeZone:'Europe/Amsterdam' }).format(new Date());
      el.textContent = time + ' ' + @json(__('site.hero_local_time', ['city' => $profile->city]));
    }
    updateClock();
    setInterval(updateClock, 30000);
  })();
</script>
@endpush
