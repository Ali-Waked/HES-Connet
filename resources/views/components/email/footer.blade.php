<tr>
  <td style="padding-top:28px;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
      <x-email.social-icons
        facebook="{{ $facebook ?? '#' }}"
        twitter="{{ $twitter ?? '#' }}"
        instagram="{{ $instagram ?? '#' }}"
        linkedin="{{ $linkedin ?? '#' }}"
      />
      <tr>
        <td align="center" style="padding-bottom:6px;">
          <p style="margin:0;font-size:12px;color:#94a3b8;line-height:1.5;">&copy; {{ date('Y') }} {{ $platform ?? 'HES Connect' }}. All rights reserved.</p>
        </td>
      </tr>
      <tr>
        <td align="center" style="padding-bottom:4px;">
          <p style="margin:0;font-size:11px;color:#cbd5e1;line-height:1.5;max-width:420px;">{{ $disclaimer ?? '' }}</p>
        </td>
      </tr>
      <tr>
        <td align="center" style="padding-top:4px;">
          <p style="margin:0;font-size:11px;color:#94a3b8;line-height:1.5;">
            @if($unsubscribeUrl ?? null)
              <a href="{{ $unsubscribeUrl }}" target="_blank" style="color:#94a3b8;text-decoration:underline;">Unsubscribe</a> &nbsp;|&nbsp;
            @endif
            <a href="{{ $privacyUrl ?? '#' }}" target="_blank" style="color:#94a3b8;text-decoration:underline;">Privacy Policy</a>
          </p>
        </td>
      </tr>
    </table>
  </td>
</tr>
