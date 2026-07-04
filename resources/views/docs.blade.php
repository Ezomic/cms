<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Working with me — {{ $profile->name }}</title>
<meta name="description" content="How I engage, what you receive, and what to expect after launch.">
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
  body{background:var(--bg);color:var(--ink);font-family:var(--body);line-height:1.6;-webkit-font-smoothing:antialiased;}
  ::selection{background:var(--accent);color:var(--white);}
  a{color:inherit;}
  .wrap{max-width:1120px;margin:0 auto;padding:0 32px;}
  .wrap-narrow{max-width:720px;margin:0 auto;padding:0 32px;}
  nav{position:sticky;top:0;z-index:50;background:rgba(247,247,244,.88);backdrop-filter:blur(8px);border-bottom:1px solid var(--line);}
  nav .inner{max-width:1120px;margin:0 auto;padding:0 32px;display:flex;align-items:center;justify-content:space-between;height:64px;}
  .logo{font-family:var(--mono);font-weight:500;font-size:14px;display:flex;align-items:center;gap:8px;text-decoration:none;color:var(--ink);}
  .logo .dot{width:6px;height:6px;background:var(--accent);border-radius:50%;}
  .back-link{font-family:var(--mono);font-size:13px;text-decoration:none;color:var(--ink-soft);}
  .back-link:hover{color:var(--ink);}

  .page-header{padding:72px 0 56px;border-bottom:1px solid var(--line);}
  .eyebrow{font-family:var(--mono);font-size:13px;color:var(--accent);text-transform:uppercase;letter-spacing:.08em;margin-bottom:20px;}
  h1{font-family:var(--display);font-weight:600;font-size:clamp(2rem,5vw,3.2rem);line-height:1.08;letter-spacing:-.02em;margin-bottom:24px;}
  .lead{font-size:18px;color:var(--ink-soft);max-width:54ch;}

  .toc{padding:40px 0;border-bottom:1px solid var(--line);}
  .toc-list{display:flex;gap:32px;flex-wrap:wrap;font-family:var(--mono);font-size:13px;}
  .toc-list a{text-decoration:none;color:var(--ink-soft);transition:color .15s;}
  .toc-list a:hover{color:var(--accent);}
  .toc-num{color:var(--accent);margin-right:6px;}

  .doc-section{padding:72px 0;border-bottom:1px solid var(--line);}
  .doc-section:last-of-type{border-bottom:none;}
  .section-label{font-family:var(--mono);font-size:13px;color:var(--ink-soft);text-transform:uppercase;letter-spacing:.08em;margin-bottom:8px;}
  .section-num{font-family:var(--mono);font-size:13px;color:var(--accent);margin-bottom:8px;}
  h2{font-family:var(--display);font-weight:600;font-size:clamp(1.5rem,3vw,2rem);letter-spacing:-.01em;margin-bottom:20px;}
  h3{font-family:var(--display);font-weight:600;font-size:1.1rem;margin-bottom:10px;}
  p{color:var(--ink-soft);font-size:16px;margin-bottom:1.2em;max-width:62ch;}
  p:last-child{margin-bottom:0;}

  .two-col{display:grid;grid-template-columns:1fr 1fr;gap:48px;align-items:start;margin-top:40px;}
  @media (max-width:720px){.two-col{grid-template-columns:1fr;}}

  .card{border:1px solid var(--line);padding:28px;}
  .card h3{font-family:var(--mono);font-size:13px;color:var(--accent);text-transform:uppercase;letter-spacing:.06em;margin-bottom:16px;}
  .card ul{list-style:none;}
  .card li{font-size:15px;padding:10px 0;border-top:1px solid var(--line);color:var(--ink-soft);display:flex;gap:12px;align-items:baseline;}
  .card li:first-child{border-top:none;padding-top:0;}
  .card li::before{content:'—';color:var(--line);flex-shrink:0;}

  .check-list{list-style:none;margin-top:24px;}
  .check-list li{padding:12px 0;border-top:1px solid var(--line);font-size:15px;color:var(--ink-soft);display:flex;gap:16px;align-items:baseline;}
  .check-list li:first-child{border-top:none;padding-top:0;}
  .check-list li .check{font-family:var(--mono);font-size:12px;color:var(--accent);flex-shrink:0;width:20px;}

  .pricing-grid{display:grid;grid-template-columns:1fr 1fr;gap:1px;background:var(--line);border:1px solid var(--line);margin-top:40px;}
  .pricing-cell{background:var(--bg);padding:32px;}
  .pricing-cell .label{font-family:var(--mono);font-size:12px;color:var(--ink-soft);text-transform:uppercase;letter-spacing:.06em;margin-bottom:16px;}
  .pricing-cell h3{font-family:var(--display);font-weight:600;font-size:1.3rem;margin-bottom:10px;}
  .pricing-cell p{font-size:14px;color:var(--ink-soft);max-width:100%;}
  @media (max-width:600px){.pricing-grid{grid-template-columns:1fr;}}

  .stack-row{display:flex;justify-content:space-between;align-items:baseline;padding:14px 0;border-top:1px solid var(--line);font-size:15px;}
  .stack-row:first-child{border-top:none;padding-top:0;}
  .stack-row .why{font-family:var(--mono);font-size:12px;color:var(--ink-soft);}
  .stack-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1px;background:var(--line);border:1px solid var(--line);margin-top:32px;}
  .stack-col{background:var(--bg);padding:28px;}
  .stack-col h3{font-family:var(--mono);font-size:12px;color:var(--accent);text-transform:uppercase;letter-spacing:.06em;margin-bottom:14px;}
  .stack-col ul{list-style:none;}
  .stack-col li{font-size:15px;padding:9px 0;border-top:1px solid var(--line);color:var(--ink-soft);}
  .stack-col li:first-of-type{border-top:none;}
  .project-list{margin-top:32px;}
  .project-row{display:flex;justify-content:space-between;align-items:baseline;gap:16px;padding:14px 0;border-top:1px solid var(--line);font-size:15px;flex-wrap:wrap;}
  .project-row:first-child{border-top:none;padding-top:0;}
  .project-row .proj-meta{font-family:var(--mono);font-size:12px;color:var(--ink-soft);white-space:nowrap;}
  .project-tags{display:flex;flex-wrap:wrap;gap:6px;margin-top:4px;}
  .tag{font-family:var(--mono);font-size:11px;color:var(--ink-soft);border:1px solid var(--line);padding:3px 8px;border-radius:20px;white-space:nowrap;}

  .timeline{margin-top:40px;}
  .tl-item{display:grid;grid-template-columns:80px 1fr;gap:24px;padding:24px 0;border-top:1px solid var(--line);}
  .tl-item:first-child{border-top:none;padding-top:0;}
  .tl-day{font-family:var(--mono);font-size:13px;color:var(--accent);}
  .tl-item h3{font-family:var(--display);font-weight:600;font-size:16px;margin-bottom:6px;}
  .tl-item p{font-size:14px;max-width:100%;}

  .callout{background:var(--accent-soft);border-left:3px solid var(--accent);padding:20px 24px;margin-top:32px;}
  .callout p{color:var(--ink);font-size:15px;}

  footer{padding:32px 0;font-family:var(--mono);font-size:12px;color:var(--ink-soft);border-top:1px solid var(--line);}
  footer .wrap{display:flex;justify-content:space-between;flex-wrap:wrap;gap:12px;}
  .footer-cta{font-family:var(--mono);font-size:13px;background:var(--ink);color:var(--white);padding:10px 20px;text-decoration:none;transition:background .15s;}
  .footer-cta:hover{background:var(--accent);}
</style>
</head>
<body>

<nav>
  <div class="inner">
    <a class="logo" href="{{ route('home') }}"><span class="dot"></span>{{ strtoupper($profile->name) }} / NL</a>
    <a class="back-link" href="{{ route('home') }}">← Back to site</a>
  </div>
</nav>

<div class="wrap-narrow">

  <div class="page-header">
    <div class="eyebrow">Working with me</div>
    <h1>Everything you need to know before we start.</h1>
    <p class="lead">How I structure engagements, what you receive when we're done, and what happens after launch.</p>
  </div>

  <div class="toc">
    <div class="toc-list">
      <a href="#engagement"><span class="toc-num">01</span>Engagement types</a>
      <a href="#deliverables"><span class="toc-num">02</span>Deliverables</a>
      <a href="#tech"><span class="toc-num">03</span>Tech defaults</a>
      <a href="#revisions"><span class="toc-num">04</span>Revisions & feedback</a>
      <a href="#after-launch"><span class="toc-num">05</span>After launch</a>
      <a href="#contract"><span class="toc-num">06</span>Contract & IP</a>
      <a href="#payment"><span class="toc-num">07</span>Payment</a>
      <a href="#privacy"><span class="toc-num">08</span>Privacy</a>
      <a href="#faq"><span class="toc-num">09</span>FAQ</a>
    </div>
  </div>

  <!-- 01 Engagement types -->
  <div class="doc-section" id="engagement">
    <div class="section-num">01</div>
    <h2>Engagement types</h2>
    <p>I work in two ways depending on what fits the project best. Both start with a short scoping call — no commitment required.</p>

    <div class="pricing-grid">
      <div class="pricing-cell">
        <div class="label">Fixed price</div>
        <h3>Scope-first projects</h3>
        <p>For well-defined work — a new feature, a redesign, a complete product build. We agree on scope, timeline, and price before any code is written. No surprises.</p>
      </div>
      <div class="pricing-cell">
        <div class="label">Day rate</div>
        <h3>Ongoing work</h3>
        <p>For teams that need a reliable senior hand on complex or evolving work. Billed weekly, cancel with one week's notice. Rate available on request — see the contact section on the main site.</p>
      </div>
    </div>

    <div class="callout" style="margin-top:32px;">
      <p>I work with one or two clients at a time. This means you get real focus, not a ticket queue. Most projects have a 2–4 week lead time from first contact to kick-off.</p>
    </div>
  </div>

  <!-- 02 Deliverables -->
  <div class="doc-section" id="deliverables">
    <div class="section-num">02</div>
    <h2>What you receive</h2>
    <p>Every project ends with a handover — not just a deployed URL. Here's what's included as standard.</p>

    <div class="two-col">
      <div class="card">
        <h3>Code & access</h3>
        <ul>
          <li>Full source code in a private Git repository</li>
          <li>Production deployment on your infrastructure</li>
          <li>All credentials and environment variables documented</li>
          <li>Storage, backups, and queue workers configured</li>
        </ul>
      </div>
      <div class="card">
        <h3>Documentation</h3>
        <ul>
          <li>Architecture overview (what it is and why)</li>
          <li>Run book — how to deploy, update, and roll back</li>
          <li>Env var reference with safe default values</li>
          <li>Recorded walkthrough if the team is non-technical</li>
        </ul>
      </div>
    </div>

    <ul class="check-list" style="margin-top:40px;">
      <li><span class="check">✓</span>Database schema with migration history — no manual SQL scripts</li>
      <li><span class="check">✓</span>Automated test suite covering critical paths</li>
      <li><span class="check">✓</span>CI/CD pipeline configured (GitHub Actions or equivalent)</li>
      <li><span class="check">✓</span>Staging environment matching production</li>
      <li><span class="check">✓</span>Basic monitoring — uptime check and error alerting</li>
    </ul>
  </div>

  <!-- 03 Tech defaults -->
  <div class="doc-section" id="tech">
    <div class="section-num">03</div>
    <h2>Tech defaults</h2>
    <p>These are my defaults. If your team has existing infrastructure or strong preferences, we discuss it in the scoping call — I'm not dogmatic about the stack.</p>

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

    <p style="margin-top:32px;">I avoid frameworks and libraries that introduce hidden complexity or make the codebase hard to hand off to a future team. Everything I write should be readable by a competent senior developer who has never seen the project before.</p>

    @if ($projects->isNotEmpty())
      <div style="margin-top:48px;">
        <div style="font-family:var(--mono);font-size:12px;color:var(--ink-soft);text-transform:uppercase;letter-spacing:.06em;margin-bottom:16px;">Recent projects built with this stack</div>
        <div class="project-list">
          @foreach ($projects as $project)
            <div class="project-row">
              <div>
                <div style="font-weight:500;">
                  @if ($project->body)
                    <a href="{{ route('project.show', $project->slug) }}" style="text-decoration:none;color:var(--ink);">{{ $project->name }}</a>
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

  <!-- 04 Revisions -->
  <div class="doc-section" id="revisions">
    <div class="section-num">04</div>
    <h2>Revisions &amp; feedback</h2>
    <p>Revisions are built into the process, not bolted on as an afterthought. Here's how feedback rounds work.</p>

    <div class="timeline">
      <div class="tl-item">
        <div class="tl-day">Week 1–2</div>
        <div>
          <h3>Design & architecture review</h3>
          <p>Before code is written, I share a short document covering the technical approach, data model, and any open questions. One round of feedback here saves three rounds later.</p>
        </div>
      </div>
      <div class="tl-item">
        <div class="tl-day">Ongoing</div>
        <div>
          <h3>Staging link, always live</h3>
          <p>You have access to a staging environment from day one. No big reveal at the end — changes are visible as they land.</p>
        </div>
      </div>
      <div class="tl-item">
        <div class="tl-day">Pre-launch</div>
        <div>
          <h3>Two rounds of UI revisions</h3>
          <p>Fixed-price projects include two consolidated feedback rounds on the final UI. "Consolidated" means collecting all feedback in one pass, not one item at a time. Scope changes are quoted separately.</p>
        </div>
      </div>
      <div class="tl-item">
        <div class="tl-day">Post-launch</div>
        <div>
          <h3>Bug fixes, no charge</h3>
          <p>Any bug that reproduces the agreed spec is fixed for free within the support window. Changed requirements are handled as a new scope item.</p>
        </div>
      </div>
    </div>
  </div>

  <!-- 05 After launch -->
  <div class="doc-section" id="after-launch">
    <div class="section-num">05</div>
    <h2>After launch</h2>
    <p>Every project comes with a 30-day support window. During this period I'm available for bug fixes, small adjustments, and questions about the handover.</p>

    <div class="two-col" style="margin-top:32px;">
      <div class="card">
        <h3>Included in every project</h3>
        <ul>
          <li>30 days of bug-fix support</li>
          <li>Patch version dependency updates</li>
          <li>One handover call with your team</li>
          <li>Email access for questions during the window</li>
        </ul>
      </div>
      <div class="card">
        <h3>Available on a retainer</h3>
        <ul>
          <li>Ongoing feature development</li>
          <li>Security & major version upgrades</li>
          <li>Performance monitoring & tuning</li>
          <li>On-call availability (quoted separately)</li>
        </ul>
      </div>
    </div>

    <div class="callout">
      <p>I'm not a hosting provider — I hand over full control and don't lock you in. If you decide to work with someone else after launch, I'll make the transition as smooth as possible.</p>
    </div>
  </div>

  <!-- 06 Contract & IP -->
  <div class="doc-section" id="contract">
    <div class="section-num">06</div>
    <h2>Contract &amp; intellectual property</h2>
    <p>Every engagement is covered by a written contract signed before work begins. No handshake deals.</p>

    <div class="two-col" style="margin-top:32px;">
      <div>
        <h3>Standard contract terms</h3>
        <p>I use a plain-Dutch freelance agreement based on the <a href="https://www.fnv.nl" target="_blank" style="color:var(--accent);text-decoration:none;">FNV model contract</a>, adapted for software projects. It covers scope, deliverables, timeline, payment terms, and liability. You receive the draft to review and mark up before signing — no pressure to accept the first version.</p>
        <p style="margin-top:16px;">An NDA is available on request and adds no lead time to the project start.</p>
      </div>
      <div>
        <h3>Who owns the code</h3>
        <p>On final payment, full intellectual property rights to the work product transfer to you. This includes all source code, design assets, database schemas, and documentation produced during the engagement.</p>
        <p style="margin-top:16px;">I retain the right to reference the project name and outcome as portfolio work, unless you request otherwise in writing — in which case the project stays confidential.</p>
      </div>
    </div>

    <div class="callout" style="margin-top:32px;">
      <p>Third-party libraries, frameworks, and open-source components remain under their original licences. The contract lists all significant dependencies and their licence types so there are no surprises if you ever need to audit the IP stack.</p>
    </div>
  </div>

  <!-- 07 Payment -->
  <div class="doc-section" id="payment">
    <div class="section-num">07</div>
    <h2>Payment schedule</h2>
    <p>I invoice in euros. All amounts are exclusive of VAT (BTW 21%), which is added for Dutch clients.</p>

    <div class="timeline">
      <div class="tl-item">
        <div class="tl-day">Kick-off</div>
        <div>
          <h3>50% upfront</h3>
          <p>Invoiced on the day the contract is signed. Work starts once payment clears — typically within 1–2 business days for Dutch bank transfers (iDEAL or SEPA).</p>
        </div>
      </div>
      <div class="tl-item">
        <div class="tl-day">Delivery</div>
        <div>
          <h3>50% on handover</h3>
          <p>Invoiced when the production deployment is live and the handover documentation is delivered. Payment terms: 14 days net.</p>
        </div>
      </div>
      <div class="tl-item">
        <div class="tl-day">Day rate</div>
        <div>
          <h3>Weekly billing</h3>
          <p>For ongoing work, I invoice every Friday for the hours or days worked that week. Payment terms: 14 days net. Cancel with one week's written notice.</p>
        </div>
      </div>
    </div>

    <div style="margin-top:32px;">
      <div class="stack-row">
        <span><strong>Accepted methods</strong></span>
        <span class="why">SEPA bank transfer, iDEAL, Wise</span>
      </div>
      <div class="stack-row">
        <span><strong>Currency</strong></span>
        <span class="why">EUR only</span>
      </div>
      <div class="stack-row">
        <span><strong>VAT number</strong></span>
        <span class="why">Provided on first invoice; reverse-charge applies for EU business clients outside NL</span>
      </div>
      <div class="stack-row">
        <span><strong>Late payment</strong></span>
        <span class="why">Statutory commercial interest (Handelsrente) applies after the due date</span>
      </div>
    </div>
  </div>

  <!-- 08 Privacy & data -->
  <div class="doc-section" id="privacy">
    <div class="section-num">08</div>
    <h2>Privacy &amp; data handling</h2>
    <p>During most engagements I will have access to production databases, credentials, and sometimes personal data belonging to your users. Here is how I handle that.</p>

    <ul class="check-list">
      <li><span class="check">✓</span>I do not store client credentials beyond the duration of the project. Secrets are kept in a local, encrypted vault and deleted on handover.</li>
      <li><span class="check">✓</span>I do not copy production databases to my local machine unless explicitly required for debugging — and never without written permission.</li>
      <li><span class="check">✓</span>If access to personal data is required, a Data Processing Agreement (DPA / verwerkersovereenkomst) is signed before that access is granted.</li>
      <li><span class="check">✓</span>I am registered with the Dutch Chamber of Commerce (KVK) and operate under Dutch and EU law, including the GDPR / AVG.</li>
      <li><span class="check">✓</span>Any subcontractors or tools I use that process personal data are subject to the same DPA requirements.</li>
    </ul>

    <div class="callout">
      <p>If your project involves health data, financial data, or any other special-category personal data under the AVG, flag this in the scoping call so we can discuss the appropriate handling before work begins.</p>
    </div>
  </div>

  <!-- 09 FAQ -->
  <div class="doc-section" id="faq">
    <div class="section-num">09</div>
    <h2>Frequently asked questions</h2>

    <div style="margin-top:32px;">
      <div style="padding:24px 0;border-top:1px solid var(--line);">
        <h3>Do you work with agencies or only direct clients?</h3>
        <p>Both. For agencies I typically act as a white-label senior developer — the client doesn't need to know I'm involved. For direct clients I handle the full engagement from scoping to handover.</p>
      </div>
      <div style="padding:24px 0;border-top:1px solid var(--line);">
        <h3>Can you work on an existing codebase?</h3>
        <p>Yes, and most engagements involve one. I'll do a short audit at the start to understand the architecture and flag anything that might affect timeline or scope. No extra charge for the audit on projects over €5k.</p>
      </div>
      <div style="padding:24px 0;border-top:1px solid var(--line);">
        <h3>What if the scope changes mid-project?</h3>
        <p>Small scope creep is part of every project and I absorb it without comment. Significant changes — new features, changed data models, additional integrations — are quoted as a separate scope item before any work begins on them. Nothing changes without a written agreement.</p>
      </div>
      <div style="padding:24px 0;border-top:1px solid var(--line);">
        <h3>Do you do design as well as development?</h3>
        <p>I can design functional UI — layouts, component systems, interaction patterns — but I'm not a visual designer in the brand / illustration sense. For projects that need a distinct visual identity I recommend bringing in a dedicated designer; I'm happy to work alongside one.</p>
      </div>
      <div style="padding:24px 0;border-top:1px solid var(--line);">
        <h3>What's the minimum project size?</h3>
        <p>I don't have a hard minimum, but engagements under roughly three days of work are rarely a good fit — the scoping and handover overhead is disproportionate. For small, well-defined tasks (a bug fix, a single integration) a day-rate arrangement works better than a fixed-price contract.</p>
      </div>
      <div style="padding:24px 0;border-top:1px solid var(--line);">
        <h3>Do you provide hosting?</h3>
        <p>I set up and configure the server and deploy the application as part of every project. The server account is in your name and under your control — I don't act as a hosting intermediary. After handover, you own the infrastructure and can manage it directly or hand it to an ops team.</p>
      </div>
      <div style="padding:24px 0;border-top:1px solid var(--line);">
        <h3>Can I see references or speak to past clients?</h3>
        <p>Yes. I can connect you with two or three past clients who have agreed to take reference calls. Some projects are under NDA and those clients can confirm the engagement but not discuss details. Ask during the scoping call.</p>
      </div>
    </div>

    <div style="margin-top:48px;padding-top:48px;border-top:1px solid var(--line);">
      <h3 style="font-family:var(--display);font-weight:600;font-size:1.4rem;margin-bottom:12px;">Ready to start?</h3>
      <p style="margin-bottom:24px;">Send a short description of what you're building and I'll get back to you within one business day.</p>
      <a href="{{ route('home') }}#contact" style="font-family:var(--mono);font-size:14px;background:var(--ink);color:var(--white);padding:14px 24px;text-decoration:none;display:inline-flex;align-items:center;gap:8px;transition:background .15s;" onmouseover="this.style.background='var(--accent)'" onmouseout="this.style.background='var(--ink)'">Get in touch →</a>
    </div>
  </div>

</div>

<footer>
  <div class="wrap">
    <span>© {{ date('Y') }} {{ $profile->name }}. Built in the Netherlands.</span>
    <a class="footer-cta" href="{{ route('home') }}">Back to site</a>
  </div>
</footer>

</body>
</html>
