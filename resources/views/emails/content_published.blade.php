<x-email.layout :title="$title ?? ''">
    <x-email.header />

    <x-email.card>
        <x-email.accent-line />

        <x-email.heading>{{ $title }}</x-email.heading>

        <x-email.divider />

        <x-email.message>{!! nl2br(e($body)) !!}</x-email.message>

        @if($contentUrl)
            <x-email.cta-button :url="$contentUrl">
                {{ __('Read More') }}
            </x-email.cta-button>
        @endif

        <x-email.divider />

        <tr>
            <td style="padding-bottom:20px;">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f8fafc;border-radius:12px;border-left:4px solid #027a75;">
                    <tr>
                        <td style="padding:16px 20px;font-size:14px;color:#475569;line-height:1.7;">
                            <p style="margin:0;font-size:13px;color:#64748b;line-height:1.5;">
                                {{ __('subscriptions.notifications.footer_text', ['type' => ucfirst($type)]) }}
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>

        <x-email.help-text>
            <a href="{{ $manageUrl }}" target="_blank" style="color:#027a75;text-decoration:underline;font-weight:500;">{{ __('Manage Subscription') }}</a>
            &nbsp;|&nbsp;
            <a href="{{ $unsubscribeUrl }}" target="_blank" style="color:#027a75;text-decoration:underline;font-weight:500;">{{ __('Unsubscribe') }}</a>
        </x-email.help-text>
    </x-email.card>

    <x-email.footer />
</x-email.layout>
