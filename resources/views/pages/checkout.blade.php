@extends('layouts.app')
@section('title', 'Checkout – ChinaBay')
@php use App\Support\Catalog; use App\Support\ShopDemo; $products = Catalog::products(); $zones = ShopDemo::deliveryZones(); @endphp

@section('content')
<nav class="mt-3 text-xs text-slate-500" aria-label="Breadcrumb"><a href="/" class="hover:text-brand">Home</a> / <a href="/cart" class="hover:text-brand">Cart</a> / <span class="text-ink font-semibold">Checkout · চেকআউট</span></nav>
<h1 class="h2 mt-1">Checkout · <span>অর্ডার করুন</span></h1>

<div class="mt-4 grid gap-5 pb-10 lg:grid-cols-[1fr_360px]" x-data="checkoutForm()">
    {{-- Left form --}}
    <form action="{{ route('checkout.store') }}" method="POST" id="coForm" class="card space-y-4" @submit="if (!agree) { $event.preventDefault(); alert('Please accept terms & conditions.'); }">
        @csrf
        <input type="hidden" name="items" id="coItemsInput" value="[]">
        @if ($errors->any())
            <div class="rounded-md bg-red-50 p-3 text-xs font-semibold text-brand">
                @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
            </div>
        @endif
        <x-shop.voice-lang-toggle />
        <div>
            <b class="text-sm">Contact Info · যোগাযোগ</b>
            <div class="mt-2 grid gap-3 sm:grid-cols-2">
                <div>
                    <label class="label" for="coName">Full name · নাম *</label>
                    <div class="relative">
                        <input id="coName" name="name" value="{{ old('name', auth()->user()?->name) }}" required class="input pe-10" placeholder="e.g. Rahim Uddin — or tap 🎤 & speak">
                        <span class="absolute end-1 top-1/2 -translate-y-1/2"><x-shop.voice-mic for="coName" /></span>
                    </div>
                </div>
                <div>
                    <label class="label" for="coPhone">Phone · মোবাইল *</label>
                    <div class="relative">
                        <input id="coPhone" name="phone" value="{{ old('phone', auth()->user()?->phone) }}" required pattern="01[0-9]{9}" class="input pe-10" placeholder="01XXXXXXXXX — or tap 🎤 & speak">
                        <span class="absolute end-1 top-1/2 -translate-y-1/2"><x-shop.voice-mic for="coPhone" /></span>
                    </div>
                </div>
            </div>
            <div class="mt-3">
                <label class="label" for="coAddr">Full address · ঠিকানা *</label>
                <div class="relative">
                    <textarea id="coAddr" name="address" required rows="2" class="input pe-10" placeholder="House, road, area... — or tap 🎤 & speak">{{ old('address') }}</textarea>
                    <span class="absolute end-1 top-2"><x-shop.voice-mic for="coAddr" mode="append" /></span>
                </div>
            </div>
            <div class="mt-3 grid gap-3 sm:grid-cols-2">
                <div><label class="label" for="district">District · জেলা *</label>
                    <select id="district" name="district" x-model="district" required class="input">
                        <option value="">Select district</option>
                        @foreach ($zones as $z)<option value="{{ $z['district'] }}">{{ $z['district'] }}</option>@endforeach
                    </select></div>
                <div><label class="label" for="thana">Thana / Area · থানা *</label>
                    <select id="thana" name="thana" x-model="thana" required class="input"><option value="">Select thana</option><template x-for="t in thanas" :key="t"><option x-text="t"></option></template></select></div>
            </div>
        </div>
        <div>
            <b class="text-sm">Delivery Method · ডেলিভারি মাধ্যম</b>
            <div class="mt-2 grid gap-2 sm:grid-cols-2">
                <label class="flex cursor-pointer items-center gap-2 rounded-md border p-3 text-xs" :class="method === 'inside' ? 'border-brand bg-brand/5' : 'border-slate-200'"><input type="radio" name="delivery_method" x-model="method" value="inside" class="accent-[#c8102e]"> Inside Dhaka · ঢাকার ভিতরে <b x-text="'৳' + chargeInside()"></b></label>
                <label class="flex cursor-pointer items-center gap-2 rounded-md border p-3 text-xs" :class="method === 'outside' ? 'border-brand bg-brand/5' : 'border-slate-200'"><input type="radio" name="delivery_method" x-model="method" value="outside" class="accent-[#c8102e]"> Outside Dhaka · ঢাকার বাইরে <b x-text="'৳' + chargeOutside()"></b></label>
            </div>
        </div>
        <div>
            <b class="text-sm">Payment · পেমেন্ট</b>
            <div class="mt-2 grid gap-2 sm:grid-cols-2 text-xs">
                <label class="flex cursor-pointer items-center gap-2 rounded-md border p-3" :class="pay === 'cod' ? 'border-brand bg-brand/5' : 'border-slate-200'"><input type="radio" name="payment_method" x-model="pay" value="cod" class="accent-[#c8102e]"> 💵 COD · ক্যাশ অন ডেলিভারি</label>
                <label class="flex cursor-pointer items-center gap-2 rounded-md border p-3" :class="pay === 'bkash' ? 'border-brand bg-brand/5' : 'border-slate-200'"><input type="radio" name="payment_method" x-model="pay" value="bkash" class="accent-[#c8102e]"> 📱 bKash</label>
                <label class="flex cursor-pointer items-center gap-2 rounded-md border p-3" :class="pay === 'nagad' ? 'border-brand bg-brand/5' : 'border-slate-200'"><input type="radio" name="payment_method" x-model="pay" value="nagad" class="accent-[#c8102e]"> 📱 Nagad</label>
                <label class="flex cursor-pointer items-center gap-2 rounded-md border p-3" :class="pay === 'card' ? 'border-brand bg-brand/5' : 'border-slate-200'"><input type="radio" name="payment_method" x-model="pay" value="card" class="accent-[#c8102e]"> 💳 Card · কার্ড</label>
            </div>
            <div x-show="pay === 'cod'" class="mt-2 rounded-md bg-emerald-50 p-3 text-[11px] text-emerald-700">পণ্য হাতে পেয়ে মূল্য পরিশোধ করুন। ডেলিভারি চার্জ সহ মোট টাকা প্রস্তুত রাখুন।</div>
            <div x-show="pay === 'bkash'" x-cloak class="mt-2 rounded-md bg-pink-50 p-3 text-[11px]">বিকাশে পেমেন্ট: <b>017XXXXXXX (Send Money)</b> — TrxID অর্ডার নোটে লিখুন। Demo only.</div>
            <div x-show="pay === 'nagad'" x-cloak class="mt-2 rounded-md bg-amber-50 p-3 text-[11px]">নগদে পেমেন্ট: <b>017XXXXXXX (Send Money)</b> — TrxID অর্ডার নোটে লিখুন। Demo only.</div>
            <div x-show="pay === 'card'" x-cloak class="mt-2 rounded-md bg-sky-50 p-3 text-[11px]">Card payment via SSLCommerz on order confirm (demo — no charge now).</div>
        </div>
        <div>
            <label class="label" for="notes">Order notes (optional)</label>
            <div class="relative">
                <textarea id="notes" name="notes" rows="2" class="input pe-10" placeholder="e.g. Call before delivery... — or tap 🎤 & speak">{{ old('notes') }}</textarea>
                <span class="absolute end-1 top-2"><x-shop.voice-mic for="notes" mode="append" /></span>
            </div>
        </div>
        <label class="flex items-center gap-2 text-xs"><input type="checkbox" x-model="agree" class="accent-[#c8102e]"> I agree to <a href="/terms" class="text-brand underline">terms & conditions</a> · শর্তাবলীতে সম্মত আছি</label>
        <button class="btn w-full py-3 text-sm">Place Order · অর্ডার কনফার্ম করুন →</button>
    </form>

    {{-- Right summary --}}
    <aside class="card h-fit space-y-3 text-sm">
        <b>Order Summary · সামারি</b>
        <div id="coItems" class="max-h-64 space-y-2 overflow-y-auto text-xs"></div>
        <div class="flex gap-2">
            <div class="relative min-w-0 flex-1">
                <input id="coCoupon" name="coupon_code" placeholder="Promo code — type or tap 🎤" class="input pe-10 !py-2 text-xs" aria-label="Promo code">
                <span class="absolute end-1 top-1/2 -translate-y-1/2"><x-shop.voice-mic for="coCoupon" title="Voice promo code" /></span>
            </div>
            <button type="button" id="coApply" class="btn-outline shrink-0 px-3 py-2 text-xs">Apply</button>
        </div>
        <p id="coCouponMsg" class="text-[11px] text-slate-500"></p>
        <div class="space-y-1.5 border-t border-slate-100 pt-3 text-xs">
            <div class="flex justify-between"><span>Subtotal</span><b id="coSub">৳ 0</b></div>
            <div class="flex justify-between text-emerald-600"><span>Discount</span><b id="coDisc">− ৳ 0</b></div>
            <div class="flex justify-between"><span>Delivery (<span x-text="district || '—'"></span>)</span><b id="coDel" x-text="'৳ ' + delivery()">৳ 0</b></div>
            <div class="flex justify-between border-t border-slate-100 pt-2 text-sm"><span class="font-bold">Total · মোট</span><b id="coTotal" class="text-brand">৳ 0</b></div>
        </div>
        <p class="text-[11px] text-slate-500">✓ SMS / Email / WhatsApp confirmation after ordering.</p>
    </aside>
</div>
@endsection

@push('scripts')
<script>
window.CB_PRODUCTS = @json(collect($products)->mapWithKeys(fn($p) => [$p['id'] => $p]));
const CB_ZONES = @json($zones);
const CB_THANAS = { Dhaka: ['Mirpur', 'Uttara', 'Dhanmondi'], Chattogram: ['GEC Circle', 'Agrabad', 'Halishahar'], Sylhet: ['Zindabazar', 'Ambarkhana', 'Uposhahar'], Rajshahi: ['Shaheb Bazar', 'Uposhahar', 'Katakhali'], Khulna: ['Sonadanga', 'Khalishpur', 'Daulatpur'], Barishal: ['Sadar', 'Nathullabad', 'Rupatali'], Rangpur: ['Jahaj Company', 'Lalbagh', 'CO Bazar'], Mymensingh: ['Ganginar Par', 'Chorpara', 'Maskanda'] };
function checkoutForm() {
    return {
        district: 'Dhaka', thana: '', method: 'inside', pay: 'cod', agree: false,
        get thanas() { return CB_THANAS[this.district] || []; },
        zone() { return CB_ZONES.find((z) => z.district === this.district) || CB_ZONES[0]; },
        chargeInside() { return this.zone().charge_inside; },
        chargeOutside() { return this.zone().charge_outside; },
        delivery() { return this.method === 'inside' ? this.chargeInside() : this.chargeOutside(); },
    };
}
(function () {
    const tk = (n) => '৳ ' + Number(n).toLocaleString();
    const thumb = (p, cls) => window.cbHelpers?.cbImg(p, cls)
        ?? `<span class="${cls} grid shrink-0 place-items-center rounded bg-slate-50 text-2xl">${p.emoji}</span>`;
    function couponVal(sub) {
        const c = JSON.parse(localStorage.getItem('cb_coupon') || 'null');
        if (c === 'CHINABAY10') { return Math.round(sub * 0.10); }
        if (c === 'FLASH50') { return Math.min(50, sub); }
        return 0;
    }
    function paint() {
        const cart = window.ChinaBay?.cart || {};
        const ids = Object.keys(cart).filter((id) => window.CB_PRODUCTS[id]);
        document.getElementById('coItems').innerHTML = ids.length
            ? ids.map((id) => { const p = window.CB_PRODUCTS[id]; return `<div class="flex items-center gap-2">${thumb(p, 'size-10')}<span class="flex-1 truncate">${p.name} × ${cart[id]}</span><b>${tk(p.price * cart[id])}</b></div>`; }).join('')
            : '<p class="text-slate-400">Cart is empty — <a href="/shop" class="text-brand underline">add products</a>.</p>';
        const sub = ids.reduce((a, id) => a + window.CB_PRODUCTS[id].price * cart[id], 0);
        const disc = couponVal(sub);
        const del = ids.length ? (parseInt(document.getElementById('coDel')?.textContent.replace(/\D/g, '') || '60', 10) || 60) : 0;
        document.getElementById('coSub').textContent = tk(sub);
        document.getElementById('coDisc').textContent = '− ' + tk(disc);
        document.getElementById('coTotal').textContent = tk(Math.max(0, sub - disc) + del);
    }
    document.addEventListener('chinabay:sync', paint);
    document.addEventListener('change', () => setTimeout(paint, 0));
    document.getElementById('coForm')?.addEventListener('submit', (e) => {
        const cart = window.ChinaBay?.cart || {};
        const items = Object.keys(cart).filter((id) => window.CB_PRODUCTS[id] && cart[id] > 0)
            .map((id) => ({ id: Number(id), qty: cart[id] }));
        if (!items.length) {
            e.preventDefault();
            window.cbHelpers?.toast('Your cart is empty.');
            return;
        }
        document.getElementById('coItemsInput').value = JSON.stringify(items);
    });
    document.getElementById('coApply')?.addEventListener('click', () => {
        const v = document.getElementById('coCoupon').value.trim().toUpperCase();
        const ok = ['CHINABAY10', 'FLASH50'].includes(v);
        window.ChinaBay.coupon = ok ? v : null; window.ChinaBay.sync();
        try { localStorage.setItem('cb_coupon', JSON.stringify(window.ChinaBay.coupon)); } catch {}
        document.getElementById('coCouponMsg').textContent = ok ? '✓ Applied: ' + v : 'Invalid code. Try CHINABAY10 / FLASH50.';
        paint();
    });
    try {
        const saved = JSON.parse(localStorage.getItem('cb_coupon') || 'null');
        if (saved) { document.getElementById('coCoupon').value = saved; document.getElementById('coCouponMsg').textContent = '✓ Applied: ' + saved; }
    } catch {}
    paint();
})();
</script>
@endpush
