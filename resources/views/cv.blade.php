<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="UTF-8">
<style>
  /* Reset block-element margins explicitly rather than with `* { margin: 0 }`,
     which dompdf also applies to the page box and would wipe the @page margin. */
  * { padding: 0; box-sizing: border-box; }
  body, p, ul, ol, dl, dd, dt, h1, h2, h3 { margin: 0; }
  /* accent updated to brand indigo (#4b3fd0) as part of the redesign; dompdf keeps table/inline layout */
  body { font-family: 'Inter', 'DejaVu Sans', sans-serif; font-size: 11px; color: #17181A; background: #fff; line-height: 1.5; }
  @page { margin: 14mm 15mm; }
  .page { max-width: 780px; margin: 0 auto; }
  .header { border-bottom: 2px solid #17181A; padding-bottom: 14px; margin-bottom: 18px; page-break-inside: avoid; }
  .name { font-family: 'Space Grotesk', 'Inter', sans-serif; font-size: 26px; font-weight: 700; letter-spacing: -0.02em; margin-bottom: 4px; }
  .tagline { font-size: 13px; color: #63645F; margin-bottom: 12px; }
  .contact-row { font-size: 11px; color: #63645F; }
  .contact-row span { margin-right: 20px; }
  .contact-row a { color: #63645F; text-decoration: none; }
  .accent { color: #4b3fd0; }
  .section { margin-bottom: 18px; }
  .section-title { font-size: 9px; text-transform: uppercase; letter-spacing: 0.1em; color: #4b3fd0; font-weight: 700; margin-bottom: 10px; border-bottom: 1px solid #DDDDD6; padding-bottom: 4px; page-break-after: avoid; }
  .section-title-row { display: table; width: 100%; margin-bottom: 10px; border-bottom: 1px solid #DDDDD6; padding-bottom: 4px; page-break-after: avoid; }
  .section-title-row .section-title { display: table-cell; margin-bottom: 0; border-bottom: none; padding-bottom: 0; }
  .section-cta { display: table-cell; text-align: right; font-size: 9px; color: #63645F; text-decoration: none; white-space: nowrap; vertical-align: bottom; }
  .hero-line { font-family: 'Space Grotesk', 'Inter', sans-serif; font-size: 15px; font-weight: 700; margin-bottom: 8px; max-width: 600px; }
  .intro { font-size: 12px; color: #3a3b38; line-height: 1.6; max-width: 600px; }
  .skills-grid { display: table; width: 100%; page-break-inside: avoid; }
  .skills-col { display: table-cell; width: 33%; vertical-align: top; padding-right: 16px; }
  .skills-col-title { font-size: 10px; font-weight: 700; color: #17181A; margin-bottom: 6px; }
  .skills-col ul { list-style: none; }
  .skills-col li { font-size: 11px; color: #63645F; padding: 3px 0; border-top: 1px solid #EEEEEA; }
  .skills-col li:first-child { border-top: none; font-weight: 700; color: #17181A; }
  .project { margin-bottom: 12px; page-break-inside: avoid; }
  .project-header { display: table; width: 100%; margin-bottom: 4px; }
  .project-name { display: table-cell; font-family: 'Space Grotesk', 'Inter', sans-serif; font-size: 13px; font-weight: 700; }
  .project-meta { display: table-cell; text-align: right; font-size: 10px; color: #63645F; white-space: nowrap; }
  .project-meta a { color: #63645F; text-decoration: none; }
  .project-client { font-size: 10px; color: #4b3fd0; margin-bottom: 4px; }
  .project-desc { font-size: 11px; color: #63645F; line-height: 1.5; }
  .project-outcome { font-size: 11px; color: #17181A; font-weight: 700; margin-top: 4px; }
  .project-tags { margin-top: 6px; }
  .project-tags span { display: inline-block; font-size: 8px; color: #63645F; background: #F0F0EB; border-radius: 3px; padding: 2px 7px; margin: 0 4px 4px 0; }
  .edu-row { display: table; width: 100%; margin-bottom: 7px; page-break-inside: avoid; }
  .edu-degree { display: table-cell; font-size: 11px; font-weight: 700; color: #17181A; }
  .edu-degree span { font-weight: 400; color: #63645F; }
  .edu-place { display: table-cell; text-align: right; font-size: 10px; color: #63645F; white-space: nowrap; }
  .availability-box { background: #F7F7F4; border-left: 3px solid #4b3fd0; padding: 10px 14px; font-size: 11px; color: #63645F; page-break-inside: avoid; }
  .availability-box strong { color: #17181A; }
  .availability-box a { color: #4b3fd0; text-decoration: none; font-weight: 700; }
  .availability-line { font-size: 10px; color: #63645F; page-break-inside: avoid; }
  .footer { margin-top: 32px; padding-top: 12px; border-top: 1px solid #DDDDD6; font-size: 9px; color: #aaa; text-align: center; }
  .footer a { color: #aaa; text-decoration: none; }
</style>
</head>
<body>
<div class="page">

  <div class="header">
    <div class="name">{{ $profile->name }}</div>
    <div class="tagline">{{ $profile->localizedTagline() }} · {{ $profile->city }}, {{ __('cv.country') }}</div>
    <div class="contact-row">
      @if($profile->email)<span><a href="mailto:{{ $profile->email }}">{{ $profile->email }}</a></span>@endif
      @if($profile->linkedin_url)<span><a href="{{ $profile->linkedin_url }}">{{ $profile->linkedin_url }}</a></span>@endif
      @if($profile->github_url)<span><a href="{{ $profile->github_url }}">{{ $profile->github_url }}</a></span>@endif
      <span>{{ __('cv.rate_on_request') }}</span>
    </div>
  </div>

  <div class="section">
    <div class="section-title">{{ __('cv.section_about') }}</div>
    @if($profile->heroHeadline())
      <div class="hero-line">{{ $profile->heroHeadline() }}</div>
    @endif
    <p class="intro">{{ $profile->heroSubtext() }}</p>
  </div>

  @if ($skills->isNotEmpty())
  <div class="section">
    <div class="section-title">{{ __('cv.section_skills') }}</div>
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

  <div class="section">
    <div class="section-title">{{ __('cv.section_education') }}</div>
    <div class="edu-row">
      <div class="edu-degree">{{ __('cv.education.mbo3_degree') }} <span>· {{ __('cv.education.mbo3_field') }}</span></div>
      <div class="edu-place">2013-2016 &middot; {{ __('cv.education.school') }}</div>
    </div>
    <div class="edu-row">
      <div class="edu-degree">{{ __('cv.education.mbo4_degree') }} <span>· {{ __('cv.education.mbo4_field') }}</span></div>
      <div class="edu-place">2016-2017 &middot; {{ __('cv.education.school') }}</div>
    </div>
  </div>

  @if ($projects->isNotEmpty())
  <div class="section">
    <div class="section-title-row">
      <div class="section-title">{{ __('cv.section_case_studies') }}</div>
      <a class="section-cta" href="{{ localized_route('work.index') }}">{{ __('cv.see_all_work') }} &rarr;</a>
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
          <div class="project-outcome"><span class="accent">{{ __('cv.result_label') }}</span> {{ $project->outcome }}</div>
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
      <strong>{{ __('cv.available_heading') }}</strong> {{ __('cv.available_body', ['city' => $profile->city]) }}
      @if($profile->email)
        <a href="mailto:{{ $profile->email }}">{{ __('cv.get_in_touch') }} &rarr;</a>
      @endif
    </div>
  @else
    <div class="availability-line">
      {{ $profile->availability_from
          ? __('cv.booked_from', ['date' => $profile->availability_from, 'city' => $profile->city])
          : __('cv.booked', ['city' => $profile->city]) }}
    </div>
  @endif

  <div class="footer">
    @if ($profile->kvk_number)
      KVK {{ $profile->kvk_number }} ·
    @endif
    {{ __('cv.generated', ['date' => now()->locale(app()->getLocale())->isoFormat('MMMM YYYY')]) }} ·
    <a href="{{ localized_route('home') }}">{{ parse_url(route('home'), PHP_URL_HOST) }}</a>
  </div>

</div>
</body>
</html>
