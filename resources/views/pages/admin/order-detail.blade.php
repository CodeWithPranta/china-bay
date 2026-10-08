@extends('layouts.admin')
@section('title', 'Order CB-1001 — ChinaBay Admin')
@section('heading', 'Order CB-1001')
@section('content')
<div x-data="{ toast: '', step: 2 }">
    <div class="grid gap-4 lg:grid-cols-3">
        <div class="card">
            <h3 class="font-bold">👤 Customer</h3>
            <p class="mt-2 font-bold">Farhana Islam <span class="ml-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[11px] text-emerald-700">✓ OTP verified</span></p>
            <p class="mt-1 text-sm text-slate-600">House 12, Road 5, Dhanmondi<br>Dhaka 1205<br>📞 01799-112233</p>
            <span class="mt-2 inline-block rounded bg-slate-100 px-2 py-0.5 text-[11px] font-bold">Repeat buyer · 6 orders</span>
        </div>
        <div class="card lg:col-span-2">
            <div class="flex items-center justify-between"><h3 class="font-bold">📦 Delivery timeline</h3><span class="rounded-full bg-sky-100 px-2 py-0.5 text-[11px] font-bold text-sky-700">Confirmed</span></div>
            <ol class="mt-3 grid grid-cols-2 gap-2 text-xs font-semibold sm:grid-cols-3 lg:grid-cols-6">
                @foreach (['Placed','Confirmed','Packed','Shipped','Out for delivery','Delivered'] as $i => $s)
                <li class="rounded-lg p-2 text-center {{ $i <= 1 ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-50 text-slate-500' }}">{{ $i <= 1 ? '●' : '○' }} {{ $s }}</li>
                @endforeach
            </ol>
            <div class="mt-3 flex flex-wrap gap-2">
                <button @click="toast='Status → Packed ✓'" class="btn px-3 py-2 text-xs">Mark packed</button>
                <button @click="toast='Status → Shipped ✓'" class="btn-outline px-3 py-2 text-xs">Mark shipped</button>
                <button @click="toast='Status → Delivered ✓'" class="btn-outline px-3 py-2 text-xs">Mark delivered</button>
            </div>
        </div>
    </div>
    <div class="mt-4 grid gap-4 lg:grid-cols-3">
        <div class="card overflow-x-auto lg:col-span-2">
            <h3 class="mb-2 font-bold">Items (2)</h3>
            <table class="w-full min-w-[480px] text-left text-sm">
                <thead class="text-xs text-slate-500"><tr><th class="py-2">Product</th><th>Variant</th><th>Qty</th><th class="text-right">Price</th></tr></thead>
                <tbody>
                    <tr class="border-t border-slate-100"><td class="py-2">⌚ Smart Watch <span class="block text-[11px] text-slate-500">Fitness Tracker</span></td><td>Black</td><td>1</td><td class="text-right font-bold">৳2,999</td></tr>
                    <tr class="border-t border-slate-100"><td class="py-2">🔋 Power Bank 20000mAh <span class="block text-[11px] text-slate-500">Fast Charging</span></td><td>—</td><td>1</td><td class="text-right font-bold">৳1,619</td></tr>
                </tbody>
            </table>
            <div class="ml-auto mt-3 max-w-60 space-y-1 text-sm">
                <p class="flex justify-between"><span>Subtotal</span><b>৳4,618</b></p>
                <p class="flex justify-between"><span>Delivery</span><b>৳60</b></p>
                <p class="flex justify-between text-emerald-600"><span>Coupon EID20</span><b>−৳200</b></p>
                <p class="flex justify-between border-t pt-1 text-base font-extrabold"><span>Total</span><span>৳4,478</span></p>
            </div>
        </div>
        <div class="space-y-4">
            <div class="card"><h3 class="font-bold">💳 Payment</h3><p class="mt-1 text-sm">COD · <b>৳4,478</b> on delivery</p><p class="text-xs text-slate-500">bKash advance ৳0 · Due ৳4,478</p></div>
            <div class="card"><h3 class="font-bold">📝 Notes + fraud check</h3><textarea class="input mt-2" rows="2" placeholder="Rider note…">Call before delivery, gate 3.</textarea>
                <p class="mt-2 rounded-lg bg-red-50 p-2 text-xs text-brand">⚠ Flag demo: same phone placed 3 COD orders in 24h — confirm via call. <button @click="toast='Flagged for review 🚩'" class="font-bold underline">Flag</button></p>
                <button @click="toast='Note saved ✓'" class="btn mt-2 w-full py-2 text-xs">Save note</button>
            </div>
        </div>
    </div>
    <div x-show="toast" x-transition class="fixed bottom-5 right-5 z-50 rounded-lg bg-navy px-4 py-3 text-sm font-bold text-white shadow-lg" x-text="toast" @click="toast=''" x-cloak></div>
</div>
@endsection
