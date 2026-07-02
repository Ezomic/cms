<x-mail::message>
# New message from your site

**From:** {{ $submission->name }} ({{ $submission->email }})

{{ $submission->message }}

Thanks,<br>
{{ config('app.name') }}
</x-mail::message>
