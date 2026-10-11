@php
    use App\Support\Catalog;
    $cats = Catalog::categories();
@endphp
<div id="ovl" class="fixed inset-0 z-40 hidden bg-black/50"></div>

<aside id="menu" class="fixed inset-y-0 left-0 z-50 w-72 -translate-x-full overflow-y-auto bg-white p-5 transition-transform duration-300" aria-label="Menu" inert>
    <div class="flex items-center justify-between">
        <a href="/" aria-label="ChinaBay home"><x-shop.logo class="h-9 w-auto" /></a>
        <button data-act="close" class="grid size-9 place-items-center rounded-full bg-slate-100 hover:bg-brand hover:text-white" aria-label="Close">
            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18"/></svg>
        </button>
    </div>
    <div class="mt-5 flex flex-col text-sm font-medium">
        <a href="/" class="border-b border-slate-100 py-3 hover:text-brand">Home</a>
        <a href="/shop" class="border-b border-slate-100 py-3 hover:text-brand">Shop</a>
        <a href="/flash-sale" class="border-b border-slate-100 py-3 hover:text-brand">⚡ Flash Sale</a>
        <a href="/track-order" class="border-b border-slate-100 py-3 hover:text-brand">Track Order</a>
        @auth
            <a href="/account" class="border-b border-slate-100 py-3 hover:text-brand">My Account · {{ auth()->user()->name }}</a>
            <form method="POST" action="{{ route('logout') }}" class="py-3">
                @csrf
                <button class="font-medium text-brand">⏻ Logout</button>
            </form>
        @else
            <div class="grid grid-cols-2 gap-2 py-3">
                <a href="{{ route('login') }}" class="btn-outline py-2.5 text-center text-xs">Login</a>
                <a href="{{ route('register') }}" class="btn py-2.5 text-center text-xs">Register</a>
            </div>
        @endauth
        <a href="/contact" class="border-b border-slate-100 py-3 hover:text-brand">Contact</a>
    </div>
    <h3 class="mb-2 mt-6 text-xs font-semibold text-slate-500">Categories</h3>
    <ul class="text-sm">
        @foreach ($cats as $c)
            <li><a href="/shop?cat={{ $c['slug'] }}" class="flex w-full items-center gap-3 py-2.5 text-start">{{ $c['emoji'] }} {{ $c['en'] }}</a></li>
        @endforeach
    </ul>
    <div class="mt-6 border-t border-slate-100 pt-4">
        <p class="mb-2 text-xs font-semibold text-slate-500">{{ __('shop.language') }}</p>
        <div class="flex flex-wrap gap-2">
            @php
                use App\Models\SiteLocale;
                $currentLocale = $appLocale ?? app()->getLocale();
                $drawerLocales = SiteLocale::active();
                if (empty($drawerLocales)) {
                    $codes = $availableLocales ?? ['bn'];
                    $names = ['en' => 'English', 'bn' => 'বাংলা', 'ar' => 'العربية'];
                    $drawerLocales = array_map(fn ($c) => (object) ['code' => $c, 'native_name' => $names[$c] ?? $c], $codes);
                }
            @endphp
            @foreach ($drawerLocales as $locale)
                <a href="{{ route('locale.switch', $locale->code) }}" hreflang="{{ $locale->code }}" class="rounded-full px-3 py-1.5 text-xs font-semibold {{ $locale->code === $currentLocale ? 'bg-brand text-white' : 'bg-slate-100 text-ink hover:bg-brand/10 hover:text-brand' }}">{{ $locale->native_name }}</a>
            @endforeach
        </div>
    </div>
</aside>

<aside id="drawer" data-mode="cart" class="fixed inset-y-0 right-0 z-50 flex w-full max-w-sm translate-x-full flex-col bg-white shadow-2xl transition-transform duration-300" aria-label="Cart" inert>
    <div class="flex items-center justify-between border-b border-slate-100 p-4">
        <h2 id="dTitle" class="text-lg font-extrabold">Your Cart</h2>
        <button data-act="close" class="grid size-9 place-items-center rounded-full bg-slate-100 hover:bg-brand hover:text-white" aria-label="Close">
            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18"/></svg>
        </button>
    </div>
    <div id="dBody" class="flex-1 overflow-y-auto p-4">
        <p class="py-16 text-center text-slate-500">Your cart is empty.<br>Add a product to get started.</p>
    </div>
    <div id="dFoot" class="border-t border-slate-100 p-4"></div>
    <div class="flex gap-2 border-t border-slate-100 p-4 pt-0">
        <a href="/cart" class="btn-outline flex-1 py-2.5 text-xs">View Cart</a>
        <a href="/checkout" class="btn flex-1 py-2.5 text-xs">Checkout →</a>
    </div>
</aside>

<script>
// Drawer renderer uses the static catalogue injected per page via window.CB_PRODUCTS.
const cbThumb = (p, cls) => window.cbHelpers?.cbImg(p, cls)
    ?? `<span class="${cls} grid shrink-0 place-items-center rounded-lg bg-slate-50 text-3xl">${p.emoji}</span>`;
function renderDrawer() {
    const body = document.getElementById('dBody');
    const foot = document.getElementById('dFoot');
    const title = document.getElementById('dTitle');
    if (!body || !foot) {
        return;
    }
    const store = window.ChinaBay;
    const catalog = window.CB_PRODUCTS || {};
    const mode = document.getElementById('drawer')?.dataset.mode || 'cart';

    if (mode === 'wish') {
        title.textContent = 'Your Wishlist';
        foot.innerHTML = '';
        if (!store.wish.length) {
            body.innerHTML = '<p class="py-16 text-center text-slate-500">No saved items yet.<br>Tap ♡ on a product to save it.</p>';
            return;
        }
        body.innerHTML = store.wish.map((id) => {
            const p = catalog[id];
            if (!p) {
                return '';
            }
            return `<div class="flex items-center gap-3 border-b border-slate-100 py-3">${cbThumb(p, 'size-14')}<div class="flex-1 text-xs"><b class="block">${p.name}</b><span class="font-semibold text-brand">৳ ${Number(p.price).toLocaleString()}</span></div><button data-add="${p.id}" class="btn px-3 py-1.5 text-[11px]">Add</button></div>`;
        }).join('');
        return;
    }

    title.textContent = 'Your Cart';
    const entries = Object.entries(store.cart);
    if (!entries.length) {
        body.innerHTML = '<p class="py-16 text-center text-slate-500">Your cart is empty.<br>Add a product to get started.</p>';
        foot.innerHTML = '';
        return;
    }
    let sub = 0;
    body.innerHTML = entries.map(([id, q]) => {
        const p = catalog[id];
        if (!p) {
            return '';
        }
        sub += p.price * q;
        return `<div class="flex items-center gap-3 border-b border-slate-100 py-3">${cbThumb(p, 'size-14')}
        <div class="min-w-0 flex-1 text-xs"><b class="block truncate">${p.name}</b><span class="text-brand font-semibold">৳ ${Number(p.price).toLocaleString()}</span>
        <div class="mt-1.5 inline-flex items-center rounded border border-slate-200"><button data-qty="${p.id}" data-d="-1" class="px-2 py-0.5" aria-label="Decrease">−</button><span class="w-6 text-center">${q}</span><button data-qty="${p.id}" data-d="1" class="px-2 py-0.5" aria-label="Increase">+</button></div></div>
        <button data-rm="${p.id}" class="text-slate-400 hover:text-brand" aria-label="Remove">🗑</button></div>`;
    }).join('');
    foot.innerHTML = `<div class="mb-3 flex justify-between font-semibold"><span>Subtotal</span><span>৳ ${sub.toLocaleString()}</span></div><a href="/checkout" class="btn w-full py-3 text-sm">Checkout · Cash on Delivery</a>`;
    const drawer = document.getElementById('drawer');
    drawer?.setAttribute('data-mode', 'cart');
    // Keep drawerMode in app.js in sync when opened via header
    document.addEventListener('chinabay:sync', () => {}, { once: true });
}
function renderGridCounts() {
    document.querySelectorAll('[data-add]').forEach((b) => {
        const n = window.ChinaBay.cart[b.dataset.add] || 0;
        const base = b.dataset.label || b.textContent.replace(/\s*\(\d+\)\s*$/, '').trim() || 'Add to Cart';
        b.dataset.label = base.replace(/^🛒\s*/, '🛒 ');
        if (b.classList.contains('w-full') && n) {
            b.textContent = `🛒 Add to Cart (${n})`;
        }
    });
    document.querySelectorAll('[data-wish]').forEach((b) => {
        const active = window.ChinaBay.wish.map(String).includes(String(b.dataset.wish));
        b.textContent = active ? '♥' : '♡';
        b.classList.toggle('text-brand', active);
    });
}
document.addEventListener('chinabay:sync', renderGridCounts);
// Drawer mode switching: header buttons set mode then open
document.addEventListener('click', (e) => {
    const t = e.target.closest('[data-act]');
    if (!t) {
        return;
    }
    if (t.dataset.act === 'cart') {
        document.getElementById('drawer')?.setAttribute('data-mode', 'cart');
    }
    if (t.dataset.act === 'wishlist') {
        document.getElementById('drawer')?.setAttribute('data-mode', 'wish');
    }
});
</script>
