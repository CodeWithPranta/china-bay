// ChinaBay storefront interactivity (design-preview phase).
// Alpine-ready + vanilla fallback. Later swap with Livewire actions
// keeping the same data-* hooks: data-add, data-wish, data-qty, data-rm, data-cat, data-act.

const $ = (s, r = document) => r.querySelector(s);
const $$ = (s, r = document) => [...r.querySelectorAll(s)];
const tk = (n) => '৳ ' + Number(n).toLocaleString('en-US');

const load = (k, d) => {
    try {
        return JSON.parse(localStorage.getItem(k)) ?? d;
    } catch {
        return d;
    }
};
const save = (k, v) => {
    try {
        localStorage.setItem(k, JSON.stringify(v));
    } catch {
        /* ignore */
    }
};

window.ChinaBay = {
    cart: load('cb_cart', {}),
    wish: load('cb_wish', []),
    coupon: load('cb_coupon', null),
    get qty() {
        return Object.values(this.cart).reduce((a, b) => a + b, 0);
    },
    sync() {
        save('cb_cart', this.cart);
        save('cb_wish', this.wish);
        save('cb_coupon', this.coupon);
        const c = $('#cCount');
        const w = $('#wCount');
        if (c) {
            c.textContent = this.qty;
        }
        if (w) {
            w.textContent = this.wish.length;
        }
        document.dispatchEvent(new CustomEvent('chinabay:sync', { detail: { cart: this.cart, wish: this.wish } }));
        if (typeof renderDrawer === 'function') {
            renderDrawer();
        }
        if (typeof renderGridCounts === 'function') {
            renderGridCounts();
        }
    },
};

let tt;
function toast(m) {
    const t = $('#toast');
    if (!t) {
        return;
    }
    t.textContent = m;
    t.classList.remove('opacity-0', 'translate-y-4');
    clearTimeout(tt);
    tt = setTimeout(() => t.classList.add('opacity-0', 'translate-y-4'), 1800);
}

function panel(el, open) {
    if (!el) {
        return;
    }
    el.toggleAttribute('inert', !open);
    el.classList.toggle(el.id === 'menu' ? '-translate-x-full' : 'translate-x-full', !open);
    $('#ovl')?.classList.toggle('hidden', !open);
    document.body.classList.toggle('overflow-hidden', open);
}

function closeAll() {
    panel($('#menu'), false);
    panel($('#drawer'), false);
}

let drawerMode = 'cart';
function openDrawer(mode) {
    drawerMode = mode;
    if (typeof renderDrawer === 'function') {
        renderDrawer();
    } else {
        const title = $('#dTitle');
        if (title) {
            title.textContent = mode === 'cart' ? 'Your Cart' : 'Your Wishlist';
        }
    }
    panel($('#drawer'), true);
    $('#drawer')?.setAttribute('data-mode', mode);
}

function bump() {
    const c = $('#cCount');
    if (!c) {
        return;
    }
    c.classList.add('scale-150');
    setTimeout(() => c.classList.remove('scale-150'), 200);
}

document.addEventListener('click', (e) => {
    const t = e.target.closest('button, a');
    if (!t) {
        $('#suggest')?.classList.add('hidden');
        return;
    }
    const d = t.dataset;
    const store = window.ChinaBay;

    if (d.add) {
        store.cart[d.add] = (store.cart[d.add] || 0) + 1;
        store.sync();
        bump();
        toast('Added to cart');
    } else if (d.wish) {
        const id = String(d.wish);
        store.wish = store.wish.includes(id) || store.wish.includes(+id)
            ? store.wish.filter((x) => String(x) !== id)
            : [...store.wish, isNaN(+id) ? id : +id];
        store.sync();
        toast('Wishlist updated');
    } else if (d.qty) {
        const id = d.qty;
        store.cart[id] = (store.cart[id] || 0) + Number(d.d || 1);
        if (store.cart[id] < 1) {
            delete store.cart[id];
        }
        store.sync();
    } else if (d.rm) {
        delete store.cart[d.rm];
        store.sync();
    } else if (d.cat && t.hasAttribute('data-go-home')) {
        window.location.href = '/shop?cat=' + d.cat;
        return;
    }

    switch (d.act) {
        case 'menu':
            panel($('#menu'), true);
            break;
        case 'close':
            closeAll();
            break;
        case 'cart':
            openDrawer('cart');
            break;
        case 'wishlist':
            openDrawer('wish');
            break;
        case 'msearch':
            $('#mSearch')?.classList.toggle('hidden');
            $('#mq')?.focus();
            break;
        case 'checkout':
            toast('Demo checkout — connect Livewire next');
            break;
    }
});

$('#ovl')?.addEventListener('click', closeAll);
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        closeAll();
        $('#suggest')?.classList.add('hidden');
    }
});

// Voice typing (Web Speech API) for manual search + AI assistant inputs.
// Dictation language follows <html lang>: bn -> bn-BD, ar -> ar-SA.
const SpeechAPI = window.SpeechRecognition || window.webkitSpeechRecognition;
let voiceRec = null;
let voiceBtn = null;

function voiceLang() {
    const l = (document.documentElement.lang || 'en').toLowerCase();
    if (l.startsWith('bn')) {
        return 'bn-BD';
    }
    if (l.startsWith('ar')) {
        return 'ar-SA';
    }
    return 'en-US';
}

function stopVoice() {
    try {
        voiceRec?.stop();
    } catch {
        /* already stopped */
    }
    voiceRec = null;
    voiceBtn?.classList.remove('bg-brand', 'text-white', 'animate-pulse');
    voiceBtn = null;
}

function startVoice(btn) {
    const input = btn.closest('form')?.querySelector('input[type="search"], input[type="text"]');
    if (!input) {
        return;
    }
    if (!SpeechAPI) {
        toast('Voice search is not supported in this browser');
        return;
    }
    if (voiceRec) {
        stopVoice();
        return;
    }
    const rec = new SpeechAPI();
    rec.lang = voiceLang();
    rec.interimResults = true;
    rec.maxAlternatives = 1;
    voiceRec = rec;
    voiceBtn = btn;
    btn.classList.add('bg-brand', 'text-white', 'animate-pulse');
    input.focus();
    rec.onresult = (e) => {
        let text = '';
        for (const r of e.results) {
            text += r[0].transcript;
        }
        input.value = text;
        input.dispatchEvent(new Event('input', { bubbles: true }));
    };
    rec.onend = () => stopVoice();
    rec.onerror = (e) => {
        stopVoice();
        if (e.error === 'not-allowed' || e.error === 'service-not-allowed') {
            toast('Microphone blocked — allow access and try again');
        }
    };
    try {
        rec.start();
    } catch {
        stopVoice();
    }
}

document.addEventListener('click', (e) => {
    const b = e.target.closest('[data-voice]');
    if (b) {
        e.preventDefault();
        startVoice(b);
    }
});

if (!SpeechAPI) {
    document.querySelectorAll('[data-voice]').forEach((b) => b.classList.add('hidden'));
}

// Expose for page-level scripts (home slider, product gallery, checkout calc).
window.cbHelpers = { $, $$, tk, toast, panel, closeAll, openDrawer, bump };
window.addEventListener('DOMContentLoaded', () => window.ChinaBay.sync());
