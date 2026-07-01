<x-mail::message>
# {{ __('subscriptions.verification.title') }}

{{ __('subscriptions.verification.body') }}

<x-mail::button :url="$url" color="primary">
{{ __('subscriptions.verification.button') }}
</x-mail::button>

{{ __('This link is valid for your subscription to') }} **{{ config('app.name') }}**.

{{ __('If you did not request this, you can safely ignore this email.') }}

{{ __('Thanks') }},
{{ config('app.name') }}
</x-mail::message>
