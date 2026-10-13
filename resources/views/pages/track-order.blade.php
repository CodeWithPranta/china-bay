@extends('layouts.app')
@section('title', 'Track Order – ChinaBay')
@php use App\Support\OrderStatus; @endphp

@section('content')
<nav class="mt-3 text-xs text-slate-500" aria-label="Breadcrumb"><a href="/" class="hover:text-brand">Home</a> / <span class="text-ink font-semibold">Track Order · অর্ডার ট্র্যাক</span></nav>
<h1 class="h2 mt-1">Track Your <span>Order</span></h1>

<div class="mt-4 grid gap-5 pb-10 lg:grid-cols-[380px_1fr]" x-data="{ modal: '' }">
    {{-- Lookup + history --}}
    <div class="space-y-4">
        <form class="card space-y-3" method="GET" action="{{ route('track') }}">
            <b class="text-sm">Order Lookup · অর্ডার খুঁজুন</b>
            <div><label class="label" for="tOid">Order ID *</label><input id="tOid" name="code" value="{{ $code }}" required class="input font-mono" placeholder="e.g. CB-20261013-0001"></div>
            <div><label class="label" for="tPhone">Phone *</label><input id="tPhone" name="phone" value="{{ $phone }}" required pattern="01[0-9]{9}" class="input" placeholder="01XXXXXXXXX"></div>
            <button class="btn w-full py-2.5 text-sm">Track 🔍</button>
            @if ($lookedUp && ! $tracked)
                <p class="rounded-md bg-red-50 p-2 text-[11px] font-semibold text-brand">No order found for this code + phone. Check and try again.</p>
            @endif
        </form>
        @if ($history->isNotEmpty())
            <div class="card text-xs">
                <b class="text-sm">My recent orders · ইতিহাস</b>
                <table class="mt-2 w-full text-left">
                    <tr class="text-slate-400"><th class="py-1">Order</th><th>Status</th><th></th></tr>
                    @foreach ($history as $h)
                        <tr class="border-t border-slate-100"><td class="py-2 font-mono font-bold">{{ $h->code }}</td><td><span class="rounded-full px-2 py-0.5 font-semibold {{ OrderStatus::badge($h->status) }}">{{ $h->statusLabel() }}</span></td><td class="text-right"><a href="{{ route('track', ['code' => $h->code, 'phone' => $h->phone]) }}" class="text-brand hover:underline">View</a></td></tr>
                    @endforeach
                </table>
            </div>
        @endif
    </div>

    {{-- Timeline + actions --}}
    <div class="space-y-4">
        <div class="card">
            @if ($tracked)
                @php $reached = OrderStatus::reached($tracked->status); @endphp
                <b class="text-sm">Order <span class="font-mono text-brand">{{ $tracked->code }}</span> · <span class="rounded-full px-2 py-0.5 {{ OrderStatus::badge($tracked->status) }}">{{ $tracked->statusLabel() }}</span></b>
                <p class="mt-1 text-xs text-slate-500">{{ $tracked->name }} · {{ $tracked->district }} · ৳{{ number_format((float) $tracked->total) }} · {{ ucfirst($tracked->payment_method) }}</p>
                @if ($tracked->status === \App\Support\OrderStatus::CANCELLED)
                    <p class="mt-2 rounded-md bg-slate-100 p-2 text-xs font-semibold text-slate-500">This order was cancelled.</p>
                @else
                    <ol class="mt-3 space-y-0 border-l-2 border-slate-100 pl-0 text-xs">
                        @foreach (OrderStatus::steps() as $s)
                            @php $on = in_array($s['key'], $reached, true); $current = end($reached) === $s['key']; @endphp
                            <li class="relative pb-4 pl-6">
                                <span class="absolute -left-[7px] top-0 size-3 rounded-full {{ $on ? 'bg-emerald-500' : 'bg-slate-200' }}"></span>
                                <b class="{{ $on ? 'text-emerald-600' : '' }}">{{ $s['label'] }} {{ $current ? '· current' : '' }}</b>
                            </li>
                        @endforeach
                    </ol>
                @endif
            @else
                <b class="text-sm">Status Timeline</b>
                <p class="mt-1 text-xs text-slate-500">Enter your order ID + phone to see the live delivery status here.</p>
            @endif
        </div>
        @if ($tracked && $tracked->isCancellable() && auth()->id() === $tracked->user_id)
            <div class="card text-xs">
                <form method="POST" action="{{ route('account.orders.cancel', $tracked->code) }}" onsubmit="return confirm('Cancel this order? Stock will be restored.');">
                    @csrf
                    <button class="btn-outline px-4 py-2">Cancel Request · বাতিল</button>
                </form>
            </div>
        @endif
    </div>
</div>
@endsection
