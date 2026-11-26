<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Balsheet') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <style>
            /* Print-friendly: hide the sidebar/topbar when printing reports/receipts. */
            @media print {
                aside, header { display: none !important; }
                main { padding-left: 0 !important; }
            }
        </style>
    </head>
    <body class="font-sans text-gray-800 antialiased">
        <div x-data="{ sidebarOpen: false }" class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Content -->
            <main class="lg:pl-64">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    @if (session('status'))
                        <div class="mb-4 rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
                            {{ session('status') }}
                        </div>
                    @endif

                    @isset($header)
                        <div class="mb-6">
                            {{ $header }}
                        </div>
                    @endisset

                    {{ $slot }}
                </div>
            </main>
        </div>
    </body>
</html>
