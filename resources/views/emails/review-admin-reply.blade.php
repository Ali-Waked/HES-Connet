<x-email.layout :title="$title ?? ''">
  <x-email.header />

  <x-email.card>
    <x-email.accent-line />

    <x-email.heading>{{ $heading }}</x-email.heading>
    <x-email.subheading>{{ $subheading }}</x-email.subheading>

    <x-email.divider />

    <x-email.greeting :name="$userName" />

    <x-email.message>{{ $preMessage }}</x-email.message>

    <tr>
      <td style="padding-bottom:20px;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f8fafc;border-radius:12px;border-left:4px solid #027a75;">
          <tr>
            <td style="padding:16px 20px;font-size:14px;color:#475569;line-height:1.7;">
              <p style="margin:0 0 8px 0;font-weight:600;color:#0f172a;">{{ __('Admin Reply') }}:</p>
              <p style="margin:0;font-style:italic;">{{ $adminReply }}</p>
            </td>
          </tr>
        </table>
      </td>
    </tr>

    <x-email.divider />

    <tr>
      <td style="padding-bottom:12px;">
        <p style="margin:0;font-size:13px;color:#94a3b8;line-height:1.5;">
          {{ __('Your original review') }} ({{ $rating }}/5):
        </p>
      </td>
    </tr>

    @if ($reviewMessage ?? null)
      <x-email.message>{{ $reviewMessage }}</x-email.message>
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
