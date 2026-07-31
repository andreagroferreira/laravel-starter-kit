<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'WizardInCode') }}</title>

        <script>
            // Dark-first without FOUC: resolve the theme before any paint.
            // Uses the same storage key as VueUse/Nuxt UI's useColorMode.
            (function () {
                var stored = localStorage.getItem('vueuse-color-scheme');
                var dark = stored === 'dark' || stored === null || stored === 'auto' && window.matchMedia('(prefers-color-scheme: dark)').matches;
                if (dark) {
                    document.documentElement.classList.add('dark');
                }
            })();
        </script>

        @vite(['resources/js/app.ts'])
        @inertiaHead
    </head>
    <body class="h-full antialiased">
        @inertia
    </body>
</html>
