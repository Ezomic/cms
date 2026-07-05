<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<style>
  * { margin: 0; padding: 0; box-sizing: border-box; }
  body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #17181A; background: #fff; line-height: 1.5; }
  .page { padding: 48px 52px; max-width: 780px; margin: 0 auto; }
  .header { border-bottom: 2px solid #17181A; padding-bottom: 20px; margin-bottom: 28px; }
  .name { font-size: 26px; font-weight: 700; letter-spacing: -0.02em; margin-bottom: 4px; }
  .tagline { font-size: 13px; color: #63645F; margin-bottom: 12px; }
  .contact-row { font-size: 11px; color: #63645F; }
  .contact-row span { margin-right: 20px; }
  .accent { color: #E8590C; }
  .section { margin-bottom: 28px; }
  .section-title { font-size: 9px; text-transform: uppercase; letter-spacing: 0.1em; color: #E8590C; font-weight: 700; margin-bottom: 10px; border-bottom: 1px solid #DDDDD6; padding-bottom: 4px; }
  .intro { font-size: 12px; color: #3a3b38; line-height: 1.6; max-width: 600px; }
  .skills-grid { display: table; width: 100%; }
  .skills-col { display: table-cell; width: 33%; vertical-align: top; padding-right: 16px; }
  .skills-col-title { font-size: 10px; font-weight: 700; color: #17181A; margin-bottom: 6px; }
  .skills-col ul { list-style: none; }
  .skills-col li { font-size: 11px; color: #63645F; padding: 3px 0; border-top: 1px solid #EEEEEA; }
  .skills-col li:first-child { border-top: none; }
  .project { margin-bottom: 18px; }
  .project-header { display: table; width: 100%; margin-bottom: 4px; }
  .project-name { display: table-cell; font-size: 13px; font-weight: 700; }
  .project-meta { display: table-cell; text-align: right; font-size: 10px; color: #63645F; white-space: nowrap; }
  .project-client { font-size: 10px; color: #E8590C; margin-bottom: 4px; }
  .project-desc { font-size: 11px; color: #63645F; line-height: 1.5; }
  .project-tags { margin-top: 4px; font-size: 9px; color: #aaa; }
  .availability-box { background: #F7F7F4; border-left: 3px solid #E8590C; padding: 10px 14px; font-size: 11px; color: #63645F; }
  .availability-box strong { color: #17181A; }
  .footer { margin-top: 32px; padding-top: 12px; border-top: 1px solid #DDDDD6; font-size: 9px; color: #aaa; text-align: center; }
</style>
</head>
<body>
<div class="page">

  <div class="header">
    <div class="name">{{ $profile->name }}</div>
    <div class="tagline">{{ $profile->tagline }} · {{ $profile->city }}, Netherlands</div>
    <div class="contact-row">
      @if($profile->email)<span>{{ $profile->email }}</span>@endif
      @if($profile->linkedin_url)<span>{{ $profile->linkedin_url }}</span>@endif
      @if($profile->github_url)<span>{{ $profile->github_url }}</span>@endif
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
    <div class="section-title">Selected work</div>
    @foreach ($projects as $project)
      <div class="project">
        <div class="project-header">
          <div class="project-name">{{ $project->name }}</div>
          <div class="project-meta">{{ $project->year }}</div>
        </div>
        <div class="project-client">{{ $project->client_name }}</div>
        <div class="project-desc">{{ $project->description }}</div>
        @if($project->tag_list)
          <div class="project-tags">{{ implode(' · ', $project->tag_list) }}</div>
        @endif
      </div>
    @endforeach
  </div>
  @endif

  <div class="availability-box">
    @if($profile->available)
      <strong>Currently available</strong> for new projects.
    @else
      Currently booked{{ $profile->availability_from ? ' — available from '.$profile->availability_from : '' }}.
    @endif
    Rate: {{ $profile->rate }}. Based in {{ $profile->city }}, NL. Works remote EU-wide and on-site.
  </div>

  <div class="footer">
    @if ($profile->kvk_number)
      KVK {{ $profile->kvk_number }} ·
    @endif
    Generated {{ date('F Y') }}
  </div>

</div>
</body>
</html>
