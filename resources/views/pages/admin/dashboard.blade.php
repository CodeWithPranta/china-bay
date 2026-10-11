@extends('layouts.admin')
@section('title', 'Dashboard — ChinaBay Admin')
@section('heading', 'Dashboard')
@section('content')
@php
    $products = \App\Support\Catalog::products();
    usort($products, fn($a,$b) => $a['stock'] <=> $b['stock']);
    $low = array_slice($products, 0, 5);
    usort($products, fn($a,$b) => $b['reviews'] <=> $a['reviews']);
    $best = array_slice($products, 0, 4);
    $bars = [40,65,52,80,58,92,70];
    $days = ['Sat','Sun','Mon','Tue','Wed','Thu','Today'];
    $orders = [
        ['CB-1006','Rahim Uddin','Dhaka',2,'৳3,998','COD','Pending'],
        ['CB-1005','Nusrat Jahan','Chattogram',1,'৳1,999','bKash','Confirmed'],
        ['CB-1004','Tanvir Ahmed','Sylhet',3,'৳5,197','COD','Shipped'],
        ['CB-1003','Mim Akter','Dhaka',1,'৳899','Nagad','Delivered'],
        ['CB-1002','Arif Hossain','Khulna',2,'৳4,398','COD','Pending'],
        ['CB-1001','Sadia Rahman','Rajshahi',1,'৳2,999','bKash','Delivered'],
    ];
@endphp
<div class="grid grid-cols-2 gap-3 xl:grid-cols-4">
    @foreach ([['Today sales','৳48,200','+12% today','bg-brand'],['Orders','36','8 pending','bg-navy'],['Revenue (month)','৳6,42,500','+18% MoM','bg-emerald-600'],['Low stock','5','reorder now','bg-amber-500']] as [$l,$v,$s,$c])
    <div class="card flex items-center gap-3">
        <span class="grid size-11 shrink-0 place-items-center rounded-lg {{ $c }} text-lg text-white">{{ $loop->index === 0 ? '৳' : ['🧾','💰','⚠'][$loop->index-1] }}</span>
        <div><p class="text-[11px] text-slate-500">{{ $l }}</p><p class="text-xl font-extrabold">{{ $v }}</p><p class="text-[11px] font-semibold text-emerald-600">{{ $s }}</p></div>
    </div>
    @endforeach
</div>

<div class="mt-4 grid gap-4 lg:grid-cols-3">
    <div class="card lg:col-span-2">
        <div class="mb-2 flex items-center justify-between"><h3 class="font-bold">Sales — last 7 days</h3><span class="text-xs text-slate-500">৳ in thousands</span></div>
        <div class="flex h-40 items-end gap-2">
            @foreach ($bars as $i => $h)
            <div class="flex flex-1 flex-col items-center gap-1">
                <div class="w-full rounded-t-md {{ $i === 6 ? 'bg-brand' : 'bg-navy/85' }}" style="height:{{ $h }}%" title="{{ $h }}"></div>
                <span class="text-[10px] text-slate-500">{{ $days[$i] }}</span>
            </div>
            @endforeach
        </div>
    </div>
    <div class="card">
        <h3 class="font-bold">Quick actions</h3>
        <div class="mt-3 grid grid-cols-2 gap-2 text-sm font-semibold">
            <a href="/admin/products" class="btn px-3 py-2 text-xs">+ Product</a>
            <a href="/admin/flash-sales" class="btn-outline px-3 py-2 text-xs">⚡ Flash sale</a>
            <a href="/admin/coupons" class="btn-outline px-3 py-2 text-xs">🎟 Coupon</a>
            <a href="/admin/stock" class="btn-outline px-3 py-2 text-xs">📊 Stock</a>
        </div>
        <div class="mt-3 rounded-lg bg-slate-50 p-3 text-xs text-slate-600">COD due today: <b>৳21,400</b> · bKash received: <b>৳26,800</b></div>
    </div>
</div>

<div class="mt-4 grid gap-4 lg:grid-cols-3">
    <div class="card overflow-x-auto lg:col-span-2">
        <div class="mb-2 flex items-center justify-between"><h3 class="font-bold">Today's / pending orders</h3><a href="/admin/orders" class="text-xs font-bold text-brand">View all →</a></div>
        <table class="w-full min-w-[560px] text-left text-sm">
            <thead class="text-xs text-slate-500"><tr><th class="py-2">Order</th><th>Customer</th><th>Total</th><th>Status</th></tr></thead>
            <tbody>
            @foreach ($orders as [$id,$c,$d,$n,$t,$pay,$s])
                <tr class="border-t border-slate-100">
                    <td class="py-2 font-bold"><a href="/admin/orders/CB-1001" class="text-brand hover:underline">{{ $id }}</a><span class="block text-[11px] font-normal text-slate-500">{{ $d }} · {{ $pay }}</span></td>
                    <td>{{ $c }}</td><td class="font-bold">{{ $t }}</td>
                    <td><span class="rounded-full px-2 py-0.5 text-[11px] font-bold {{ $s==='Pending'?'bg-amber-100 text-amber-700':($s==='Delivered'?'bg-emerald-100 text-emerald-700':'bg-sky-100 text-sky-700') }}">{{ $s }}</span></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="space-y-4">
        <div class="card">
            <h3 class="font-bold">⚠ Low stock ({{ count($low) }})</h3>
            <ul class="mt-2 space-y-2 text-sm">
                @foreach ($low as $p)<li class="flex items-center gap-2"><x-shop.product-thumb :product="$p" class="size-9" /><span class="min-w-0 flex-1 truncate">{{ $p['name'] }}</span><span class="rounded-full bg-red-100 px-2 py-0.5 text-[11px] font-bold text-brand">{{ $p['stock'] }} left</span></li>@endforeach
            </ul>
            <a href="/admin/stock" class="mt-2 inline-block text-xs font-bold text-brand">Manage stock →</a>
        </div>
        <div class="card">
            <h3 class="font-bold">★ Best sellers</h3>
            <ul class="mt-2 space-y-2 text-sm">
                @foreach ($best as $p)<li class="flex items-center gap-2"><x-shop.product-thumb :product="$p" class="size-9" /><span class="min-w-0 flex-1 truncate">{{ $p['name'] }}</span><span class="text-xs text-slate-500">★{{ $p['rating'] }} ({{ $p['reviews'] }})</span></li>@endforeach
            </ul>
        </div>
    </div>
</div>
@endsection
