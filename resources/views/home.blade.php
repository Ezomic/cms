<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ $profile->name }} — {{ $profile->tagline }}</title>
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
  .logo{font-family:var(--mono);font-weight:500;font-size:14px;display:flex;align-items:center;gap:8px;}
  .logo .dot{width:6px;height:6px;background:var(--accent);border-radius:50%;}
  .nav-links{display:flex;gap:32px;font-size:14px;font-family:var(--mono);}
  .nav-links a{text-decoration:none;color:var(--ink-soft);transition:color .15s;}
  .nav-links a:hover{color:var(--ink);}
  .nav-cta{font-family:var(--mono);font-size:13px;border:1px solid var(--ink);padding:8px 16px;text-decoration:none;transition:all .15s;}
  .nav-cta:hover{background:var(--ink);color:var(--white);}
  @media (max-width:720px){.nav-mobile-hide{display:none;}}
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
  .work-name{font-family:var(--display);font-weight:600;font-size:22px;margin-bottom:8px;}
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
  blockquote{font-family:var(--display);font-weight:500;font-size:clamp(1.3rem,3vw,2rem);line-height:1.35;letter-spacing:-.01em;max-width:26ch;}
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
  .contact-form input,.contact-form textarea{width:100%;border:1px solid var(--line);background:var(--white);padding:12px 14px;font-family:var(--body);font-size:14px;color:var(--ink);}
  .contact-form input:focus,.contact-form textarea:focus{outline:1px solid var(--ink);}
  .contact-form textarea{margin-bottom:16px;resize:vertical;}
  .honeypot{position:absolute;left:-9999px;top:-9999px;}
  .form-status{font-family:var(--mono);font-size:13px;padding:12px 16px;margin-bottom:20px;border:1px solid var(--accent);color:var(--accent);}
  .form-errors{font-family:var(--mono);font-size:13px;padding:12px 16px;margin-bottom:20px;border:1px solid #C0392B;color:#C0392B;}
  @media (max-width:560px){.contact-form-grid{grid-template-columns:1fr;}}
  footer{padding:32px 0;font-family:var(--mono);font-size:12px;color:var(--ink-soft);}
  footer .wrap{display:flex;justify-content:space-between;flex-wrap:wrap;gap:12px;}
</style>
</head>
<body>

<nav>
  <div class="wrap">
    <div class="logo"><span class="dot"></span>{{ strtoupper($profile->name) }} / NL</div>
    <div class="nav-links nav-mobile-hide">
      <a href="#work">Work</a>
      <a href="#process">Process</a>
      <a href="#contact">Contact</a>
    </div>
    <a class="nav-cta" href="#contact">Get in touch</a>
  </div>
</nav>

<header class="hero">
  <div class="grid-bg"></div>
  <div class="wrap hero-inner">
    <div class="status-row">
      <span class="status-dot {{ $profile->available ? '' : 'away' }}"></span>
      <span>{{ $profile->available ? 'Available for new projects' : 'Currently booked' }}</span>
      <span style="color:var(--line)">·</span>
      <span id="local-time">--:-- local time, {{ $profile->city }}, NL</span>
    </div>

    <div class="eyebrow">{{ $profile->tagline }}</div>
    <h1>{{ $profile->hero_headline }}</h1>
    <p class="hero-sub">
      I'm {{ $profile->name }}, a freelance {{ strtolower($profile->tagline) }} based in
      {{ $profile->city }}, the Netherlands. {{ $profile->hero_subtext }}
    </p>

    <div class="hero-actions">
      <a class="btn-primary" href="#contact">Start a project →</a>
      <a class="btn-secondary" href="#work">See recent work</a>
    </div>
  </div>
</header>

<section id="stack">
  <div class="wrap">
    <div class="section-head">
      <div class="section-label">Capabilities</div>
      <h2>One developer, the whole stack.</h2>
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
      <div class="section-label">Selected work</div>
      <h2>Recent projects.</h2>
    </div>

    <div class="work-list">
      @forelse ($projects as $project)
        <div class="work-item">
          <div class="work-year">{{ $project->year }} — {{ $project->client_name }}</div>
          <div>
            <div class="work-name">{{ $project->name }}</div>
            <div class="work-desc">{{ $project->description }}</div>
          </div>
          <div class="work-tags">
            @foreach ($project->tagList() as $tag)
              <span class="tag">{{ $tag }}</span>
            @endforeach
          </div>
        </div>
      @empty
        <p style="color:var(--ink-soft)">Projects will show up here once added in the admin panel.</p>
      @endforelse
    </div>
  </div>
</section>

<section id="process">
  <div class="wrap">
    <div class="section-head">
      <div class="section-label">How I work</div>
      <h2>Four steps, no surprises.</h2>
    </div>

    <div class="process-grid">
      <div class="process-step">
        <div class="process-num">01</div>
        <h3>Scope</h3>
        <p>A short call to understand the problem, then a clear proposal with fixed price or day rate.</p>
      </div>
      <div class="process-step">
        <div class="process-num">02</div>
        <h3>Plan</h3>
        <p>Technical approach, milestones, and a realistic timeline — agreed before any code is written.</p>
      </div>
      <div class="process-step">
        <div class="process-num">03</div>
        <h3>Build</h3>
        <p>Weekly check-ins and a staging link you can follow along with, so nothing arrives as a surprise.</p>
      </div>
      <div class="process-step">
        <div class="process-num">04</div>
        <h3>Ship</h3>
        <p>Deployed, documented, and handed over — plus a support window for anything after launch.</p>
      </div>
    </div>
  </div>
</section>

@if ($testimonial)
<section class="testimonial">
  <div class="wrap">
    <span class="quote-mark">"</span>
    <blockquote>{{ $testimonial->quote }}</blockquote>
    <div class="testimonial-attr">— {{ $testimonial->author_name }}@if($testimonial->author_role), {{ $testimonial->author_role }}@endif</div>
  </div>
</section>
@endif

<section id="contact" class="contact">
  <div class="wrap contact-inner">
    <div>
      <div class="section-label" style="margin-bottom:16px;">Get in touch</div>
      <h2>Have a project in mind?</h2>
      <p class="lead">Tell me what you're building and what's not working yet. I usually reply within one business day.</p>

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
        <h3>Or send a message directly</h3>

        @if (session('status'))
          <div class="form-status">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
          <div class="form-errors">
            @foreach ($errors->all() as $error)<div>{{ $error }}</div>@endforeach
          </div>
        @endif

        <form method="POST" action="{{ route('contact.store') }}">
          @csrf
          <input type="text" name="website" class="honeypot" tabindex="-1" autocomplete="off">
          <div class="contact-form-grid">
            <div>
              <label for="contact-name">Name</label>
              <input id="contact-name" type="text" name="name" value="{{ old('name') }}" required>
            </div>
            <div>
              <label for="contact-email">Email</label>
              <input id="contact-email" type="email" name="email" value="{{ old('email') }}" required>
            </div>
          </div>
          <label for="contact-message">Message</label>
          <textarea id="contact-message" name="message" rows="4" required>{{ old('message') }}</textarea>
          <button class="btn-primary" type="submit" style="border:none;cursor:pointer;">Send message →</button>
        </form>
      </div>
    </div>

    <div class="meta-box">
      <div><span>Based in</span><strong>{{ $profile->city }}, NL</strong></div>
      <div><span>Rate</span><strong>{{ $profile->rate }}</strong></div>
      <div><span>Availability</span><strong>{{ $profile->available ? 'Now' : 'From ' . $profile->availability_from }}</strong></div>
      <div><span>Languages</span><strong>NL / EN</strong></div>
      <div><span>Remote / on-site</span><strong>Both, EU-wide</strong></div>
    </div>
  </div>
</section>

<footer>
  <div class="wrap">
    <span>© {{ date('Y') }} {{ $profile->name }}. Built in the Netherlands.</span>
    <span>KVK {{ $profile->kvk_number }}</span>
  </div>
</footer>

<script>
  function updateClock(){
    const el = document.getElementById('local-time');
    const time = new Intl.DateTimeFormat('en-GB', { hour:'2-digit', minute:'2-digit', timeZone:'Europe/Amsterdam' }).format(new Date());
    el.textContent = time + ' local time, {{ $profile->city }}, NL';
  }
  updateClock();
  setInterval(updateClock, 30000);
</script>

</body>
</html>
