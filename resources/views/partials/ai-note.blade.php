@push('styles')
<style>
  .ai-note{margin-top:44px;padding:24px 26px;background:var(--surface);border:1px solid var(--line);border-radius:var(--r-lg);max-width:70ch;}
  .ai-note h3{font-size:17px;margin-bottom:10px;}
  .ai-note p{font-size:14.5px;color:var(--muted);}
  .ai-note p + p{margin-top:10px;}
  .ai-note a{color:var(--primary);text-decoration:underline;text-underline-offset:3px;}
</style>
@endpush
<div class="ai-note">
  <h3>{{ __('site.ai_headline') }}</h3>
  <p>{{ __('site.ai_p1') }}</p>
  <p>{!! __('site.ai_p2', ['tracker' => '<a href="'.e(localized_route('project.show', ['project' => 'tracker'])).'">Tracker</a>']) !!}</p>
</div>
