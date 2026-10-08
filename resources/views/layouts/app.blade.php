<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'ChinaBay – Quality Meets Connection')</title>
    <meta name="description" content="@yield('meta', 'China imported products, gadgets, home appliances and lifestyle — cash on delivery across Bangladesh.')">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    @endif
    {{-- Alpine.js: future Livewire companion. x-data hooks already in place. --}}
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @stack('head')
</head>
<body class="bg-white font-sans text-ink antialiased" x-data="{ mobileSearch: false }">
<div class="mx-auto max-w-[1280px] px-3 sm:px-5">
    <x-shop.header :cart-count="0" :wish-count="0" />

    <main>
        @yield('content')
    </main>

    <x-shop.footer />
</div>

<x-shop.drawers />
<div id="toast" class="pointer-events-none fixed bottom-5 left-1/2 z-[60] -translate-x-1/2 translate-y-4 rounded-full bg-ink px-5 py-2.5 text-sm text-white opacity-0 shadow-lg transition" role="status"></div>

@stack('scripts')
</body>
</html>
