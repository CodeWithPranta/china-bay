<!DOCTYPE html>
<html lang="{{ $appLocale ?? str_replace('_', '-', app()->getLocale()) }}" dir="{{ $appDirection ?? 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'ChinaBay – Quality Meets Connection')</title>
    <meta name="description" content="@yield('meta', 'China imported products, gadgets, home appliances and lifestyle — cash on delivery across Bangladesh.')">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="16x16 32x32 48x48">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Hind+Siliguri:wght@400;500;600;700&family=Noto+Naskh+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    @endif
    {{-- Alpine.js ships inside Livewire (force-injected on every page via
        ForceLivewireAssets middleware). Do NOT add a second Alpine copy —
        duplicate instances break Livewire filters and x-data islands. --}}
    {{-- Admin-editable theme (Settings > Theme) overrides compiled defaults. --}}
    @php
        $theme = [
            '--color-brand' => \App\Models\Setting::get('theme.brand', '#c8102e'),
            '--color-brand-dark' => \App\Models\Setting::get('theme.brand_dark', '#a50d25'),
            '--color-ink' => \App\Models\Setting::get('theme.ink', '#1b2233'),
            '--color-navy' => \App\Models\Setting::get('theme.navy', '#1c2536'),
        ];
    @endphp
    <style>:root { @foreach ($theme as $var => $val){{ $var }}: {{ $val }}; @endforeach }</style>
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
<x-shop.ai-search />
<div id="toast" class="pointer-events-none fixed bottom-5 left-1/2 z-[60] -translate-x-1/2 translate-y-4 rounded-full bg-ink px-5 py-2.5 text-sm text-white opacity-0 shadow-lg transition" role="status"></div>

@stack('scripts')
</body>
</html>
