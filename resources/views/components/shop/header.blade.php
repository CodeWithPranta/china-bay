@props(['cartCount' => 0, 'wishCount' => 0])
@php
    use App\Support\ShopDemo;
    $cats = ShopDemo::categories();
    $links = [
        ['Home', '/'],
        ['Shop', '/shop'],
        ['New Arrivals', '/shop?sort=new'],
        ['Deals', '/flash-sale'],
        ['Track Order', '/track-order'],
        ['About Us', '/about'],
        ['Contact', '/contact'],
    ];
@endphp

<div class="flex items-center justify-between gap-4 rounded-t-sm bg-brand px-4 py-2 text-[11px] text-white">
    <div class="flex flex-wrap gap-x-6 gap-y-1">
        <span>🚚 Fast &amp; Reliable Delivery</span>
        <span class="hidden sm:inline">🛡️ Secure Packaging</span>
        <span class="hidden md:inline">🌐 Worldwide Coverage</span>
    </div>
    <span class="hidden font-medium lg:block">বিশ্বের সেরা পণ্য এখন আপনার হাতের নাগালে ❤️</span>
</div>

<header class="sticky top-0 z-30 flex items-center gap-3 bg-white/95 py-3 backdrop-blur lg:gap-8 lg:py-4">
    <button data-act="menu" class="grid size-10 place-items-center rounded-xl bg-brand/10 text-brand transition hover:bg-brand hover:text-white active:scale-95 lg:hidden" aria-label="Open menu">
        <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 7h16"/><path d="M4 12h10"/><path d="M4 17h16"/></svg>
    </button>
    <a href="/" class="shrink-0 leading-none">
        <span class="text-2xl font-extrabold tracking-tight text-brand sm:text-4xl">ChinaBay</span>
        <span class="hidden text-[10px] text-slate-500 sm:block">Quality Meets Connection</span>
    </a>
    <div class="relative hidden flex-1 md:block" id="searchWrap">
        <form action="/shop" method="GET" class="flex overflow-hidden rounded-full border border-slate-200 bg-slate-50 focus-within:border-brand" role="search">
            <input name="q" type="search" autocomplete="off" placeholder="Search for products..." class="min-w-0 flex-1 bg-transparent px-5 py-2.5 text-sm outline-none" aria-label="Search products" value="{{ request('q') }}">
            <button class="grid w-14 place-items-center bg-brand text-white hover:bg-brand-dark" aria-label="Search">
                <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
            </button>
        </form>
    </div>
    <div class="ml-auto flex items-center gap-4 text-xs sm:gap-6 md:ml-0">
        <button @click="mobileSearch = !mobileSearch" class="grid size-10 place-items-center rounded-xl bg-slate-100 md:hidden" aria-label="Search">
            <svg class="size-5" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" viewBox="0 0 24 24"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>
        </button>
        <a href="/account" class="hidden items-center gap-2 sm:flex">
            <span class="text-2xl">👤</span>
            <span class="hidden xl:block"><b class="block text-[13px]">Account</b><span class="text-slate-500">Login / Register</span></span>
        </a>
        <button data-act="wishlist" class="relative flex items-center gap-2" aria-label="Wishlist">
            <span class="text-2xl text-brand">♡</span>
            <span id="wCount" class="absolute -right-2 -top-1 grid size-4 place-items-center rounded-full bg-brand text-[9px] text-white sm:left-4 sm:right-auto">0</span>
            <b class="hidden xl:block">Wishlist</b>
        </button>
        <a href="/cart" data-act="cart" class="relative flex items-center gap-2" aria-label="Cart">
            <span class="text-2xl">🛒</span>
            <span id="cCount" class="absolute -right-2 -top-1 grid size-4 place-items-center rounded-full bg-brand text-[9px] text-white transition sm:left-4 sm:right-auto">0</span>
            <b class="hidden xl:block">Cart</b>
        </a>
    </div>
</header>

<form action="/shop" method="GET" x-show="mobileSearch" x-cloak class="mb-3 md:hidden">
    <input name="q" type="search" placeholder="Search for products..." class="w-full rounded-full border border-slate-200 bg-slate-50 px-5 py-2.5 text-sm outline-none focus:border-brand" aria-label="Search products">
</form>

<nav class="hidden items-center gap-8 border-y border-slate-100 bg-slate-50/60 pr-4 text-[13px] font-medium lg:flex" aria-label="Main">
    <div class="group relative">
        <button class="btn !rounded-md px-5 py-2.5 text-sm">☰ All Categories ⌄</button>
        <ul class="invisible absolute left-0 top-full z-40 w-60 rounded-lg border border-slate-100 bg-white py-2 opacity-0 shadow-xl transition group-hover:visible group-hover:opacity-100 group-focus-within:visible group-focus-within:opacity-100">
            @foreach ($cats as $c)
                <li><a href="/shop?cat={{ $c['slug'] }}" class="flex w-full items-center gap-3 px-4 py-2 text-left hover:bg-slate-50 hover:text-brand">{{ $c['emoji'] }} {{ $c['en'] }}</a></li>
            @endforeach
        </ul>
    </div>
    <div class="flex gap-7">
        @foreach ($links as $i => [$t, $h])
            <a href="{{ $h }}" class="py-3 {{ request()->is(ltrim($h, '/')) || ($h === '/' && request()->is('/')) ? 'border-b-2 border-brand text-brand' : 'hover:text-brand' }}">{{ $t }}</a>
        @endforeach
    </div>
    <a href="/flash-sale" class="ml-auto rounded-full bg-brand/10 px-3 py-1 text-xs font-bold text-brand">⚡ Flash Sale</a>
</nav>
