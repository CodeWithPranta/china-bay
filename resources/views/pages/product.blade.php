@extends('layouts.app')
@section('title', ($product['name'] ?? 'Product') . ' – ChinaBay')
@php
    use App\Support\ShopDemo;
    $all = ShopDemo::products();
    $p = $product ?? $all[0];
    $related = array_values(array_filter($all, fn($x) => $x['cat'] === $p['cat'] && $x['id'] !== $p['id']));
    $related = array_slice(count($related) ? $related : $all, 0, 4);
    $inStock = ($p['stock'] ?? 0) > 0;
@endphp

@section('content')
<nav class="mt-3 text-xs text-slate-500" aria-label="Breadcrumb"><a href="/" class="hover:text-brand">Home</a> / <a href="/shop" class="hover:text-brand">Shop</a> / <a href="/shop?cat={{ $p['cat'] }}" class="hover:text-brand">{{ ucfirst($p['cat']) }}</a> / <span class="text-ink font-semibold">{{ $p['name'] }}</span></nav>

<div class="mt-3 grid gap-6 pb-8 md:grid-cols-2" x-data="{ thumb: 0, qty: 1, tab: 'desc', size: 'M', color: 'Black' }">
    {{-- Gallery --}}
    @php $thumbs = [$p['emoji'], '📦', '✨', '🔍']; @endphp
    <div>
        <div class="grid h-72 place-items-center rounded-lg border border-slate-100 bg-slate-50 text-[7rem] sm:h-96" aria-label="{{ $p['name'] }}">
            <span x-text="['{{ $p['emoji'] }}','📦','✨','🔍'][thumb]">{{ $p['emoji'] }}</span>
        </div>
        <div class="mt-2 grid grid-cols-4 gap-2">
            @foreach ($thumbs as $i => $t)
                <button @click="thumb = {{ $i }}" :class="thumb === {{ $i }} ? 'border-brand ring-2 ring-brand/20' : 'border-slate-200'" class="grid h-16 place-items-center rounded-lg border text-3xl" aria-label="View {{ $i + 1 }}">{{ $t }}</button>
            @endforeach
        </div>
    </div>

    {{-- Info --}}
    <div>
        <h1 class="text-2xl font-extrabold">{{ $p['name'] }} <span class="block text-sm font-normal text-slate-500">{{ $p['sub'] }}</span></h1>
        <p class="mt-1 text-xs text-amber-400">★★★★★ <span class="text-slate-500">{{ $p['rating'] }} · {{ $p['reviews'] }} reviews · রিভিউ</span></p>
        <p class="mt-2"><s class="text-sm text-slate-400">৳ {{ number_format($p['old']) }}</s> <b class="text-2xl text-brand">৳ {{ number_format($p['price']) }}</b> <span class="badge-off">-{{ $p['off'] }}%</span></p>
        <p class="mt-2">{!! $inStock ? '<span class="rounded-full bg-emerald-50 px-2 py-1 text-[11px] font-bold text-emerald-600">✓ In Stock (' . $p['stock'] . ' pcs)</span>' : '<span class="rounded-full bg-red-50 px-2 py-1 text-[11px] font-bold text-brand">Out of stock</span>' !!}</p>
        <p class="mt-3 text-sm text-slate-600">অরিজিনাল চায়না ইমপোর্টেড {{ $p['name'] }} — {{ $p['sub'] }}। কোয়ালিটি চেক করা, সারা বাংলাদেশে ক্যাশ অন ডেলিভারি। <span class="text-slate-500">Genuine import, quality checked before dispatch.</span></p>

        <div class="mt-3 text-xs">
            <b>Size · সাইজ:</b>
            <div class="mt-1 flex gap-2">
                @foreach (['S', 'M', 'L', 'XL'] as $s)
                    <button @click="size = '{{ $s }}'" :class="size === '{{ $s }}' ? 'border-brand bg-brand/5 text-brand' : 'border-slate-200'" class="rounded-md border px-3 py-1.5 font-semibold">{{ $s }}</button>
                @endforeach
            </div>
            <b class="mt-3 block">Color · কালার:</b>
            <div class="mt-1 flex gap-2">
                @foreach (['Black', 'White', 'Blue'] as $c)
                    <button @click="color = '{{ $c }}'" :class="color === '{{ $c }}' ? 'border-brand bg-brand/5 text-brand' : 'border-slate-200'" class="rounded-md border px-3 py-1.5 font-semibold">{{ $c }}</button>
                @endforeach
            </div>
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-2">
            <div class="inline-flex items-center rounded-md border border-slate-200">
                <button @click="qty = Math.max(1, qty - 1)" class="px-3 py-2" aria-label="Decrease">−</button>
                <span x-text="qty" class="w-8 text-center font-bold">1</span>
                <button @click="qty = Math.min({{ $p['stock'] }}, qty + 1)" class="px-3 py-2" aria-label="Increase">+</button>
            </div>
            <button data-add="{{ $p['id'] }}" class="btn flex-1 px-5 py-2.5 text-sm">🛒 Add to Cart</button>
            <button data-wish="{{ $p['id'] }}" class="btn-outline px-4 py-2.5 text-sm" aria-label="Wishlist">♡</button>
        </div>
        <a href="/checkout" class="btn mt-2 w-full py-3 text-sm">Buy Now · এখনই কিনুন →</a>

        <ul class="mt-4 grid grid-cols-2 gap-2 text-[11px] text-slate-600">
            <li class="rounded bg-slate-50 px-2 py-1.5">💵 COD available · ক্যাশ অন ডেলিভারি</li>
            <li class="rounded bg-slate-50 px-2 py-1.5">📱 bKash / Nagad accepted</li>
            <li class="rounded bg-slate-50 px-2 py-1.5">🚚 Delivery 2–5 days nationwide</li>
            <li class="rounded bg-slate-50 px-2 py-1.5">↩️ 7-day easy return</li>
        </ul>
        <div class="card mt-3 flex items-center gap-3 !bg-brand/5 text-xs">
            <span class="text-2xl">📞</span>
            <div><b>ফোনে অর্ডার করুন: <a href="tel:+8801849415277" class="text-brand">+8801849415277</a></b><p class="text-slate-500">সকাল ৯টা – রাত ১০টা (প্রতিদিন)</p></div>
        </div>
    </div>
</div>

{{-- Tabs --}}
<div class="pb-8" x-data="{ tab: 'desc' }">
    <div class="flex gap-1 border-b border-slate-200 text-sm font-semibold" role="tablist">
        <button @click="tab = 'desc'" :class="tab === 'desc' ? 'border-brand text-brand' : 'border-transparent text-slate-500'" class="border-b-2 px-4 py-2">Description</button>
        <button @click="tab = 'spec'" :class="tab === 'spec' ? 'border-brand text-brand' : 'border-transparent text-slate-500'" class="border-b-2 px-4 py-2">Specification</button>
        <button @click="tab = 'rev'" :class="tab === 'rev' ? 'border-brand text-brand' : 'border-transparent text-slate-500'" class="border-b-2 px-4 py-2">Reviews ({{ $p['reviews'] }})</button>
    </div>
    <div x-show="tab === 'desc'" class="py-4 text-sm text-slate-600"><p>{{ $p['name'] }} ({{ $p['sub'] }}) — সরাসরি চায়না থেকে আমদানি করা অরিজিনাল পণ্য। ডেলিভারির আগে প্রতিটি ইউনিট কোয়ালিটি চেক করা হয়। সারা দেশে হোম ডেলিভারি, পণ্য হাতে পেয়ে মূল্য পরিশোধ।</p></div>
    <div x-show="tab === 'spec'" x-cloak class="py-4 text-sm"><table class="w-full max-w-lg text-xs"><tr class="border-b border-slate-100"><td class="py-2 text-slate-500">Category</td><td class="font-semibold">{{ ucfirst($p['cat']) }}</td></tr><tr class="border-b border-slate-100"><td class="py-2 text-slate-500">Variant</td><td class="font-semibold">{{ $p['sub'] }}</td></tr><tr class="border-b border-slate-100"><td class="py-2 text-slate-500">Rating</td><td class="font-semibold">{{ $p['rating'] }} / 5 ({{ $p['reviews'] }} reviews)</td></tr><tr><td class="py-2 text-slate-500">Origin</td><td class="font-semibold">China · চায়না ইমপোর্ট</td></tr></table></div>
    <div x-show="tab === 'rev'" x-cloak class="space-y-2 py-4 text-sm">
        <div class="card"><b class="text-xs">রহিম U. ★★★★★</b><p class="mt-1 text-xs text-slate-600">খুব ভালো কোয়ালিটি, ডেলিভারি দ্রুত পেয়েছি। Highly recommended!</p></div>
        <div class="card"><b class="text-xs">Nusrat J. ★★★★☆</b><p class="mt-1 text-xs text-slate-600">Good product for the price. Packaging could be better.</p></div>
    </div>
</div>

<section class="pb-10">
    <x-shop.section-head title="Related <span>Products</span>" link="/shop" />
    <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-4">
        @foreach ($related as $r)
            <x-shop.product-card :product="$r" />
        @endforeach
    </div>
</section>
@endsection

@push('scripts')
<script>
window.CB_PRODUCTS = @json(collect($all)->mapWithKeys(fn($x) => [$x['id'] => $x]));
try {
    const r = JSON.parse(localStorage.getItem('cb_recent') || '[]');
    const id = {{ $p['id'] }};
    localStorage.setItem('cb_recent', JSON.stringify([id, ...r.filter((x) => x !== id)].slice(0, 12)));
} catch { /* ignore */ }
</script>
@endpush
