<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        @livewireStyles
        @livewireScripts
    </head>
    <body class="font-sans antialiased">
        <style>
            [x-cloak] { display: none !important; }
        </style>
        <div class="flex flex-col bg-gray-100 dark:bg-gray-900 justify-between">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @if (isset($header))
                <header class="bg-white dark:bg-gray-800 shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <!-- Page Content -->
            <main class="mb-auto">
                {{ $slot }}
            </main>

            <footer id="footer" class="w-full h-64 bg-gray-100 dark:bg-gray-700 text-black dark:text-white static bottom-0 p-2">
                <livewire:nwws-log />
            </footer>

        </div>
    </body>
</html>
