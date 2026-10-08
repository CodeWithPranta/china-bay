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
            <div class="relative">
                <x-shop.product-card :product="$p" />
                <div class="absolute inset-x-3 bottom-[52px]">
                    <div class="h-1.5 overflow-hidden rounded-full bg-slate-100"><div class="h-full rounded-full bg-gradient-to-r from-brand to-amber-500" style="width: {{ $sold }}%"></div></div>
                    <p class="mt-0.5 text-[10px] font-semibold text-brand">{{ $sold }}% sold · {{ $p['stock'] }} left!</p>
                </div>
            </div>
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
