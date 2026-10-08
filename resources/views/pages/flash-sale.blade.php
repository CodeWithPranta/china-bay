@extends('layouts.app')
@section('title', 'Flash Sale – ChinaBay')
@php use App\Support\ShopDemo; $products = array_slice(ShopDemo::products(), 0, 6); @endphp

@section('content')
{{-- Campaign banner --}}
<section class="mt-3 rounded-lg bg-gradient-to-r from-red-700 to-red-500 p-5 text-white sm:p-8">
    <div class="flex flex-wrap items-center gap-4">
        <div class="flex-1">
            <span class="rounded-full bg-white/20 px-3 py-1 text-[11px] font-bold">⚡ FLASH SALE · ফ্ল্যাশ সেল</span>
            <h1 class="mt-2 text-2xl font-extrabold sm:text-3xl">Up to 50% OFF · বিশাল ছাড়!</h1>
            <p class="text-xs text-white/85">Use code <b>FLASH50</b> for extra ৳50 off · সীমিত স্টক!</p>
        </div>
        <div class="flex items-center gap-2 text-center" x-data="flashTimer()" x-init="start()">
            <template x-for="(v, k) in parts" :key="k">
                <div class="min-w-14 rounded-lg bg-white/15 px-2 py-1.5"><b class="block text-xl" x-text="v"></b><span class="text-[10px]" x-text="k"></span></div>
            </template>
        </div>
    </div>
</section>

{{-- Coupon strip --}}
<div class="mt-3 flex flex-wrap gap-2 text-xs">
    <span class="rounded-full bg-brand/10 px-3 py-1.5 font-bold text-brand">🎟 CHINABAY10 = 10% OFF</span>
    <span class="rounded-full bg-brand/10 px-3 py-1.5 font-bold text-brand">⚡ FLASH50 = ৳50 OFF</span>
    <span class="rounded-full bg-slate-100 px-3 py-1.5 text-slate-500">COD · bKash · Nagad available</span>
</div>

<section class="pb-10">
    <x-shop.section-head title="Today's <span>Deals</span>" link="/shop" />
    <div class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-6">
        @foreach ($products as $i => $p)
            @php $sold = [82, 65, 74, 58, 45, 90][$i % 6]; @endphp
            <article class="flex flex-col rounded-lg border border-slate-100 p-3 shadow-sm transition hover:shadow-md">
                <div class="relative">
                    <span class="badge-off absolute start-0 top-0">-{{ $p['off'] }}%</span>
                    <button data-wish="{{ $p['id'] }}" aria-label="Toggle wishlist" class="absolute end-0 top-0 text-xl text-slate-300 transition hover:scale-110 hover:text-brand">♡</button>
                    <a href="/product/{{ $p['slug'] }}" class="grid h-28 place-items-center text-6xl" aria-label="{{ $p['name'] }}">{{ $p['emoji'] }}</a>
                </div>
                <h3 class="mt-2 min-h-8 text-[11px] font-semibold leading-snug">
                    <a href="/product/{{ $p['slug'] }}" class="hover:text-brand">{{ $p['name'] }}<br><span class="font-normal text-slate-500">{{ $p['sub'] }}</span></a>
                </h3>
                <p class="mt-1 text-[10px] text-slate-400"><s>৳ {{ number_format($p['old']) }}</s> <b class="text-sm text-brand">৳ {{ number_format($p['price']) }}</b></p>
                <p class="text-[10px] text-amber-400">★★★★★ <span class="text-slate-400">({{ $p['reviews'] }})</span></p>
                <div class="mt-2">
                    <div class="h-1.5 overflow-hidden rounded-full bg-slate-100" role="progressbar" aria-valuenow="{{ $sold }}" aria-valuemin="0" aria-valuemax="100" aria-label="{{ $sold }}% sold"><div class="h-full rounded-full bg-gradient-to-r from-brand to-amber-500" style="width: {{ $sold }}%"></div></div>
                    <p class="mt-1 text-[10px] font-semibold text-brand">{{ $sold }}% sold · {{ $p['stock'] }} left!</p>
                </div>
                <div class="mt-auto pt-2">
                    <button data-add="{{ $p['id'] }}" class="btn w-full py-1.5 text-[11px]">🛒 Add to Cart</button>
                </div>
            </article>
        @endforeach
    </div>
</section>
@endsection

@push('scripts')
<script>
window.CB_PRODUCTS = @json(collect(ShopDemo::products())->mapWithKeys(fn($p) => [$p['id'] => $p]));
function flashTimer() {
    return {
        parts: { hrs: '00', min: '00', sec: '00' },
        start() {
            const tick = () => {
                const now = new Date(); const end = new Date(); end.setHours(23, 59, 59, 999);
                let s = Math.max(0, Math.floor((end - now) / 1000));
                const h = String(Math.floor(s / 3600)).padStart(2, '0');
                const m = String(Math.floor((s % 3600) / 60)).padStart(2, '0');
                const sec = String(s % 60).padStart(2, '0');
                this.parts = { hrs: h, min: m, sec };
            };
            tick(); setInterval(tick, 1000);
        },
    };
}
</script>
@endpush
