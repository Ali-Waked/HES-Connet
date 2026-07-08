<x-email.layout :lang="$lang ?? 'en'" :dir="$dir ?? 'ltr'" :title="$title ?? ''">
    <x-email.header />

    <x-email.card>
        @unless(($showAccentLine ?? true) === false)
            <x-email.accent-line />
        @endunless

        @isset($heading)
            <x-email.heading>{{ $heading }}</x-email.heading>
        @elseisset($title)
            <x-email.heading>{{ $title }}</x-email.heading>
        @endisset

        @isset($subheading)
            <x-email.subheading>{{ $subheading }}</x-email.subheading>
        @endisset

        @unless(($showDivider ?? true) === false)
            <x-email.divider />
        @endunless

        @isset($name)
            <x-email.greeting name="{{ $name }}" />
        @endisset

        <x-email.message>{{ $body ?? '' }}</x-email.message>

        @isset($rtlMessage)
            <x-email.rtl-block>{{ $rtlMessage }}</x-email.rtl-block>
        @endisset

        @isset($actionText, $actionUrl)
            <x-email.cta-button :url="$actionUrl">
                {{ $actionText }}
            </x-email.cta-button>
        @endisset

        @unless(($showDivider ?? true) === false)
            <x-email.divider />
        @endunless

        @isset($helpText)
            <x-email.help-text>{!! $helpText !!}</x-email.help-text>
        @else
            <x-email.help-text>
                {{ __('Need help?') }}
                <a href="{{ $supportUrl ?? '#' }}" target="_blank" style="color:#027a75;text-decoration:underline;font-weight:500;">{{ __('Support Center') }}</a>
            </x-email.help-text>
        @endisset
    </x-email.card>

    <x-email.footer
        :platform="$platform ?? null"
        :disclaimer="$disclaimer ?? null"
        :privacyUrl="$privacyUrl ?? '#'"
        :unsubscribeUrl="$unsubscribeUrl ?? null"
    />
</x-email.layout>
