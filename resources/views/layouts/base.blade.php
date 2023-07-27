<!DOCTYPE html>
<html lang="en">
    <head>
        @include('partials.analytics')
        <meta charset="UTF-8" />
        <meta
            name="viewport"
            content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0"
        />
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
        <link
            href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@200;300;400;600;700;900&family=Rock+Salt&display=swap"
            rel="stylesheet"
        />
        <link rel="stylesheet" href="https://use.typekit.net/utc5cwe.css" />
        <link rel="preconnect" href="https://fonts.bunny.net" />
        <link
            href="https://fonts.bunny.net/css?family=inter:100,200,300,400,500,600,700,800,900"
            rel="stylesheet"
        />
        @vite('resources/css/app.css')
        @livewireStyles
        @stack('head')
        <link
            href="https://api.mapbox.com/mapbox-gl-js/v2.14.1/mapbox-gl.css"
            rel="stylesheet"
        />
        <script src="https://api.mapbox.com/mapbox-gl-js/v2.14.1/mapbox-gl.js"></script>
    </head>

    <body class="bg-blue-900 font-sans" x-data="{ openMenu: false }">
        {{ $slot }}
        @livewire('modal-pro')
        @livewireScripts
        @vite('resources/js/app.js')
        @stack('scripts')
        <x-notification />
    </body>
</html>
