@extends('layouts.app')
@section('title', 'Cart – ChinaBay')
@php use App\Support\Catalog; $products = Catalog::products(); @endphp

@section('content')
<nav class="mt-3 text-xs text-slate-500" aria-label="Breadcrumb"><a href="/" class="hover:text-brand">Home</a> / <span class="text-ink font-semibold">Cart · কার্ট</span></nav>
<h1 class="h2 mt-1">Shopping <span>Cart</span></h1>

<div id="cartWrap" class="mt-4 grid gap-5 pb-10 lg:grid-cols-[1fr_340px]" x-data="{ code: '', msg: '' }">
    <div>
        <div id="cartRows" class="space-y-2"></div>
        <div id="cartEmpty" class="hidden py-16 text-center">
            <p class="text-6xl">🛒</p>
            <b class="mt-2 block">Your cart is empty</b>
            <p class="mt-1 text-xs text-slate-500">আপনার কার্ট খালি। পছন্দের পণ্য যোগ করুন।</p>
            <a href="/shop" class="btn mt-4 px-6 py-2.5 text-sm">Continue Shopping →</a>
        </div>
        <a href="/shop" class="mt-3 inline-block text-xs font-semibold text-brand hover:underline">← Continue shopping · আরও কেনাকাটা করুন</a>
    </div>
    <aside class="card h-fit space-y-3 text-sm">
        <b>Order Summary · অর্ডার সামারি</b>
        <div class="flex gap-2">
            <input x-model="code" id="couponIn" placeholder="Coupon code (CHINABAY10 / FLASH50)" class="input !py-2 text-xs" aria-label="Coupon code">
            <button id="couponBtn" class="btn-outline px-3 py-2 text-xs">Apply</button>
        </div>
        <p id="couponMsg" class="text-[11px] text-slate-500" x-text="msg"></p>
        <div class="space-y-1.5 border-t border-slate-100 pt-3 text-xs">
            <div class="flex justify-between"><span>Subtotal · সাবটোটাল</span><b id="ctSub">৳ 0</b></div>
            <div class="flex justify-between text-emerald-600"><span>Discount · ছাড়</span><b id="ctDisc">− ৳ 0</b></div>
            <div class="flex justify-between text-slate-500"><span>Delivery · ডেলিভারি</span><span>Calculated at checkout</span></div>
            <div class="flex justify-between border-t border-slate-100 pt-2 text-sm"><span class="font-bold">Total · মোট</span><b id="ctTotal" class="text-brand">৳ 0</b></div>
        </div>
        <a href="/checkout" class="btn w-full py-3 text-sm">Proceed to Checkout →</a>
        <p class="text-center text-[11px] text-slate-500">💵 COD · 📱 bKash / Nagad · 💳 Card</p>
    </aside>
</div>

<section class="pb-10">
    <x-shop.section-head title="Recently <span>Viewed</span>" />
    <p class="mt-2 rounded-lg border border-dashed border-slate-200 py-6 text-center text-xs text-slate-400">আপনি যে পণ্যগুলো দেখবেন সেগুলো এখানে থাকবে</p>
</section>
@endsection

@push('scripts')
<script>
window.CB_PRODUCTS = @json(collect($products)->mapWithKeys(fn($p) => [$p['id'] => $p]));
(function () {
    const rows = document.getElementById('cartRows');
    const empty = document.getElementById('cartEmpty');
    const tk = (n) => '৳ ' + Number(n).toLocaleString();
    function couponVal(sub) {
        const c = (JSON.parse(localStorage.getItem('cb_coupon') || 'null'));
        if (c === 'CHINABAY10') { return Math.round(sub * 0.10); }
        if (c === 'FLASH50') { return Math.min(50, sub); }
        return 0;
    }
    function paint() {
        const cart = window.ChinaBay?.cart || {};
        const ids = Object.keys(cart).filter((id) => window.CB_PRODUCTS[id]);
        rows.innerHTML = ids.map((id) => {
            const p = window.CB_PRODUCTS[id], q = cart[id];
            return `<div class="card flex items-center gap-3 !p-3">
                <span class="grid size-16 place-items-center rounded-lg bg-slate-50 text-4xl">${p.emoji}</span>
                <div class="min-w-0 flex-1 text-xs"><b class="block truncate text-sm">${p.name}</b>
                <span class="text-brand font-bold">${tk(p.price)}</span>
                <div class="mt-1.5 inline-flex items-center rounded border border-slate-200">
                <button data-qty="${p.id}" data-d="-1" class="px-2 py-0.5" aria-label="Decrease">−</button><span class="w-6 text-center font-bold">${q}</span><button data-qty="${p.id}" data-d="1" class="px-2 py-0.5" aria-label="Increase">+</button></div></div>
                <div class="text-right text-xs"><b>${tk(p.price * q)}</b><br><button data-rm="${p.id}" class="mt-1 text-slate-400 hover:text-brand">🗑 Remove</button></div></div>`;
        }).join('');
        const has = ids.length > 0;
        rows.classList.toggle('hidden', !has);
        empty.classList.toggle('hidden', has);
        const sub = ids.reduce((a, id) => a + window.CB_PRODUCTS[id].price * cart[id], 0);
        const disc = couponVal(sub);
        document.getElementById('ctSub').textContent = tk(sub);
        document.getElementById('ctDisc').textContent = '− ' + tk(disc);
        document.getElementById('ctTotal').textContent = tk(Math.max(0, sub - disc));
    }
    document.addEventListener('chinabay:sync', paint);
    document.getElementById('couponBtn')?.addEventListener('click', () => {
        const v = document.getElementById('couponIn').value.trim().toUpperCase();
        const ok = ['CHINABAY10', 'FLASH50'].includes(v);
        window.ChinaBay.coupon = ok ? v : null;
        window.ChinaBay.sync();
        document.getElementById('couponMsg').textContent = ok ? '✓ Coupon applied: ' + v : 'Invalid code. Try CHINABAY10 or FLASH50.';
        try { localStorage.setItem('cb_coupon', JSON.stringify(window.ChinaBay.coupon)); } catch {}
        paint();
    });
    try {
        const saved = JSON.parse(localStorage.getItem('cb_coupon') || 'null');
        if (saved) { document.getElementById('couponIn').value = saved; document.getElementById('couponMsg').textContent = '✓ Coupon applied: ' + saved; }
    } catch {}
    paint();
})();
</script>
@endpush
