@extends('layouts.app')
@section('title', 'ChinaBay – Quality Meets Connection')
@php
    use App\Support\ShopDemo;
    $slides = ShopDemo::slides();
    $cats = ShopDemo::categories();
    $products = ShopDemo::products();
    $featured = array_slice($products, 0, 6);
@endphp

@section('content')
{{-- Hero slider (auto-rotate + dots + swipe; no prev/next arrows) --}}
<section id="hero" aria-roledescription="carousel" aria-label="Promotions" class="relative mt-3 overflow-hidden rounded-lg bg-slate-50">
    <div id="slides" class="relative min-h-[26rem] md:min-h-[22rem]">
        @foreach ($slides as $i => $s)
            <div class="slide bg-gradient-to-r {{ $s['grad'] }} text-white {{ $i === 0 ? 'on' : '' }}" role="group" aria-label="Slide {{ $i + 1 }}">
                <div>
                    <span class="inline-block rounded-full bg-white/20 px-3 py-1 text-[11px] font-semibold">{{ $s['tag'] }}</span>
                    <h1 class="mt-3 text-3xl font-extrabold leading-tight md:text-4xl">{{ $s['h1'] }}<br>{{ $s['h2'] }}</h1>
                    <p class="mt-2 max-w-md text-sm text-white/85">{{ $s['p'] }}</p>
                    <div class="mt-4 flex gap-2">
                        <a href="/shop?cat={{ $s['cat'] }}" class="inline-flex items-center gap-2 rounded-md bg-white px-5 py-2.5 text-sm font-bold text-brand transition hover:bg-slate-100">Shop Now →</a>
                        <a href="/flash-sale" class="inline-flex items-center gap-2 rounded-md border border-white/40 px-5 py-2.5 text-sm font-semibold text-white hover:bg-white/10">Flash Sale ⚡</a>
                    </div>
                </div>
                <div class="hidden place-items-center text-[9rem] md:grid" aria-hidden="true">{{ $s['emoji'] }}</div>
                <div class="grid place-items-center text-7xl md:hidden" aria-hidden="true">{{ $s['emoji'] }}</div>
            </div>
        @endforeach
    </div>
    <div id="dots" class="absolute inset-x-0 bottom-3 flex justify-center gap-1.5">
        @foreach ($slides as $i => $s)
            <button data-dot="{{ $i }}" class="size-2 rounded-full {{ $i === 0 ? 'bg-white' : 'bg-white/50' }}" aria-label="Go to slide {{ $i + 1 }}"></button>
        @endforeach
    </div>
</section>

{{-- Categories --}}
<section class="mt-8">
    <x-shop.section-head title="Shop by <span>Category</span>" link="/shop" />
    <ul class="-mx-3 mt-4 flex snap-x gap-3 overflow-x-auto px-3 pb-2 text-center sm:mx-0 sm:grid sm:grid-cols-4 sm:overflow-visible sm:px-0 lg:grid-cols-8">
        @foreach ($cats as $c)
            <li class="min-w-[92px] snap-start">
                <a href="/shop?cat={{ $c['slug'] }}" class="block rounded-lg border border-slate-100 p-3 shadow-sm transition hover:shadow-md">
                    <span class="grid h-14 place-items-center rounded-md {{ $c['bg'] }} text-3xl">{{ $c['emoji'] }}</span>
                    <b class="mt-2 block text-[11px]">{{ $c['en'] }}</b>
                    <span class="text-[11px] text-slate-500">{{ $c['bn'] }}</span>
                </a>
            </li>
        @endforeach
    </ul>
</section>

{{-- Trust strip --}}
<section class="mt-6 grid grid-cols-1 gap-4 rounded-lg bg-slate-50 px-5 py-4 text-xs sm:grid-cols-2 lg:grid-cols-4">
    <div class="flex items-center gap-3"><span class="text-2xl">🚚</span><div><b class="block">Fast Delivery</b><span class="text-slate-500">সারা দেশে ক্যাশ অন ডেলিভারি</span></div></div>
    <div class="flex items-center gap-3"><span class="text-2xl">🛡️</span><div><b class="block">Secure Packaging</b><span class="text-slate-500">নিরাপদ প্যাকেজিং গ্যারান্টি</span></div></div>
    <div class="flex items-center gap-3"><span class="text-2xl">💵</span><div><b class="block">Cash on Delivery</b><span class="text-slate-500">পণ্য হাতে পেয়ে টাকা দিন</span></div></div>
    <div class="flex items-center gap-3"><span class="text-2xl">↩️</span><div><b class="block">Easy Returns</b><span class="text-slate-500">৭ দিনের রিটার্ন সুবিধা</span></div></div>
</section>

{{-- Featured products --}}
<section class="mt-8" id="featured">
    <div class="flex flex-wrap items-center justify-between gap-2">
        <h2 class="h2">Featured <span>Products</span> <small id="filterTag" class="ml-2 hidden rounded-full bg-brand/10 px-2 py-0.5 text-xs font-semibold text-brand"></small></h2>
        <div class="flex items-center gap-3 text-xs">
            <label class="flex items-center gap-1.5 text-slate-500">Sort
                <select id="sort" class="rounded-md border border-slate-200 bg-white px-2 py-1 text-ink">
                    <option value="">Featured</option>
                    <option value="lo">Price: low to high</option>
                    <option value="hi">Price: high to low</option>
                    <option value="off">Biggest discount</option>
                </select>
            </label>
            <button id="resetFilter" class="font-medium text-brand hover:underline">Reset</button>
        </div>
    </div>
    <div id="grid" class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
        @foreach ($featured as $p)
            <x-shop.product-card :product="$p" />
        @endforeach
    </div>
    <p id="empty" class="hidden py-12 text-center text-slate-500">No products match. <button id="showAll" class="font-semibold text-brand underline">Show all products</button></p>
</section>

{{-- Promo banners --}}
<section class="mt-6 grid gap-4 md:grid-cols-3">
    <a href="/shop?cat=toys" class="flex items-center justify-between rounded-lg bg-gradient-to-r from-pink-100 to-pink-50 p-5 transition hover:shadow-md"><div><b class="text-xl text-brand">কিউট ক্যাট টয়</b><p class="text-[11px]">আপনার ছোট্ট সোনার জন্য</p><span class="btn mt-3 px-4 py-1.5 text-[11px]">Shop Now →</span></div><span class="text-6xl">🐱</span></a>
    <a href="/shop?cat=fashion" class="flex items-center justify-between rounded-lg bg-gradient-to-r from-sky-100 to-amber-50 p-5 transition hover:shadow-md"><div><b class="text-xl text-sky-900">ভ্রমণ কম্বো</b><p class="text-[11px]">ভ্রমণ হোক আরও আরামদায়ক</p><span class="mt-3 inline-block rounded-md bg-sky-700 px-4 py-1.5 text-[11px] font-semibold text-white">Shop Now →</span></div><span class="text-6xl">🎒</span></a>
    <a href="/shop?cat=gadgets" class="flex items-center justify-between rounded-lg bg-gradient-to-r from-red-700 to-red-500 p-5 text-white transition hover:shadow-md"><div><b class="text-xl">সেরা গ্যাজেট</b><p class="text-[11px]">স্মার্ট জীবন, স্মার্ট আপনি</p><span class="mt-3 inline-block rounded-md bg-white px-4 py-1.5 text-[11px] font-semibold text-brand">Shop Now →</span></div><span class="text-6xl">⌚</span></a>
</section>

{{-- Why choose --}}
<section class="mt-8">
    <x-shop.section-head title="Why Choose <span>ChinaBay?</span>" />
    <div class="mt-4 grid grid-cols-1 gap-4 text-xs sm:grid-cols-2 lg:grid-cols-4">
        <div class="card"><span class="text-3xl">🇨🇳</span><b class="mt-2 block text-sm">Direct China Import</b><p class="mt-1 text-slate-500">সরাসরি চায়না থেকে আমদানি — সেরা দামে অরিজিনাল পণ্য।</p></div>
        <div class="card"><span class="text-3xl">📞</span><b class="mt-2 block text-sm">Phone Order Support</b><p class="mt-1 text-slate-500">ফোনে অর্ডার করুন: +8801849415277 (সকাল ৯টা–রাত ১০টা)।</p></div>
        <div class="card"><span class="text-3xl">💳</span><b class="mt-2 block text-sm">bKash / Nagad / COD</b><p class="mt-1 text-slate-500">বিকাশ, নগদ, কার্ড অথবা ক্যাশ অন ডেলিভারি।</p></div>
        <div class="card"><span class="text-3xl">⭐</span><b class="mt-2 block text-sm">Quality Checked</b><p class="mt-1 text-slate-500">প্রতিটি পণ্য ডেলিভারির আগে কোয়ালিটি চেক করা হয়।</p></div>
    </div>
</section>

{{-- Recently viewed placeholder --}}
<section class="mt-8 pb-8">
    <x-shop.section-head title="Recently <span>Viewed</span>" />
    <div id="recentRow" class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
        <p class="col-span-full rounded-lg border border-dashed border-slate-200 py-8 text-center text-xs text-slate-400">আপনি যে পণ্যগুলো দেখবেন সেগুলো এখানে থাকবে · Browse products to fill this row</p>
    </div>
</section>
@endsection

@push('scripts')
<script>
window.CB_PRODUCTS = @json(collect($products)->mapWithKeys(fn($p) => [$p['id'] => $p]));
(function () {
    // Slider: auto-rotate 5s + dots + swipe (arrows removed by design)
    const slides = [...document.querySelectorAll('#slides .slide')];
    const dots = [...document.querySelectorAll('#dots [data-dot]')];
    let cur = 0, timer;
    function go(i) {
        cur = (i + slides.length) % slides.length;
        slides.forEach((s, k) => s.classList.toggle('on', k === cur));
        dots.forEach((d, k) => { d.className = 'size-2 rounded-full ' + (k === cur ? 'bg-white' : 'bg-white/50'); });
        restart();
    }
    function restart() { clearInterval(timer); timer = setInterval(() => go(cur + 1), 5000); }
    dots.forEach((d) => d.addEventListener('click', () => go(+d.dataset.dot)));
    let sx = 0;
    const hero = document.getElementById('slides');
    hero?.addEventListener('touchstart', (e) => { sx = e.touches[0].clientX; }, { passive: true });
    hero?.addEventListener('touchend', (e) => {
        const dx = e.changedTouches[0].clientX - sx;
        if (Math.abs(dx) > 40) { go(cur + (dx < 0 ? 1 : -1)); }
    }, { passive: true });
    restart();

    // Client filter/sort/search-suggest
    const grid = document.getElementById('grid');
    const base = grid ? grid.innerHTML : '';
    const tag = document.getElementById('filterTag');
    const empty = document.getElementById('empty');
    const params = new URLSearchParams(location.search);
    const card = (p) => `<article class="relative flex flex-col rounded-lg border border-slate-100 p-3 shadow-sm transition hover:shadow-md">
        <span class="badge-off absolute start-2 top-2">-${p.off}%</span>
        <button data-wish="${p.id}" aria-label="Toggle wishlist" class="absolute end-2 top-1.5 text-xl text-slate-300 hover:text-brand">♡</button>
        <a href="/product/${p.slug}" class="grid h-28 place-items-center text-6xl">${p.emoji}</a>
        <h3 class="mt-2 text-[11px] font-semibold leading-snug"><a href="/product/${p.slug}" class="hover:text-brand">${p.name}<br><span class="font-normal text-slate-500">${p.sub}</span></a></h3>
        <p class="mt-1 text-[10px] text-slate-400"><s>৳ ${Number(p.old).toLocaleString()}</s> <b class="text-sm text-brand">৳ ${Number(p.price).toLocaleString()}</b></p>
        <p class="text-[10px] text-amber-400">★★★★★ <span class="text-slate-400">(${p.reviews})</span></p>
        <div class="mt-auto flex gap-1.5 pt-2"><button data-add="${p.id}" class="btn w-full py-1.5 text-[11px]">🛒 Add to Cart</button></div></article>`;
    function render(list, label) {
        if (!grid) { return; }
        grid.innerHTML = list.map(card).join('');
        const has = list.length > 0;
        grid.classList.toggle('hidden', !has);
        empty?.classList.toggle('hidden', has);
        if (tag) {
            if (label) { tag.textContent = label; tag.classList.remove('hidden'); }
            else { tag.classList.add('hidden'); }
        }
        window.ChinaBay?.sync?.();
    }
    function applySort(list, mode) {
        const a = [...list];
        if (mode === 'lo') { a.sort((x, y) => x.price - y.price); }
        if (mode === 'hi') { a.sort((x, y) => y.price - x.price); }
        if (mode === 'off') { a.sort((x, y) => y.off - x.off); }
        return a;
    }
    const all = Object.values(window.CB_PRODUCTS);
    document.getElementById('sort')?.addEventListener('change', (e) => render(applySort(all, e.target.value), ''));
    document.getElementById('resetFilter')?.addEventListener('click', () => { render(all.slice(0, 6), ''); document.getElementById('sort').value = ''; });
    document.getElementById('showAll')?.addEventListener('click', () => render(all, ''));
    const cat = params.get('cat');
    if (cat && cat !== 'all') {
        const list = all.filter((p) => p.cat === cat);
        render(list.length ? list : all.slice(0, 6), list.length ? 'Category: ' + cat : '');
    }
    // Recently viewed (localStorage cb_recent ids)
    try {
        const recent = JSON.parse(localStorage.getItem('cb_recent') || '[]').map(Number).filter(Boolean);
        if (recent.length && grid) {
            const row = document.getElementById('recentRow');
            const items = recent.slice(0, 6).map((id) => window.CB_PRODUCTS[id]).filter(Boolean);
            if (items.length && row) { row.innerHTML = items.map(card).join(''); }
        }
    } catch { /* ignore */ }
})();
</script>
@endpush
