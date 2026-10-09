<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'ChinaBay Admin')</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="16x16 32x32 48x48">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @else
        <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    @endif
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    {{-- Admin-editable theme (Settings > Theme). --}}
    <style>:root {
        --color-brand: {{ \App\Models\Setting::get('theme.brand', '#c8102e') }};
        --color-brand-dark: {{ \App\Models\Setting::get('theme.brand_dark', '#a50d25') }};
        --color-ink: {{ \App\Models\Setting::get('theme.ink', '#1b2233') }};
        --color-navy: {{ \App\Models\Setting::get('theme.navy', '#1c2536') }};
    }</style>
</head>
<body class="bg-slate-100 font-sans text-ink antialiased" x-data="{ sidebar: window.innerWidth >= 1024 }">
<div class="flex min-h-screen">
    <x-admin.sidebar />

    <div class="min-w-0 flex-1">
        <header class="sticky top-0 z-20 flex items-center gap-3 border-b border-slate-200 bg-white/95 px-4 py-3 backdrop-blur sm:px-6">
            <button @click="sidebar = !sidebar" class="grid size-10 place-items-center rounded-lg bg-slate-100 hover:bg-brand hover:text-white" aria-label="Toggle sidebar">☰</button>
            <div>
                <p class="text-[11px] text-slate-500">ChinaBay Admin</p>
                <h1 class="text-base font-extrabold">@yield('heading', 'Dashboard')</h1>
            </div>
            <div class="ml-auto flex items-center gap-2 text-xs">
                <span class="hidden rounded-full bg-emerald-50 px-3 py-1.5 font-semibold text-emerald-700 sm:inline">● Online</span>
                <a href="/" class="btn-outline px-3 py-2 text-xs">View Store →</a>
                <span class="hidden font-semibold text-slate-500 sm:inline">{{ auth()->user()?->name }}</span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn-outline px-3 py-2 text-xs">Logout</button>
                </form>
                <span class="grid size-9 place-items-center rounded-full bg-brand font-bold text-white">{{ strtoupper(mb_substr(auth()->user()?->name ?? 'A', 0, 1)) }}</span>
            </div>
        </header>

        <main class="p-4 sm:p-6">
            @yield('content')
        </main>
    </div>
</div>
@stack('scripts')
</body>
</html>
