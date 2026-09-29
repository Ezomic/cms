<x-mail::message>
# {{ $period }}

**{{ number_format($views) }} {{ Str::plural('page view', $views) }}**, against {{ number_format($previousViews) }} the week before.

@if ($topPaths !== [])
## Top pages

<x-mail::table>
| Page | Views |
| :--- | ----: |
@foreach ($topPaths as $row)
| {{ $row['path'] }} | {{ number_format($row['views']) }} |
@endforeach
</x-mail::table>
@endif

@if ($topReferrers !== [])
## Top referrers

<x-mail::table>
| Source | Views |
| :----- | ----: |
@foreach ($topReferrers as $row)
| {{ $row['host'] }} | {{ number_format($row['views']) }} |
@endforeach
</x-mail::table>
@endif

@if ($unreadEnquiries > 0)
## Enquiries

{{ $unreadEnquiries }} unread {{ Str::plural('enquiry', $unreadEnquiries) }} in the inbox.

<x-mail::button :url="$inboxUrl">
Open the inbox
</x-mail::button>
@endif

{{ config('app.name') }}
</x-mail::message>
