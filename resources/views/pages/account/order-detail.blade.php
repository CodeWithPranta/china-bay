@extends('layouts.app')
@section('title', 'Order CB-1001 — ChinaBay')
@section('content')
<div class="flex flex-col gap-4 py-6 lg:flex-row" x-data="{ ret: false }">
    <x-account.sidebar />
    <div class="min-w-0 flex-1 space-y-4">
        <div class="card">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div><h1 class="text-lg font-extrabold">Order CB-1001</h1><p class="text-xs text-slate-500">Placed 08 Oct 2026 · COD · <span class="rounded-full bg-sky-100 px-2 py-0.5 font-semibold text-sky-700">Shipped</span></p></div>
                <div class="flex gap-2"><button onclick="window.cbHelpers.toast('Invoice downloading')" class="btn-outline px-3 py-2 text-xs">🧾 Invoice</button><button onclick="window.cbHelpers.toast('Cancel request sent')" class="rounded-md border border-red-100 px-3 py-2 text-xs text-red-600 hover:bg-red-50">Cancel</button><button @click="ret = true" class="btn px-3 py-2 text-xs">Return / Exchange</button></div>
            </div>
            <ol class="mt-4 grid grid-cols-3 gap-1 text-center text-[10px] font-semibold sm:grid-cols-6">
                @foreach ([['Placed',1],['Confirmed',1],['Processing',1],['Shipped',1],['Out for Delivery',0],['Delivered',0]] as [$s,$done])
                    <li><span class="mx-auto mb-1 grid size-7 place-items-center rounded-full {{ $done ? 'bg-brand text-white' : 'bg-slate-100 text-slate-400' }}">{{ $done ? '✓' : '•' }}</span><span class="{{ $done ? 'text-brand' : 'text-slate-400' }}">{{ $s }}</span></li>
                @endforeach
            </ol>
        </div>
        <div class="grid gap-4 md:grid-cols-2">
            <div class="card text-xs"><h2 class="mb-2 font-bold">Items (2)</h2>
                @foreach ([['🎧','TWS Wireless Earbuds','৳ 1,999 × 1'],['⌚','Smart Watch','৳ 2,999 × 1']] as [$e,$n,$p])
                    <div class="flex items-center gap-3 border-t border-slate-100 py-2"><span class="grid size-11 place-items-center rounded-lg bg-slate-50 text-2xl">{{ $e }}</span><div><b>{{ $n }}</b><br><span class="text-slate-500">{{ $p }}</span></div></div>
                @endforeach
            </div>
            <div class="space-y-4">
                <div class="card grid gap-3 text-xs sm:grid-cols-2">
                    <div><h2 class="mb-1 font-bold">📍 Shipping</h2><p class="text-slate-600">Rahim Uddin<br>House 12, Road 5, Dhanmondi<br>Dhaka 1205 · 01600 000000</p></div>
                    <div><h2 class="mb-1 font-bold">💵 Payment</h2><p class="text-slate-600">Cash on Delivery<br><span class="rounded bg-amber-50 px-1.5 py-0.5 font-semibold text-amber-700">Unpaid</span></p></div>
                </div>
                <div class="card text-xs"><h2 class="mb-2 font-bold">Order Totals</h2>
                    <div class="space-y-1 text-slate-600"><div class="flex justify-between"><span>Subtotal</span><span>৳ 4,998</span></div><div class="flex justify-between"><span>Discount (CHINABAY10)</span><span class="text-emerald-600">−৳ 440</span></div><div class="flex justify-between"><span>Delivery</span><span>৳ 60</span></div><div class="flex justify-between border-t border-slate-100 pt-1.5 text-sm font-extrabold text-ink"><span>Total</span><span>৳ 4,618</span></div></div>
                </div>
            </div>
        </div>
        <div x-show="ret" x-cloak class="fixed inset-0 z-50 grid place-items-center bg-black/50 p-4">
            <div class="card w-full max-w-sm" @click.away="ret = false">
                <h2 class="font-bold">Return / Exchange Request</h2>
                <label class="label mt-3">Reason</label><select class="input text-sm"><option>Wrong size / variant</option><option>Damaged item</option><option>Different from picture</option><option>Changed mind (7 days)</option></select>
                <label class="label mt-2">Note</label><textarea class="input" rows="3" placeholder="Describe the issue..."></textarea>
                <div class="mt-3 flex gap-2"><button @click="ret = false" class="btn-outline flex-1 py-2 text-xs">Close</button><button @click="ret = false; window.cbHelpers.toast('Return request submitted')" class="btn flex-1 py-2 text-xs">Submit</button></div>
            </div>
        </div>
    </div>
</div>
@endsection
