<x-mail::message>
# {{ $title }}

{!! nl2br(e($body)) !!}

@if($contentUrl)
<x-mail::button :url="$contentUrl" color="primary">
{{ __('Read More') }}
</x-mail::button>
@endif

---

<x-mail::panel>
{{ __('subscriptions.notifications.footer_text', ['type' => ucfirst($type)]) }}
</x-mail::panel>

<x-mail::subcopy>
[{{ __('Manage Subscription') }}]({{ $manageUrl }}) &nbsp;|&nbsp; [{{ __('Unsubscribe') }}]({{ $unsubscribeUrl }})
</x-mail::subcopy>
</x-mail::message>
