@extends('layouts.app')
@section('title', 'Shop – ChinaBay')
@php
    use App\Support\ShopDemo;
    $products = ShopDemo::products();
    $cats = ShopDemo::categories();
    $q = request('q', '');
    $activeCat = request('cat', 'all');
@endphp

@section('content')
<nav class="mt-3 text-xs text-slate-500" aria-label="Breadcrumb"><a href="/" class="hover:text-brand">Home</a> / <span class="text-ink font-semibold">Shop</span></nav>

{{-- Toolbar --}}
<div class="mt-2 flex flex-wrap items-center gap-2">
    <p id="resultCount" class="text-xs text-slate-500">{{ count($products) }} products found · <span id="echoQ">{{ $q ? '“' . $q . '”' : '' }}</span></p>
    <div class="ms-auto flex items-center gap-2 text-xs">
        <label class="flex items-center gap-1.5 text-slate-500">Sort
            <select id="sortSel" class="max-w-36 rounded-md border border-slate-200 bg-white px-2 py-1.5 text-ink sm:max-w-none">
                <option value="">Featured</option>
                <option value="lo" {{ request('sort') === 'lo' ? 'selected' : '' }}>Price: low → high</option>
                <option value="hi" {{ request('sort') === 'hi' ? 'selected' : '' }}>Price: high → low</option>
                <option value="off">Biggest discount</option>
            </select>
        </label>
    </div>
</div>
<div id="pills" class="-mx-3 mt-3 flex snap-x gap-2 overflow-x-auto px-3 pb-1 text-xs [scrollbar-width:none] sm:mx-0 sm:px-0 [&::-webkit-scrollbar]:hidden">
    <button data-pill="all" class="shrink-0 snap-start whitespace-nowrap rounded-full border px-4 py-2 font-medium shadow-sm transition {{ $activeCat === 'all' ? 'border-brand bg-brand font-semibold text-white' : 'border-slate-200 bg-white text-ink hover:border-brand hover:text-brand' }}">All · সব</button>
    @foreach ($cats as $c)
        <button data-pill="{{ $c['slug'] }}" class="shrink-0 snap-start whitespace-nowrap rounded-full border px-4 py-2 shadow-sm transition {{ $activeCat === $c['slug'] ? 'border-brand bg-brand font-semibold text-white' : 'border-slate-200 bg-white font-medium text-ink hover:border-brand hover:text-brand' }}">{{ $c['emoji'] }} {{ $c['en'] }}</button>
    @endforeach
</div>

<div class="mt-4 pb-8 lg:flex lg:gap-5" x-data="{ filtersOpen: false }" @keydown.escape.window="filtersOpen = false">
    {{-- Mobile filter bar --}}
    <div class="mb-3 lg:hidden">
        <button @click="filtersOpen = true" class="inline-flex w-full items-center justify-center gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2.5 text-xs font-semibold shadow-sm transition active:scale-[.98]">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 6h16M7 12h10M10 18h4" /></svg>
            Filters · ফিল্টার
            <span id="filterCount" class="hidden min-w-5 place-items-center rounded-full bg-brand px-1.5 py-0.5 text-[10px] font-bold text-white"></span>
        </button>
    </div>

    {{-- Overlay (mobile only) --}}
    <div x-show="filtersOpen" x-cloak x-transition.opacity.duration.200ms @click="filtersOpen = false" class="fixed inset-0 z-40 bg-black/50 lg:hidden" aria-hidden="true"></div>

    {{-- Sidebar: slide-over drawer on mobile, static column on desktop --}}
    <aside :class="filtersOpen ? 'translate-x-0' : '-translate-x-full'"
        class="fixed inset-y-0 left-0 z-50 w-72 max-w-[85vw] space-y-4 overflow-y-auto bg-white p-4 text-xs shadow-2xl transition-transform duration-300 lg:static lg:z-auto lg:block lg:w-56 lg:shrink-0 lg:translate-x-0 lg:bg-transparent lg:p-0 lg:shadow-none"
        aria-label="Product filters">
        <div class="flex items-center justify-between lg:hidden">
            <b class="text-sm">Filters · ফিল্টার</b>
            <button @click="filtersOpen = false" class="grid size-9 place-items-center rounded-full bg-slate-100 hover:bg-brand hover:text-white" aria-label="Close filters">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18" /></svg>
            </button>
        </div>
        <div class="card !p-3">
            <div class="flex items-center justify-between">
                <b class="text-sm">Category · ক্যাটাগরি</b>
                <button id="clearCats" class="text-[11px] font-medium text-brand hover:underline lg:hidden">Clear</button>
            </div>
            <ul class="mt-2 space-y-1.5">
                @foreach ($cats as $c)
                    <li><label class="flex items-center gap-2 py-0.5"><input type="checkbox" class="fCat size-4 accent-[#c8102e]" value="{{ $c['slug'] }}" {{ $activeCat === $c['slug'] ? 'checked' : '' }}> {{ $c['emoji'] }} {{ $c['en'] }}</label></li>
                @endforeach
            </ul>
        </div>
        <div class="card !p-3">
            <b class="text-sm">Price · দাম (৳)</b>
            <div class="mt-2 space-y-1.5">
                <label class="flex items-center gap-2 py-0.5"><input type="radio" name="pr" class="fPrice size-4 accent-[#c8102e]" value=""> All prices</label>
                <label class="flex items-center gap-2 py-0.5"><input type="radio" name="pr" class="fPrice size-4 accent-[#c8102e]" value="0-1500"> Under ৳1,500</label>
                <label class="flex items-center gap-2 py-0.5"><input type="radio" name="pr" class="fPrice size-4 accent-[#c8102e]" value="1500-3000"> ৳1,500 – ৳3,000</label>
                <label class="flex items-center gap-2 py-0.5"><input type="radio" name="pr" class="fPrice size-4 accent-[#c8102e]" value="3000-99999"> Above ৳3,000</label>
            </div>
        </div>
        <div class="card !p-3">
            <b class="text-sm">Rating · রেটিং</b>
            <div class="mt-2 space-y-1.5">
                <label class="flex items-center gap-2 py-0.5"><input type="radio" name="rt" class="fRate size-4 accent-[#c8102e]" value="0" checked> Any</label>
                <label class="flex items-center gap-2 py-0.5"><input type="radio" name="rt" class="fRate size-4 accent-[#c8102e]" value="4.5"> ★ 4.5+</label>
                <label class="flex items-center gap-2 py-0.5"><input type="radio" name="rt" class="fRate size-4 accent-[#c8102e]" value="4.7"> ★ 4.7+</label>
            </div>
            <label class="mt-2 flex items-center gap-2 py-0.5"><input type="checkbox" id="fAvail" class="size-4 accent-[#c8102e]"> In stock only</label>
        </div>
        <div class="sticky bottom-0 flex gap-2 bg-white pb-1 pt-2 lg:hidden">
            <button id="clearFilters" class="btn-outline flex-1 py-2.5 text-xs">Clear all</button>
            <button @click="filtersOpen = false" class="btn flex-1 py-2.5 text-xs">Show results</button>
        </div>
    </aside>

    {{-- Grid --}}
    <div class="min-w-0 flex-1">
        <div id="shopGrid" class="grid grid-cols-2 gap-3 sm:grid-cols-3 xl:grid-cols-4">
            @foreach ($products as $p)
                <x-shop.product-card :product="$p" />
            @endforeach
        </div>
        <div id="shopEmpty" class="hidden py-16 text-center">
            <p class="text-5xl">🔍</p>
            <b class="mt-2 block">No products found</b>
            <p class="mt-1 text-xs text-slate-500">দুঃখিত, কোনো পণ্য পাওয়া যায়নি। ফিল্টার বদলে আবার চেষ্টা করুন।</p>
            <button id="clearF" class="btn mt-4 px-5 py-2 text-xs">Show all products</button>
        </div>
        <section class="mt-8">
            <x-shop.section-head title="Recently <span>Viewed</span>" />
            <p class="mt-2 rounded-lg border border-dashed border-slate-200 py-6 text-center text-xs text-slate-400">আপনি যে পণ্যগুলো দেখবেন সেগুলো এখানে থাকবে</p>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script>
window.CB_PRODUCTS = @json(collect($products)->mapWithKeys(fn($p) => [$p['id'] => $p]));
(function () {
    const params = new URLSearchParams(location.search);
    const grid = document.getElementById('shopGrid');
    const empty = document.getElementById('shopEmpty');
    const count = document.getElementById('resultCount');
    const all = Object.values(window.CB_PRODUCTS);
    const card = (p) => `<article class="relative flex flex-col rounded-lg border border-slate-100 p-3 shadow-sm transition hover:shadow-md">
        <span class="badge-off absolute start-2 top-2">-${p.off}%</span>
        <button data-wish="${p.id}" class="absolute end-2 top-1.5 text-xl text-slate-300 hover:text-brand">♡</button>
        <a href="/product/${p.slug}" class="grid h-28 place-items-center text-6xl">${p.emoji}</a>
        <h3 class="mt-2 text-[11px] font-semibold leading-snug"><a href="/product/${p.slug}" class="hover:text-brand">${p.name}<br><span class="font-normal text-slate-500">${p.sub}</span></a></h3>
        <p class="mt-1 text-[10px] text-slate-400"><s>৳ ${Number(p.old).toLocaleString()}</s> <b class="text-sm text-brand">৳ ${Number(p.price).toLocaleString()}</b></p>
        <p class="text-[10px] text-amber-400">★★★★★ <span class="text-slate-400">(${p.reviews})</span></p>
        <div class="mt-auto flex gap-1.5 pt-2"><button data-add="${p.id}" class="btn w-full py-1.5 text-[11px]">🛒 Add to Cart</button></div></article>`;
    function current() {
        const cats = [...document.querySelectorAll('.fCat:checked')].map((c) => c.value);
        const pr = document.querySelector('.fPrice:checked')?.value || '';
        const rt = parseFloat(document.querySelector('.fRate:checked')?.value || '0');
        const avail = document.getElementById('fAvail')?.checked;
        let q = (params.get('q') || '').toLowerCase();
        let list = all.filter((p) => {
            if (cats.length && !cats.includes(p.cat) && !cats.includes('all')) { return false; }
            if (q && !(p.name + ' ' + p.sub).toLowerCase().includes(q)) { return false; }
            if (pr) { const [a, b] = pr.split('-').map(Number); if (p.price < a || p.price > b) { return false; } }
            if (p.rating < rt) { return false; }
            if (avail && p.stock < 1) { return false; }
            return true;
        });
        const sort = document.getElementById('sortSel')?.value || params.get('sort') || '';
        if (sort === 'lo') { list.sort((a, b) => a.price - b.price); }
        if (sort === 'hi') { list.sort((a, b) => b.price - a.price); }
        if (sort === 'off') { list.sort((a, b) => b.off - a.off); }
        return list;
    }
    function paint() {
        const list = current();
        grid.innerHTML = list.map(card).join('');
        grid.classList.toggle('hidden', !list.length);
        empty.classList.toggle('hidden', !!list.length);
        count.innerHTML = list.length + ' products found · <span>' + (params.get('q') ? '“' + params.get('q').replace(/</g, '&lt;') + '”' : '') + '</span>';
        const n = document.querySelectorAll('.fCat:checked').length
            + (document.querySelector('.fPrice:checked')?.value ? 1 : 0)
            + (parseFloat(document.querySelector('.fRate:checked')?.value || '0') > 0 ? 1 : 0)
            + (document.getElementById('fAvail')?.checked ? 1 : 0);
        const badge = document.getElementById('filterCount');
        if (badge) {
            badge.textContent = n;
            badge.classList.toggle('hidden', !n);
            badge.classList.toggle('grid', !!n);
        }
        window.ChinaBay?.sync?.();
    }
    function clearAll() {
        document.querySelectorAll('.fCat').forEach((c) => { c.checked = false; });
        document.querySelectorAll('.fPrice').forEach((r, i) => { r.checked = i === 0; });
        document.querySelectorAll('.fRate').forEach((r, i) => { r.checked = i === 0; });
        const avail = document.getElementById('fAvail');
        if (avail) { avail.checked = false; }
        paint();
    }
    document.getElementById('clearFilters')?.addEventListener('click', clearAll);
    document.getElementById('clearCats')?.addEventListener('click', clearAll);
    document.querySelectorAll('.fCat,.fPrice,.fRate,#fAvail').forEach((el) => el.addEventListener('change', paint));
    document.getElementById('sortSel')?.addEventListener('change', paint);
    document.getElementById('clearF')?.addEventListener('click', () => {
        document.querySelectorAll('.fCat').forEach((c) => { c.checked = false; });
        history.replaceState(null, '', '/shop'); params.delete('q'); paint();
    });
    document.querySelectorAll('[data-pill]').forEach((b) => b.addEventListener('click', () => {
        const v = b.dataset.pill;
        document.querySelectorAll('.fCat').forEach((c) => { c.checked = c.value === v; });
        const url = v === 'all' ? '/shop' : '/shop?cat=' + v;
        history.replaceState(null, '', url);
        const base = 'shrink-0 snap-start whitespace-nowrap rounded-full border px-4 py-2 shadow-sm transition ';
        document.querySelectorAll('[data-pill]').forEach((x) => { x.className = base + 'border-slate-200 bg-white font-medium text-ink hover:border-brand hover:text-brand'; });
        b.className = base + 'border-brand bg-brand font-semibold text-white';
        paint();
    }));
    paint();
})();
</script>
@endpush
