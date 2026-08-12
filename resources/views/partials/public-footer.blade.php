<footer class="site">
  <div class="wrap">
    <span>&copy; {{ date('Y') }} {{ $profile->name }}. {{ __('site.footer_built') }}</span>
    <span>
      @if ($profile->github_url)<a href="{{ $profile->github_url }}" target="_blank" rel="noopener noreferrer">GitHub</a> &middot; @endif
      @if ($profile->linkedin_url)<a href="{{ $profile->linkedin_url }}" target="_blank" rel="noopener noreferrer">LinkedIn</a> &middot; @endif
      <a href="{{ localized_route('cv') }}">CV</a>
      @if ($profile->kvk_number) &middot; KVK {{ $profile->kvk_number }}@endif
    </span>
  </div>
</footer>
