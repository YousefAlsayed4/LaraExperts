<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ __('filament-panels::layout.direction') ?? 'ltr' }}" class="antialiased filament js-focus-visible">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="application-name" content="{{ config('app.name', 'Laravel') }}">

    <!-- SEO Tags -->
    {{-- <x-seo::meta/> --}}
    <!-- SEO Tags -->

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

    @livewireStyles
    @filamentStyles
    @stack('styles')

    <link rel="stylesheet" href="{{ asset('vendor/lara-experts/frontend.css') }}">

    <style>
        * { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="font-sans antialiased bg-gray-50 text-gray-900 dark:text-gray-100 dark:bg-gray-900 @if(app()->isLocal()) debug-screens @endif">

<header x-data="{ open: false }" class="bg-white dark:bg-gray-900 shadow-md px-6 py-4">
    <div class="container mx-auto flex justify-between items-center">
        <a class="flex items-center gap-2 text-lg font-semibold text-gray-800 dark:text-white hover:text-gray-600" href="{{ url('/') }}">
            {{-- <img class="w-8" src="https://larazeus.com/images/zeus-logo.webp" alt="{{ config('form.site_title', config('app.name', 'Laravel')) }}"> --}}
            LaraExperts
        </a>
        <nav class="hidden sm:flex space-x-6 text-gray-700 dark:text-gray-300">
            {{-- Navigation Links --}}
        </nav>
    </div>
</header>

<header class="bg-gray-100 dark:bg-gray-800 py-3 px-6 shadow-sm">
    <div class="container mx-auto flex justify-between items-center">
        <div class="w-full">
            @if(isset($breadcrumbs))
                <nav class="text-gray-500 font-semibold text-sm" aria-label="Breadcrumb">
                    <ol class="list-none p-0 inline-flex">{{ $breadcrumbs }}</ol>
                </nav>
            @endif
            @if(isset($header))
                <h1 class="text-xl font-bold text-gray-700 dark:text-gray-100 mt-1">
                    {{ $header }}
                </h1>
            @endif
        </div>
    </div>
</header>

<div class="container mx-auto my-8 px-6 py-10">
    {{ $slot }}
</div>

<footer class="bg-gray-100 dark:bg-gray-800 py-6 text-center text-gray-600 dark:text-gray-300 text-sm">
    <p>by LaraExperts</p>
</footer>

@livewireScripts
@filamentScripts
@livewire('notifications')
@stack('scripts')

<script>
    const theme = localStorage.getItem('theme')
    if ((theme === 'dark') || (!theme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark')
    }
</script>

</body>
</html>


