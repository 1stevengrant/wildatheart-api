{{--
    Wild at Heart mail layout.

    Diverges from Laravel's default in three ways, all in service of the dark
    design surviving real mail clients:

    1. bgcolor attributes mirror every CSS background-color. Outlook's Word
       rendering engine ignores background-color on table cells but honours
       bgcolor, so without these the dark design falls back to white.
    2. color-scheme is declared as dark so Apple Mail and iOS stop applying
       their own inversion on top of a design that is already dark.
    3. [data-ogsc] / [data-ogsb] rules re-assert our colours for Outlook.com,
       which rewrites inline styles when the reader has dark mode enabled.
--}}
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<title>{{ config('app.name') }}</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
<meta name="color-scheme" content="dark">
<meta name="supported-color-schemes" content="dark">
<style>
:root {
color-scheme: dark;
supported-color-schemes: dark;
}

@media only screen and (max-width: 600px) {
.inner-body {
width: 100% !important;
}

.footer {
width: 100% !important;
}

.content-cell {
padding: 24px !important;
}
}

@media only screen and (max-width: 500px) {
.button {
width: 100% !important;
}
}

/* Outlook.com rewrites inline colours in dark mode; re-assert ours. */
[data-ogsc] .wrapper,
[data-ogsb] .wrapper,
[data-ogsc] .body,
[data-ogsb] .body {
background-color: #010101 !important;
}

[data-ogsc] .inner-body,
[data-ogsb] .inner-body {
background-color: #0D151A !important;
}

[data-ogsc] p,
[data-ogsc] li {
color: #E0E1E2 !important;
}

[data-ogsc] h1,
[data-ogsc] h2,
[data-ogsc] h3 {
color: #ffffff !important;
}

[data-ogsc] .inner-body a {
color: #E7A26B !important;
}

[data-ogsc] .button-primary {
background-color: #964300 !important;
color: #ffffff !important;
}
</style>
{!! $head ?? '' !!}
</head>
<body bgcolor="#010101">

<table class="wrapper" width="100%" cellpadding="0" cellspacing="0" role="presentation" bgcolor="#010101">
<tr>
<td align="center" bgcolor="#010101">
<table class="content" width="100%" cellpadding="0" cellspacing="0" role="presentation">
{!! $header ?? '' !!}

<!-- Email Body -->
<tr>
<td class="body" width="100%" cellpadding="0" cellspacing="0" bgcolor="#010101" style="border: hidden !important;">
<table class="inner-body" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation" bgcolor="#0D151A">
<!-- Body content -->
<tr>
<td class="content-cell">
{!! Illuminate\Mail\Markdown::parse($slot) !!}

{!! $subcopy ?? '' !!}
</td>
</tr>
</table>
</td>
</tr>

{!! $footer ?? '' !!}
</table>
</td>
</tr>
</table>
</body>
</html>
