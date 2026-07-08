<x-email.layout :title="__('subscriptions.verification.title')">
    <x-email.header />

    <x-email.card>
        <x-email.accent-line />

        <x-email.heading>{{ __('subscriptions.verification.title') }}</x-email.heading>

        <x-email.subheading>{{ __('subscriptions.verification.body') }}</x-email.subheading>

        <x-email.divider />

        <x-email.cta-button :url="$url">
            {{ __('subscriptions.verification.button') }}
        </x-email.cta-button>

        <x-email.divider />

        <x-email.message>{{ __('This link is valid for your subscription to') }} <strong>{{ config('app.name') }}</strong>.</x-email.message>

        <x-email.message>{{ __('If you did not request this, you can safely ignore this email.') }}</x-email.message>

        <x-email.help-text>
            {{ __('Thanks') }},<br>
            {{ config('app.name') }}
        </x-email.help-text>
    </x-email.card>

    <x-email.footer />
</x-email.layout>
