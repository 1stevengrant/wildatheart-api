<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0"
    />
    <x-opengraph />
    <x-analytics />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="ie=edge" />
    <x-favicons />

    <link rel="stylesheet" href="https://use.typekit.net/qce7npw.css">
    @routes
    @viteReactRefresh
    @vite(['resources/js/local.tsx', "resources/js/Pages/{$page['component']}.tsx"])
    @inertiaHead
</head>
<body class="font-sans antialiased bg-black text-white selection:bg-p200 selection:text-white h-full">
@inertia
</body>
</html>
