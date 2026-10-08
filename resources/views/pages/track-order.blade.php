@extends('layouts.app')
@section('title', 'Track Order – ChinaBay')

@section('content')
<nav class="mt-3 text-xs text-slate-500" aria-label="Breadcrumb"><a href="/" class="hover:text-brand">Home</a> / <span class="text-ink font-semibold">Track Order · অর্ডার ট্র্যাক</span></nav>
<h1 class="h2 mt-1">Track Your <span>Order</span></h1>

<div class="mt-4 grid gap-5 pb-10 lg:grid-cols-[380px_1fr]" x-data="{ modal: '', oid: 'CB-1001', phone: '' }">
    {{-- Lookup + history --}}
    <div class="space-y-4">
        <form class="card space-y-3" @submit.prevent="oid = oid || 'CB-1001'">
            <b class="text-sm">Order Lookup · অর্ডার খুঁজুন</b>
            <div><label class="label" for="tOid">Order ID *</label><input id="tOid" x-model="oid" required class="input" placeholder="e.g. CB-1001"></div>
            <div><label class="label" for="tPhone">Phone *</label><input id="tPhone" x-model="phone" required pattern="01[0-9]{9}" class="input" placeholder="01XXXXXXXXX"></div>
            <button class="btn w-full py-2.5 text-sm">Track 🔍</button>
            <p class="text-[11px] text-slate-500">Demo: any ID shows sample timeline below.</p>
        </form>
        <div class="card text-xs">
            <b class="text-sm">Order History · ইতিহাস</b>
            <table class="mt-2 w-full text-left">
                <tr class="text-slate-400"><th class="py-1">Order</th><th>Status</th><th></th></tr>
                @foreach ([['CB-1001', 'Shipped'], ['CB-1002', 'Delivered'], ['CB-1003', 'Processing']] as [$id, $st])
                    <tr class="border-t border-slate-100"><td class="py-2 font-bold"><a href="/account/orders/{{ $id }}" class="text-brand hover:underline">{{ $id }}</a></td><td>{{ $st }}</td><td class="text-right"><a href="/track-order?oid={{ $id }}" class="text-brand hover:underline">View</a></td></tr>
                @endforeach
            </table>
        </div>
    </div>

    {{-- Timeline + actions --}}
    <div class="space-y-4">
        <div class="card">
            <b class="text-sm">Order <span class="text-brand" x-text="oid">CB-1001</span> · Status Timeline</b>
            <ol class="mt-3 space-y-0 border-l-2 border-slate-100 pl-0 text-xs">
                @php $tl = [['Placed', 'Order received ✅', 1], ['Confirmed', 'Phone confirmation done', 0], ['Processing', 'Quality check & packing', 0], ['Shipped', 'Handed to courier', 0], ['Out for Delivery', 'Rider is on the way', 0], ['Delivered', 'Enjoy your product 🎉', 0]]; @endphp
                @foreach ($tl as [$t, $d, $on])
                    <li class="relative pb-4 pl-6">
                        <span class="absolute -left-[7px] top-0 size-3 rounded-full {{ $on ? 'bg-emerald-500' : 'bg-slate-200' }}"></span>
                        <b class="{{ $on ? 'text-emerald-600' : '' }}">{{ $t }} {{ $on ? '· current' : '' }}</b>
                        <p class="text-slate-500">{{ $d }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
        <div class="card flex flex-wrap gap-2 text-xs">
            <button @click="modal = 'cancel'" class="btn-outline px-4 py-2">Cancel Request · বাতিল</button>
            <button @click="modal = 'return'" class="btn-outline px-4 py-2">Return Request · রিটার্ন</button>
            <button @click="modal = 'exchange'" class="btn-outline px-4 py-2">Exchange Request · এক্সচেঞ্জ</button>
        </div>
        {{-- Modal-ish form --}}
        <div x-show="modal" x-cloak class="fixed inset-0 z-50 grid place-items-center bg-black/50 p-4">
            <form class="card w-full max-w-sm space-y-3" @submit.prevent="modal = ''; alert('Request submitted (demo).')">
                <b class="text-sm capitalize" x-text="modal + ' request'"></b>
                <div><label class="label">Reason · কারণ *</label><textarea required rows="3" class="input" placeholder="Write your reason..."></textarea></div>
                <div><label class="label">Phone *</label><input required pattern="01[0-9]{9}" class="input" placeholder="01XXXXXXXXX"></div>
                <div class="flex gap-2"><button type="button" @click="modal = ''" class="btn-outline flex-1 py-2 text-xs">Close</button><button class="btn flex-1 py-2 text-xs">Submit →</button></div>
            </form>
        </div>
    </div>
</div>
@endsection
