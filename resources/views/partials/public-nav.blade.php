@php
    $onHome = request()->routeIs('home') || request()->routeIs('nl.home');
    $homeBase = $onHome ? '' : localized_route('home');
@endphp
<nav class="site">
  <div class="wrap">
    <a class="logo" href="{{ localized_route('home') }}"><span class="b"></span>{{ $profile->name }}</a>
    <div class="nav-links nav-desktop">
      <a class="link {{ request()->routeIs('work.*') || request()->routeIs('nl.work.*') || request()->routeIs('project.show') || request()->routeIs('nl.project.show') ? 'on' : '' }}" href="{{ localized_route('work.index') }}">{{ __('site.nav_work') }}</a>
      <a class="link {{ request()->routeIs('blog.*') || request()->routeIs('nl.blog.*') ? 'on' : '' }}" href="{{ localized_route('blog.index') }}">{{ __('site.nav_blog') }}</a>
      <a class="link" href="{{ $homeBase }}#services">{{ __('site.nav_services') }}</a>
      <a class="link {{ request()->routeIs('docs') || request()->routeIs('nl.docs') ? 'on' : '' }}" href="{{ localized_route('docs') }}">{{ __('site.nav_docs') }}</a>
      <a class="lang-toggle" href="{{ alternate_locale_url(app()->getLocale() === 'en' ? 'nl' : 'en') }}">{{ __('site.lang_toggle') }}</a>
      <a class="btn pri sm" href="{{ $homeBase }}#contact">{{ __('site.nav_cta') }}</a>
    </div>
    <button class="nav-burger" type="button" aria-expanded="false" aria-controls="mobile-menu" aria-label="{{ __('site.nav_menu_label') }}">
      <span></span><span></span><span></span>
    </button>
  </div>
  <div class="mobile-menu" id="mobile-menu">
    <div class="wrap">
      <a href="{{ localized_route('work.index') }}">{{ __('site.nav_work') }}</a>
      <a href="{{ localized_route('blog.index') }}">{{ __('site.nav_blog') }}</a>
      <a href="{{ $homeBase }}#services">{{ __('site.nav_services') }}</a>
      <a href="{{ localized_route('docs') }}">{{ __('site.nav_docs') }}</a>
      <a href="{{ $homeBase }}#contact">{{ __('site.nav_contact') }}</a>
      <a href="{{ alternate_locale_url(app()->getLocale() === 'en' ? 'nl' : 'en') }}">{{ __('site.lang_toggle_long') }}</a>
    </div>
  </div>
</nav>
