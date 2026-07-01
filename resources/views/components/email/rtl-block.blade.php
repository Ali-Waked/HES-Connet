@if($slot)
<tr>
  <td style="padding-bottom:24px;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color:#f8fafc;border-radius:12px;border-left:4px solid #027a75;">
      <tr>
        <td style="padding:16px 20px;font-size:14px;color:#475569;line-height:1.7;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif;" dir="rtl">
          {{ $slot }}
        </td>
      </tr>
    </table>
  </td>
</tr>
@endif
