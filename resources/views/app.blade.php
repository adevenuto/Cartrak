<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        {{-- viewport-fit=cover is required or env(safe-area-inset-*) resolves to 0px
             on iOS, and the bottom nav sits under the home indicator. --}}
        <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">

        {{-- Inline style to set the HTML background color based on our theme in app.css.
             This runs before the CSS bundle loads, so it cannot use var(); the value
             MUST track --surface-page in resources/css/cartrak/ds-tokens.css, or cold
             loads flash the wrong colour. --}}
        <style>
            html {
                background-color: #ECEAEB;
            }
        </style>

        <meta name="theme-color" content="#ECEAEB">

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">
        <link rel="apple-touch-icon" href="/apple-touch-icon.png">

        @fonts

        @vite(['resources/css/app.css', 'resources/js/app.ts', "resources/js/pages/{$page['component']}.vue"])
        <x-inertia::head>
            <title>{{ config('app.name', 'Laravel') }}</title>
        </x-inertia::head>
    </head>
    <body class="font-sans antialiased">
        <x-inertia::app />
    </body>
</html>
