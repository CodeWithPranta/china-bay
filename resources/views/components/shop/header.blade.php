@props(['cartCount' => 0, 'wishCount' => 0])
@php
    use App\Support\ShopDemo;
    $cats = ShopDemo::categories();
    $links = [
        [__('shop.nav_home'), '/'],
        [__('shop.nav_shop'), '/shop'],
        [__('shop.nav_new'), '/shop?sort=new'],
        [__('shop.nav_deals'), '/flash-sale'],
        [__('shop.nav_track'), '/track-order'],
        [__('shop.nav_about'), '/about'],
        [__('shop.nav_contact'), '/contact'],
    ];
@endphp

<div class="flex items-center justify-between gap-4 rounded-t-sm bg-brand px-4 py-2 text-[11px] text-white">
    <div class="flex flex-wrap gap-x-6 gap-y-1">
        <span>🚚 {{ __('shop.top_delivery') }}</span>
        <span class="hidden sm:inline">🛡️ {{ __('shop.top_packaging') }}</span>
        <span class="hidden md:inline">🌐 {{ __('shop.top_coverage') }}</span>
    </div>
    <div class="flex items-center gap-3">
        <span class="hidden font-medium lg:block">{{ __('shop.top_tagline') }}</span>
        <x-shop.language-switcher />
    </div>
</div>

<header class="sticky top-0 z-30 flex items-center gap-3 bg-white/95 py-3 backdrop-blur lg:gap-8 lg:py-4">
    <button data-act="menu" class="grid size-10 place-items-center rounded-xl bg-brand/10 text-brand transition hover:bg-brand hover:text-white active:scale-95 lg:hidden" aria-label="Open menu">
        <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h16"/><path d="M4 12h10"/><path d="M4 17h16"/></svg>
    </button>
    <a href="/" class="shrink-0 leading-none" aria-label="ChinaBay home">
        <x-shop.logo class="h-11 w-auto sm:h-12" />
        <span class="hidden text-[10px] text-slate-500 sm:block">Quality Meets Connection</span>
    </a>
    <div class="relative hidden flex-1 md:block" id="searchWrap" x-data="{ searchMode: localStorage.getItem('cb_search_mode') || 'manual' }">
        <form action="/shop" method="GET" data-cb-search @submit="if (searchMode === 'ai') { $event.preventDefault(); $dispatch('ai-search-open', { q: $event.target.q.value }); }" class="flex items-center gap-1 rounded-full border border-slate-200 bg-slate-50 py-1 pe-1 ps-2 focus-within:border-brand" role="search">
            <div class="flex shrink-0 items-center rounded-full bg-slate-200/70 p-0.5 text-[11px] font-semibold" role="tablist" aria-label="Search mode">
                <button type="button" role="tab" data-mode-btn="manual" :aria-selected="searchMode === 'manual'" @click="searchMode = 'manual'; localStorage.setItem('cb_search_mode', 'manual')" class="rounded-full px-2.5 py-1 transition" :class="searchMode === 'manual' ? 'bg-white text-ink shadow' : 'text-slate-500 hover:text-ink'">Search</button>
                <button type="button" role="tab" data-mode-btn="ai" :aria-selected="searchMode === 'ai'" @click="searchMode = 'ai'; localStorage.setItem('cb_search_mode', 'ai')" class="rounded-full px-2.5 py-1 transition" :class="searchMode === 'ai' ? 'bg-white text-brand shadow' : 'text-slate-500 hover:text-brand'">✨ AI</button>
            </div>
            <input name="q" type="search" autocomplete="off" :placeholder="searchMode === 'ai' ? 'Ask Bay AI anything…' : '{{ __('shop.search_placeholder') }}'" class="min-w-0 flex-1 bg-transparent px-3 py-1.5 text-sm outline-none" aria-label="Search products" value="{{ request('q') }}">
            <button type="button" data-voice aria-label="{{ __('shop.voice_search') }}" title="{{ __('shop.voice_search') }}" class="grid size-9 shrink-0 place-items-center rounded-full text-slate-400 transition hover:bg-brand/10 hover:text-brand">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2.5a3 3 0 0 0-3 3v6a3 3 0 0 0 6 0v-6a3 3 0 0 0-3-3Z" /><path d="M19 10.5v.5a7 7 0 0 1-14 0v-.5M12 18v3.5M8.5 21.5h7" /></svg>
            </button>
            <button class="grid size-9 shrink-0 place-items-center rounded-full bg-brand text-white hover:bg-brand-dark" aria-label="Search">
                <svg x-show="searchMode === 'manual'" class="size-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
                <svg x-show="searchMode === 'ai'" x-cloak class="size-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 3v3M12 18v3M3 12h3M18 12h3M5.6 5.6l2.1 2.1M16.3 16.3l2.1 2.1M5.6 18.4l2.1-2.1M16.3 7.7l2.1-2.1"/><circle cx="12" cy="12" r="3.2"/></svg>
            </button>
        </form>
    </div>
    <div class="ms-auto flex items-center gap-4 text-xs sm:gap-6 md:ms-0">
        <button @click="mobileSearch = !mobileSearch" class="grid size-10 place-items-center rounded-xl bg-slate-100 md:hidden" aria-label="Search">
            <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
        </button>
        <a href="/account" class="hidden items-center gap-2 sm:flex">
            <svg class="size-6 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" /></svg>
            <span class="hidden xl:block"><b class="block text-[13px]">{{ __('shop.account') }}</b><span class="text-slate-500">{{ __('shop.login_register') }}</span></span>
        </a>
        <button data-act="wishlist" class="relative flex items-center gap-2 text-brand" aria-label="Wishlist">
            <svg class="size-6 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733-.715-1.607-2.377-2.733-4.313-2.733C5.1 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12Z" /></svg>
            <span id="wCount" class="absolute -end-2 -top-1 grid size-4 place-items-center rounded-full bg-brand text-[9px] text-white sm:end-auto sm:start-4">0</span>
            <b class="hidden xl:block">{{ __('shop.wishlist') }}</b>
        </button>
        <a href="/cart" data-act="cart" class="relative flex items-center gap-2" aria-label="Cart">
            <svg class="size-6 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" /></svg>
            <span id="cCount" class="absolute -end-2 -top-1 grid size-4 place-items-center rounded-full bg-brand text-[9px] text-white transition sm:end-auto sm:start-4">0</span>
            <b class="hidden xl:block">{{ __('shop.cart') }}</b>
        </a>
    </div>
</header>

<form action="/shop" method="GET" data-cb-search x-show="mobileSearch" x-cloak x-data="{ searchMode: localStorage.getItem('cb_search_mode') || 'manual' }" @submit="if (searchMode === 'ai') { $event.preventDefault(); $dispatch('ai-search-open', { q: $event.target.q.value }); }" class="mb-3 md:hidden" role="search">
    <div class="flex items-center gap-1 rounded-full border border-slate-200 bg-slate-50 py-1 pe-1 ps-2 focus-within:border-brand">
        <div class="flex shrink-0 items-center rounded-full bg-slate-200/70 p-0.5 text-[11px] font-semibold" role="tablist" aria-label="Search mode">
            <button type="button" role="tab" data-mode-btn="manual" :aria-selected="searchMode === 'manual'" @click="searchMode = 'manual'; localStorage.setItem('cb_search_mode', 'manual')" class="rounded-full px-2.5 py-1 transition" :class="searchMode === 'manual' ? 'bg-white text-ink shadow' : 'text-slate-500'">Search</button>
            <button type="button" role="tab" data-mode-btn="ai" :aria-selected="searchMode === 'ai'" @click="searchMode = 'ai'; localStorage.setItem('cb_search_mode', 'ai')" class="rounded-full px-2.5 py-1 transition" :class="searchMode === 'ai' ? 'bg-white text-brand shadow' : 'text-slate-500'">✨ AI</button>
        </div>
        <input name="q" type="search" :placeholder="searchMode === 'ai' ? 'Ask Bay AI anything…' : '{{ __('shop.search_placeholder') }}'" class="min-w-0 flex-1 bg-transparent px-3 py-2 text-sm outline-none" aria-label="Search products">
        <button type="button" data-voice aria-label="{{ __('shop.voice_search') }}" title="{{ __('shop.voice_search') }}" class="grid size-9 shrink-0 place-items-center rounded-full text-slate-400 transition hover:bg-brand/10 hover:text-brand">
            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2.5a3 3 0 0 0-3 3v6a3 3 0 0 0 6 0v-6a3 3 0 0 0-3-3Z" /><path d="M19 10.5v.5a7 7 0 0 1-14 0v-.5M12 18v3.5M8.5 21.5h7" /></svg>
        </button>
        <button class="grid size-9 shrink-0 place-items-center rounded-full bg-brand text-white hover:bg-brand-dark" aria-label="Search">
            <svg x-show="searchMode === 'manual'" class="size-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
            <svg x-show="searchMode === 'ai'" x-cloak class="size-4" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M12 3v3M12 18v3M3 12h3M18 12h3M5.6 5.6l2.1 2.1M16.3 16.3l2.1 2.1M5.6 18.4l2.1-2.1M16.3 7.7l2.1-2.1"/><circle cx="12" cy="12" r="3.2"/></svg>
        </button>
    </div>
</form>

<nav class="hidden items-center gap-8 border-y border-slate-100 bg-slate-50/60 pe-4 text-[13px] font-medium lg:flex" aria-label="Main">
    <div class="group relative">
        <button class="btn !rounded-md px-5 py-2.5 text-sm">
            <svg class="size-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M4 7h16" /><path d="M4 12h16" /><path d="M4 17h16" /></svg>
            <span>{{ __('shop.all_categories') }}</span>
            <svg class="size-4 shrink-0 transition-transform duration-200 group-hover:rotate-180" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6" /></svg>
        </button>
        <ul class="invisible absolute start-0 top-full z-40 w-60 rounded-lg border border-slate-100 bg-white py-2 opacity-0 shadow-xl transition group-hover:visible group-hover:opacity-100 group-focus-within:visible group-focus-within:opacity-100">
            @foreach ($cats as $c)
                <li><a href="/shop?cat={{ $c['slug'] }}" class="flex w-full items-center gap-3 px-4 py-2 text-start hover:bg-slate-50 hover:text-brand">{{ $c['emoji'] }} {{ $c['en'] }}</a></li>
            @endforeach
        </ul>
    </div>
    <div class="flex gap-7">
        @foreach ($links as $i => [$t, $h])
            <a href="{{ $h }}" class="py-3 {{ request()->is(ltrim($h, '/')) || ($h === '/' && request()->is('/')) ? 'border-b-2 border-brand text-brand' : 'hover:text-brand' }}">{{ $t }}</a>
        @endforeach
    </div>
    <a href="/flash-sale" class="ms-auto rounded-full bg-brand/10 px-3 py-1 text-xs font-bold text-brand">⚡ {{ __('shop.flash_sale') }}</a>
</nav>
