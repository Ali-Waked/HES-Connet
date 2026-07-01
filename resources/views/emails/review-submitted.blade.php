<x-email.layout :title="$title ?? ''">
  <x-email.header />

  <x-email.card>
    <x-email.accent-line />

    <x-email.heading>{{ $heading }}</x-email.heading>
    <x-email.subheading>{{ $subheading }}</x-email.subheading>

    <x-email.divider />

    <x-email.greeting :name="$userName" />

    @foreach ($messages as $msg)
      <x-email.message>{{ $msg }}</x-email.message>
    @endforeach

    @if ($rtlMessage ?? null)
      <x-email.rtl-block>{{ $rtlMessage }}</x-email.rtl-block>
    @endif

    @if ($ctaUrl ?? null)
      <x-email.cta-button :url="$ctaUrl">{{ $ctaLabel ?? 'Go to Dashboard' }}</x-email.cta-button>
    @endif

    <x-email.divider />

    <tr>
      <td style="padding-bottom:4px;">
        <p style="margin:0;font-size:13px;color:#94a3b8;line-height:1.6;text-align:center;">
          {{ __('Need help?') }}
          <a href="{{ $supportUrl ?? '#' }}" target="_blank" style="color:#027a75;text-decoration:underline;font-weight:500;">{{ __('Support Center') }}</a>
        </p>
      </td>
    </tr>
  </x-email.card>

  <x-email.footer
    :platform="$platform"
    :disclaimer="$disclaimer"
    :privacyUrl="$privacyUrl ?? '#'"
  />
</x-email.layout>
