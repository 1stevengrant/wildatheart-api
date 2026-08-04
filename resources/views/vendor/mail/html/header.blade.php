{{--
    The logo is a PNG rather than resources/img/logo.svg because Gmail and
    Outlook strip SVG entirely. It is served from public/ so the URL is stable
    and absolute; asset() resolves against APP_URL, which queued mail needs.

    Many clients block images by default, so the alt text is the real fallback
    header and is styled to stay legible on black when the image does not load.
--}}
@props(['url'])
<tr>
<td class="header" bgcolor="#010101">
<a href="{{ $url }}" style="display: inline-block;">
<img src="{{ asset('img/email-logo.png') }}"
     class="logo"
     width="164"
     alt="Wild at Heart"
     style="border: 0; display: block; margin: 0 auto; max-width: 164px; width: 164px; height: auto; color: #E7A26B; font-size: 19px; font-weight: bold;">
</a>
</td>
</tr>
