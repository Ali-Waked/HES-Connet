<x-email.layout :lang="$lang ?? 'en'" :dir="$dir ?? 'ltr'">
    <x-email.header />

    <x-email.card>
        <x-email.greeting name="{{ $name ?? '' }}" />

        @isset($title)
            <x-email.heading>{{ $title }}</x-email.heading>
        @endisset

        <x-email.message>{{ $body ?? '' }}</x-email.message>

        @isset($actionText, $actionUrl)
            <x-email.cta-button :url="$actionUrl">
                {{ $actionText }}
            </x-email.cta-button>
        @endisset
    </x-email.card>

    <x-email.footer />
</x-email.layout>
