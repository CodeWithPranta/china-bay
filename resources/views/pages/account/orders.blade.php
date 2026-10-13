@extends('layouts.app')
@section('title', 'My Orders — ChinaBay')
@section('content')
@php use App\Support\OrderStatus; @endphp
<div class="flex flex-col gap-4 py-6 lg:flex-row" x-data="{ f: 'all' }">
    <x-account.sidebar />
    <div class="min-w-0 flex-1">
        <div class="card">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h1 class="text-lg font-extrabold">My Orders 📦</h1>
                <select x-model="f" class="input !w-auto text-xs"><option value="all">All orders</option>@foreach (OrderStatus::steps() as $s)<option value="{{ $s['label'] }}">{{ $s['label'] }}</option>@endforeach<option value="Cancelled">Cancelled</option></select>
            </div>
            @if (session('status'))
                <p class="mt-3 rounded-lg bg-emerald-50 px-4 py-2 text-xs font-semibold text-emerald-700">{{ session('status') }}</p>
            @endif
            <div class="mt-3 space-y-2.5">
                @forelse ($orders as $order)
                    <div class="flex flex-wrap items-center gap-2 rounded-lg border border-slate-100 p-3 text-xs" x-show="f==='all' || f==='{{ $order->statusLabel() }}'">
                        <div class="min-w-0 flex-1"><b class="font-mono">{{ $order->code }}</b> <span class="text-slate-400">· {{ $order->created_at->format('d M Y') }} · {{ $order->items->sum('qty') }} items</span><br><span class="font-semibold text-brand">৳{{ number_format((float) $order->total) }}</span> <span class="rounded-full px-2 py-0.5 font-semibold {{ OrderStatus::badge($order->status) }}">{{ $order->statusLabel() }}</span></div>
                        <a href="{{ route('account.orders.show', $order->code) }}" class="btn-outline px-3 py-1.5">View</a>
                        @if ($order->isCancellable())
                            <form method="POST" action="{{ route('account.orders.cancel', $order->code) }}" onsubmit="return confirm('Cancel {{ $order->code }}?');">
                                @csrf
                                <button class="rounded-md border border-red-100 px-3 py-1.5 text-red-600 hover:bg-red-50">Cancel</button>
                            </form>
                        @endif
                    </div>
                @empty
                    <p class="py-8 text-center text-xs text-slate-400">No orders yet — <a href="/shop" class="font-bold text-brand underline">start shopping</a>.</p>
                @endforelse
            </div>
            <div class="mt-3">{{ $orders->links() }}</div>
        </div>
    </div>
</div>
@endsection
