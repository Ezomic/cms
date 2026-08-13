{{--
  No part of the submitted message is echoed here. Anyone able to pass the
  contact form's bot checks can trigger this email to an address of their
  choosing, so quoting their text back would turn the form into a delivery
  mechanism aimed at third parties (CMS-116).
--}}
<x-mail::message>
# {{ __('site.contact_ack_heading') }}

{{ __('site.contact_ack_body', ['name' => $name]) }}

{{ __('site.contact_ack_reply', ['email' => $ownerEmail]) }}

{{ __('site.contact_ack_signoff') }}<br>
{{ config('app.name') }}
</x-mail::message>
