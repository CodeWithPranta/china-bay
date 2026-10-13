@extends('layouts.app')
@section('title', 'Order Success – ChinaBay')
@php use App\Support\OrderStatus; @endphp

@section('content')
<section class="mx-auto max-w-2xl py-10 text-center">
    <span class="mx-auto grid size-20 place-items-center rounded-full bg-emerald-50 text-4xl">✅</span>
    <h1 class="h2 mt-4">ধন্যবাদ! Order Placed <span>Successfully</span></h1>
    <p class="mt-2 text-sm text-slate-600">Order ID: <b class="text-brand">{{ $order->code }}</b> · ৳{{ number_format((float) $order->total) }} · Our team will call to confirm shortly.</p>
    <p class="mt-1 text-xs text-slate-500">📩 SMS / Email / WhatsApp confirmation will be sent to your phone. · এসএমএস ও হোয়াটসঅ্যাপে কনফার্মেশন পাবেন।</p>

    {{-- Timeline --}}
    <ol class="mt-6 grid grid-cols-3 gap-2 text-[10px] sm:grid-cols-6">
        @foreach (OrderStatus::steps() as $i => $s)
            <li class="rounded-lg border p-2 {{ $i === 0 ? 'border-emerald-300 bg-emerald-50 font-bold text-emerald-700' : 'border-slate-100 text-slate-400' }}">{{ $s['label'] }}@if ($i === 0) ✅@endif</li>
        @endforeach
    </ol>

    <div class="card mt-6 space-y-2 text-left text-xs">
        <b class="text-sm">Your items ({{ $order->items->sum('qty') }})</b>
        @foreach ($order->items as $item)
            <div class="flex justify-between border-t border-slate-100 py-1.5"><span>{{ $item->name }} × {{ $item->qty }}</span><b>৳{{ number_format((float) $item->unit_price * $item->qty) }}</b></div>
        @endforeach
        <div class="flex justify-between border-t border-slate-100 pt-1.5 font-extrabold"><span>Total (incl. delivery)</span><span>৳{{ number_format((float) $order->total) }}</span></div>
    </div>

    <div class="card mt-4 space-y-2 text-left text-xs">
        <b class="text-sm">What happens next? · পরবর্তী ধাপ</b>
        <p>1️⃣ Our team calls within 30 min – 2 hours to confirm.</p>
        <p>2️⃣ Product quality-checked & packed. · 3️⃣ Courier dispatch + tracking SMS.</p>
    </div>

    <div class="mt-6 flex flex-wrap justify-center gap-2 text-sm">
        <a href="{{ route('track', ['code' => $order->code, 'phone' => $order->phone]) }}" class="btn px-5 py-2.5">Track Order 📦</a>
        <a href="/shop" class="btn-outline px-5 py-2.5">Continue Shopping →</a>
        <a href="https://wa.me/8801849415277" class="btn-outline px-5 py-2.5">💬 WhatsApp Support</a>
    </div>
</section>
<script>try { localStorage.removeItem('cb_cart'); localStorage.removeItem('cb_coupon'); if (window.ChinaBay) { window.ChinaBay.cart = {}; window.ChinaBay.coupon = null; window.ChinaBay.sync(); } } catch {}</script>
@endsection
