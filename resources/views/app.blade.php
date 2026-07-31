<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'GBA') }}</title>

        @vite(['resources/js/app.ts'])
        @inertiaHead
    </head>
    <body class="h-full antialiased">
        @inertia
    </body>
</html>
