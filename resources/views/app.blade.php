<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {!! SEOMeta::generate() !!}
    {!! OpenGraph::generate() !!}
    {!! Twitter::generate() !!}
    {!! JsonLd::generate() !!}
    @routes
    @viteReactRefresh
    @vite(['resources/js/local.tsx', "resources/js/Pages/{$page['component']}.tsx"])
    @inertiaHead
</head>
<body class="font-sans antialiased bg-blue-900">
@inertia
</body>
</html>
