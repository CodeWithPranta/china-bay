@extends('layouts.app')
@section('title', 'Order {{ $order->code }} — ChinaBay')
@section('content')
@php use App\Support\OrderStatus; $reached = OrderStatus::reached($order->status); @endphp
<div class="flex flex-col gap-4 py-6 lg:flex-row" x-data="{ ret: false }">
    <x-account.sidebar />
    <div class="min-w-0 flex-1 space-y-4">
        @if (session('status'))
            <p class="rounded-lg bg-emerald-50 px-4 py-2 text-xs font-semibold text-emerald-700">{{ session('status') }}</p>
        @endif
        <div class="card">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div><h1 class="font-mono text-lg font-extrabold">Order {{ $order->code }}</h1><p class="text-xs text-slate-500">Placed {{ $order->created_at->format('d M Y, h:i A') }} · {{ ucfirst($order->payment_method) }} · <span class="rounded-full px-2 py-0.5 font-semibold {{ OrderStatus::badge($order->status) }}">{{ $order->statusLabel() }}</span></p></div>
                <div class="flex gap-2">
                    @if ($order->isCancellable())
                        <form method="POST" action="{{ route('account.orders.cancel', $order->code) }}" onsubmit="return confirm('Cancel this order?');">
                            @csrf
                            <button class="rounded-md border border-red-100 px-3 py-2 text-xs text-red-600 hover:bg-red-50">Cancel</button>
                        </form>
                    @endif
                    <button @click="ret = true" class="btn px-3 py-2 text-xs">Return / Exchange</button>
                </div>
            </div>
            @if ($order->status !== OrderStatus::CANCELLED)
                <ol class="mt-4 grid grid-cols-3 gap-1 text-center text-[10px] font-semibold sm:grid-cols-6">
                    @foreach (OrderStatus::steps() as $s)
                        @php $done = in_array($s['key'], $reached, true); @endphp
                        <li><span class="mx-auto mb-1 grid size-7 place-items-center rounded-full {{ $done ? 'bg-brand text-white' : 'bg-slate-100 text-slate-400' }}">{{ $done ? '✓' : '•' }}</span><span class="{{ $done ? 'text-brand' : 'text-slate-400' }}">{{ $s['label'] }}</span></li>
                    @endforeach
                </ol>
            @else
                <p class="mt-3 rounded-md bg-slate-100 p-2 text-xs font-semibold text-slate-500">This order was cancelled and stock was restored.</p>
            @endif
        </div>
        <div class="grid gap-4 md:grid-cols-2">
            <div class="card text-xs"><h2 class="mb-2 font-bold">Items ({{ $order->items->sum('qty') }})</h2>
                @foreach ($order->items as $item)
                    <div class="flex items-center gap-3 border-t border-slate-100 py-2"><x-shop.product-thumb :product="$item->product?->toCardArray() ?? ['emoji' => '📦', 'image' => null]" class="size-11" /><div><b>{{ $item->name }}</b><br><span class="text-slate-500">৳{{ number_format((float) $item->unit_price) }} × {{ $item->qty }}</span></div><b class="ms-auto">৳{{ number_format((float) $item->unit_price * $item->qty) }}</b></div>
                @endforeach
            </div>
            <div class="space-y-4">
                <div class="card grid gap-3 text-xs sm:grid-cols-2">
                    <div><h2 class="mb-1 font-bold">📍 Shipping</h2><p class="text-slate-600">{{ $order->name }}<br>{{ $order->address }}<br>{{ $order->district }}{{ $order->thana ? ' · '.$order->thana : '' }} · {{ $order->phone }}</p></div>
                    <div><h2 class="mb-1 font-bold">💵 Payment</h2><p class="text-slate-600">{{ ucfirst($order->payment_method) }}<br><span class="rounded px-1.5 py-0.5 font-semibold {{ $order->payment_status === 'paid' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">{{ ucfirst($order->payment_status) }}</span></p></div>
                </div>
                <div class="card text-xs"><h2 class="mb-2 font-bold">Order Totals</h2>
                    <div class="space-y-1 text-slate-600"><div class="flex justify-between"><span>Subtotal</span><span>৳{{ number_format((float) $order->subtotal) }}</span></div>@if ($order->discount > 0)<div class="flex justify-between"><span>Discount{{ $order->coupon_code ? " ({$order->coupon_code})" : '' }}</span><span class="text-emerald-600">−৳{{ number_format((float) $order->discount) }}</span></div>@endif<div class="flex justify-between"><span>Delivery</span><span>৳{{ number_format((float) $order->delivery_charge) }}</span></div><div class="flex justify-between border-t border-slate-100 pt-1.5 text-sm font-extrabold text-ink"><span>Total</span><span>৳{{ number_format((float) $order->total) }}</span></div></div>
                </div>
            </div>
        </div>
        <div x-show="ret" x-cloak class="fixed inset-0 z-50 grid place-items-center bg-black/50 p-4">
            <div class="card w-full max-w-sm" @click.away="ret = false">
                <h2 class="font-bold">Return / Exchange Request</h2>
                <p class="mt-1 text-xs text-slate-500">For order <span class="font-mono font-bold">{{ $order->code }}</span> — contact support with your reason:</p>
                <div class="mt-3 flex gap-2"><button @click="ret = false" class="btn-outline flex-1 py-2 text-xs">Close</button><a href="/contact" class="btn flex-1 py-2 text-xs">Contact support →</a></div>
            </div>
        </div>
    </div>
</div>
@endsection
