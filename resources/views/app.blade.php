<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0"
    />

{{--    {!! SEOMeta::generate() !!}--}}
{{--    {!! OpenGraph::generate() !!}--}}
{{--    {!! Twitter::generate() !!}--}}
{{--    {!! JsonLd::generate() !!}--}}
    @include('partials.analytics')
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <link
        rel="icon"
        type="image/svg+xml"
        href="{{ asset('favicon.svg') }}"
    />
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://use.typekit.net" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link rel="preconnect" href="https://fonts.bunny.net" />
    <link
        href="https://fonts.bunny.net/css?family=inter:100,200,300,400,500,600,700,800,900"
        rel="stylesheet"
    />
    @routes
    @viteReactRefresh
    @vite(['resources/js/local.tsx', "resources/js/Pages/{$page['component']}.tsx"])
    @inertiaHead
</head>
<body class="font-sans antialiased bg-blue-900 text-white">
@inertia
</body>
</html>
