<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: 'Inter', 'DejaVu Sans', sans-serif; font-size: 11px; color: #17181A; background: #fff; line-height: 1.5; }
  .page { padding: 48px 52px; max-width: 780px; margin: 0 auto; }
  .header { border-bottom: 2px solid #17181A; padding-bottom: 20px; margin-bottom: 28px; page-break-inside: avoid; }
  .name { font-family: 'Space Grotesk', 'Inter', sans-serif; font-size: 26px; font-weight: 700; letter-spacing: -0.02em; margin-bottom: 4px; }
  .tagline { font-size: 13px; color: #63645F; margin-bottom: 12px; }
  .contact-row { font-size: 11px; color: #63645F; }
  .contact-row span { margin-right: 20px; }
  .contact-row a { color: #63645F; text-decoration: none; }
  .accent { color: #E8590C; }
  .section { margin-bottom: 28px; }
  .section-title { font-size: 9px; text-transform: uppercase; letter-spacing: 0.1em; color: #E8590C; font-weight: 700; margin-bottom: 10px; border-bottom: 1px solid #DDDDD6; padding-bottom: 4px; page-break-after: avoid; }
  .section-title-row { display: table; width: 100%; margin-bottom: 10px; border-bottom: 1px solid #DDDDD6; padding-bottom: 4px; page-break-after: avoid; }
  .section-title-row .section-title { display: table-cell; margin-bottom: 0; border-bottom: none; padding-bottom: 0; }
  .section-cta { display: table-cell; text-align: right; font-size: 9px; color: #63645F; text-decoration: none; white-space: nowrap; vertical-align: bottom; }
  .intro { font-size: 12px; color: #3a3b38; line-height: 1.6; max-width: 600px; }
  .skills-grid { display: table; width: 100%; page-break-inside: avoid; }
  .skills-col { display: table-cell; width: 33%; vertical-align: top; padding-right: 16px; }
  .skills-col-title { font-size: 10px; font-weight: 700; color: #17181A; margin-bottom: 6px; }
  .skills-col ul { list-style: none; }
  .skills-col li { font-size: 11px; color: #63645F; padding: 3px 0; border-top: 1px solid #EEEEEA; }
  .skills-col li:first-child { border-top: none; }
  .project { margin-bottom: 18px; page-break-inside: avoid; }
  .project-header { display: table; width: 100%; margin-bottom: 4px; }
  .project-name { display: table-cell; font-family: 'Space Grotesk', 'Inter', sans-serif; font-size: 13px; font-weight: 700; }
  .project-meta { display: table-cell; text-align: right; font-size: 10px; color: #63645F; white-space: nowrap; }
  .project-meta a { color: #63645F; text-decoration: none; }
  .project-client { font-size: 10px; color: #E8590C; margin-bottom: 4px; }
  .project-desc { font-size: 11px; color: #63645F; line-height: 1.5; }
  .project-outcome { font-size: 11px; color: #17181A; font-weight: 700; margin-top: 4px; }
  .project-tags { margin-top: 6px; }
  .project-tags span { display: inline-block; font-size: 8px; color: #63645F; background: #F0F0EB; border-radius: 3px; padding: 2px 7px; margin: 0 4px 4px 0; }
  .availability-box { background: #F7F7F4; border-left: 3px solid #E8590C; padding: 10px 14px; font-size: 11px; color: #63645F; page-break-inside: avoid; }
  .availability-box strong { color: #17181A; }
  .availability-line { font-size: 10px; color: #63645F; page-break-inside: avoid; }
  .footer { margin-top: 32px; padding-top: 12px; border-top: 1px solid #DDDDD6; font-size: 9px; color: #aaa; text-align: center; }
</style>
</head>
<body>
<div class="page">

  <div class="header">
    <div class="name">{{ $profile->name }}</div>
    <div class="tagline">{{ $profile->tagline }} · {{ $profile->city }}, Netherlands</div>
    <div class="contact-row">
      @if($profile->email)<span><a href="mailto:{{ $profile->email }}">{{ $profile->email }}</a></span>@endif
      @if($profile->linkedin_url)<span><a href="{{ $profile->linkedin_url }}">{{ $profile->linkedin_url }}</a></span>@endif
      @if($profile->github_url)<span><a href="{{ $profile->github_url }}">{{ $profile->github_url }}</a></span>@endif
      @if($profile->rate)<span>{{ $profile->rate }}</span>@endif
    </div>
  </div>

  <div class="section">
    <div class="section-title">About</div>
    <p class="intro">{{ $profile->hero_subtext }}</p>
  </div>

  @if ($skills->isNotEmpty())
  <div class="section">
    <div class="section-title">Skills &amp; stack</div>
    <div class="skills-grid">
      @foreach ($skills as $category => $items)
        <div class="skills-col">
          <div class="skills-col-title">{{ $category }}</div>
          <ul>
            @foreach ($items as $skill)
              <li>{{ $skill->name }}</li>
            @endforeach
          </ul>
        </div>
      @endforeach
    </div>
  </div>
  @endif

  @if ($projects->isNotEmpty())
  <div class="section">
    <div class="section-title-row">
      <div class="section-title">Case studies</div>
      <a class="section-cta" href="{{ route('work.index') }}">See all work &rarr;</a>
    </div>
    @foreach ($projects as $project)
      <div class="project">
        <div class="project-header">
          <div class="project-name">{{ $project->name }}</div>
          <div class="project-meta">
            {{ $project->year }}
            @if($project->github_url ?? null)
              &middot; <a href="{{ $project->github_url }}">GitHub</a>
            @endif
          </div>
        </div>
        <div class="project-client">{{ $project->client_name }}</div>
        <div class="project-desc">{{ $project->description }}</div>
        @if($project->outcome ?? null)
          <div class="project-outcome"><span class="accent">Result &mdash;</span> {{ $project->outcome }}</div>
        @endif
        @if($project->tag_list)
          <div class="project-tags">
            @foreach ($project->tag_list as $tag)
              <span>{{ $tag }}</span>
            @endforeach
          </div>
        @endif
      </div>
    @endforeach
  </div>
  @endif

  @if($profile->available)
    <div class="availability-box">
      <strong>Currently available</strong> for new projects. Rate: {{ $profile->rate }}. Based in {{ $profile->city }}, NL. Works remote EU-wide and on-site.
    </div>
  @else
    <div class="availability-line">
      Currently booked{{ $profile->availability_from ? ' — available from '.$profile->availability_from : '' }}. Rate: {{ $profile->rate }}. Based in {{ $profile->city }}, NL.
    </div>
  @endif

  <div class="footer">
    @if ($profile->kvk_number)
      KVK {{ $profile->kvk_number }} ·
    @endif
    Generated {{ date('F Y') }}
  </div>

</div>
</body>
</html>
