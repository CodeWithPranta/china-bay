@extends('layouts.app')
@section('title', 'My Account — ChinaBay')
@php use App\Support\ShopDemo; $recs = array_slice(ShopDemo::products(), 0, 4); $map = collect(ShopDemo::products())->keyBy('id'); @endphp
@section('content')
<div class="flex flex-col gap-4 py-6 lg:flex-row">
    <x-account.sidebar />
    <div class="min-w-0 flex-1 space-y-4">
        <div class="card flex flex-wrap items-center justify-between gap-3">
            <div><h1 class="text-xl font-extrabold">আসসালামু আলাইকুম, Rahim 👋</h1><p class="text-xs text-slate-500">Gold member · 120 points · 3 active orders</p></div>
            <a href="/shop" class="btn px-4 py-2.5 text-xs">Continue Shopping →</a>
        </div>
        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
            @foreach ([['📦','12','Orders'],['♡','8','Wishlist'],['🪙','120','Points'],['🎟','3','Coupons']] as [$e,$n,$t])
                <div class="card text-center"><span class="text-2xl">{{ $e }}</span><b class="block text-xl">{{ $n }}</b><span class="text-xs text-slate-500">{{ $t }}</span></div>
            @endforeach
        </div>
        <div class="card">
            <div class="mb-3 flex items-center justify-between"><h2 class="font-bold">Recent Orders</h2><a href="/account/orders" class="text-xs font-semibold text-brand">View all →</a></div>
            <div class="overflow-x-auto"><table class="w-full min-w-[520px] text-xs">
                <thead><tr class="text-left text-slate-400"><th class="py-2">Order</th><th>Date</th><th>Total</th><th>Status</th></tr></thead>
                <tbody>
                    @foreach ([['CB-1001','08 Oct 2026','৳ 4,618','Shipped','bg-sky-100 text-sky-700'],['CB-1002','02 Oct 2026','৳ 1,999','Processing','bg-amber-100 text-amber-700'],['CB-1003','24 Sep 2026','৳ 2,649','Delivered','bg-emerald-100 text-emerald-700']] as [$id,$d,$t,$s,$c])
                        <tr class="border-t border-slate-100"><td class="py-2.5 font-semibold"><a href="/account/orders/CB-1001" class="hover:text-brand">{{ $id }}</a></td><td>{{ $d }}</td><td>{{ $t }}</td><td><span class="rounded-full px-2 py-0.5 font-semibold {{ $c }}">{{ $s }}</span></td></tr>
                    @endforeach
                </tbody>
            </table></div>
        </div>
        <div class="card">
            <div class="flex justify-between text-xs font-semibold"><span>🪙 Loyalty — Gold tier</span><span>120 / 200 pts</span></div>
            <div class="mt-2 h-2.5 overflow-hidden rounded-full bg-slate-100"><div class="h-full w-3/5 rounded-full bg-brand"></div></div>
            <p class="mt-1.5 text-[11px] text-slate-500">80 pts more to reach <b>Platinum</b> · <a href="/account/loyalty" class="text-brand">View rewards →</a></p>
        </div>
        <div>
            <h2 class="mb-2 font-bold">Recommended for you ✨</h2>
            <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                @foreach ($recs as $p)<x-shop.product-card :product="$p" />@endforeach
            </div>
        </div>
    </div>
</div>
@push('scripts')<script>window.CB_PRODUCTS = @json($map);</script>@endpush
@endsection
