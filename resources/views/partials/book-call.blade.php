@php
    $bookingUrl = config('site.booking_url');
    $contactHref = $contactHref ?? '#contact';
@endphp
<div class="book-call-wrap">
  @if ($bookingUrl)
    <a class="btn pri book-call" href="{{ $bookingUrl }}" target="_blank" rel="noopener noreferrer">
      {{ __('site.book_call') }}
    </a>
  @else
    <a class="btn pri book-call" href="{{ $contactHref }}">
      {{ __('site.book_call') }}
    </a>
  @endif
  <span class="book-call-note">{{ __('site.book_call_promise') }}</span>
</div>
