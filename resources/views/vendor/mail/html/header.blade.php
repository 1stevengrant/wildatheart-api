{{--
    The logo is a PNG rather than resources/img/logo.svg because Gmail and
    Outlook strip SVG entirely. Its URL comes from mail.logo_url (the public
    production copy of public/img/email-logo.png by default) rather than
    asset(), because a local or scheme-less APP_URL leaves recipients with an
    unreachable localhost link.

    Many clients block images by default, so the alt text is the real fallback
    header and is styled to stay legible on black when the image does not load.
--}}
@props(['url'])
<tr>
<td class="header" bgcolor="#010101">
<a href="{{ $url }}" style="display: inline-block;">
<img src="{{ config('mail.logo_url') ?: asset('img/email-logo.png') }}"
     class="logo"
     width="164"
     alt="Wild at Heart"
     style="border: 0; display: block; margin: 0 auto; max-width: 164px; width: 164px; height: auto; color: #E7A26B; font-size: 19px; font-weight: bold;">
</a>
</td>
</tr>
