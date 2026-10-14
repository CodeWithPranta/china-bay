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

// Voice typing (Web Speech API) for search, checkout fields + AI inputs.
// Dictation language: explicit user choice (localStorage cb_voice_lang,
// 'bn-BD' | 'en-US') wins, else follows <html lang>: bn -> bn-BD.
function voiceLang() {
    const saved = localStorage.getItem('cb_voice_lang');
    if (saved === 'bn-BD' || saved === 'en-US') {
        return saved;
    }
    const l = (document.documentElement.lang || 'en').toLowerCase();
    if (l.startsWith('bn')) {
        return 'bn-BD';
    }
    if (l.startsWith('ar')) {
        return 'ar-SA';
    }
    return 'en-US';
}

// --- Phone/voice normalization -------------------------------------------
// Speech recognition returns phone digits with spaces ("0 1 7 1 2 ..."),
// dashes, or localized digits / number words. Collapse to 01XXXXXXXXX so
// the checkout `pattern="01[0-9]{9}"` check passes after voice input.
function normalizeDigits(text) {
    const map = {
        '০': '0', '১': '1', '২': '2', '৩': '3', '৪': '4', '৫': '5', '৬': '6', '৭': '7', '৮': '8', '৯': '9',
        '٠': '0', '١': '1', '٢': '2', '٣': '3', '٤': '4', '٥': '5', '٦': '6', '٧': '7', '٨': '8', '٩': '9',
        '۰': '0', '۱': '1', '۲': '2', '۳': '3', '۴': '4', '۵': '5', '۶': '6', '۷': '7', '۸': '8', '۹': '9',
    };
    return String(text ?? '').replace(/[০-৯٠-٩۰-۹]/g, (d) => map[d] ?? d);
}

function phoneFromSpeech(text) {
    let s = normalizeDigits(String(text ?? '').toLowerCase());
    const words = [
        [/zero/g, '0'], [/\boh\b/g, '0'],
        [/\bশূন্য\b/g, '0'],
        [/\bone\b/g, '1'], [/\bএক\b/g, '1'],
        [/\btwo\b/g, '2'], [/\bto\b/g, '2'], [/\btoo\b/g, '2'], [/\bদুই\b/g, '2'],
        [/\bthree\b/g, '3'], [/\bতিন\b/g, '3'],
        [/\bfour\b/g, '4'], [/\bfor\b/g, '4'], [/\bচার\b/g, '4'],
        [/\bfive\b/g, '5'], [/\bপাঁচ\b/g, '5'],
        [/\bsix\b/g, '6'], [/\bছয়\b|\bছয়\b/g, '6'],
        [/\bseven\b/g, '7'], [/\bসাত\b/g, '7'],
        [/\beight\b/g, '8'], [/\bআট\b/g, '8'],
        [/\bnine\b/g, '9'], [/\bনয়\b|\bনয়\b/g, '9'],
    ];
    for (const [re, d] of words) {
        s = s.replace(re, d);
    }
    return s.replace(/\D+/g, '');
}

function isPhoneField(input) {
    if (!input) {
        return false;
    }
    return input.id === 'coPhone' || input.name === 'phone' || input.type === 'tel' || input.inputMode === 'tel' || input.getAttribute('inputmode') === 'tel';
}

function normalizePhoneField(input) {
    if (!input || !isPhoneField(input)) {
        return;
    }
    const digits = phoneFromSpeech(input.value).slice(0, 11);
    if (digits !== input.value) {
        input.value = digits;
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));
    }
}

// Strip spaces from a dictated phone number as the user types/pastes too,
// so gaps never survive until submit even if recognition already ended.
document.addEventListener('input', (e) => {
    if (e.target instanceof HTMLInputElement && isPhoneField(e.target)) {
        const cur = e.target.selectionStart;
        const clean = phoneFromSpeech(e.target.value).slice(0, 11);
        if (clean !== e.target.value) {
            e.target.value = clean;
            try {
                e.target.setSelectionRange(cur, cur);
            } catch {
                /* ignore */
            }
        }
    }
});

const SpeechAPI = window.SpeechRecognition || window.webkitSpeechRecognition;
let voiceRec = null;
let voiceBtn = null;

function setVoiceLang(lang) {
    localStorage.setItem('cb_voice_lang', lang);
    document.dispatchEvent(new CustomEvent('chinabay:voice-lang', { detail: lang }));
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
    // Explicit per-field target (checkout mic buttons) wins; otherwise fall
    // back to the first text input of the enclosing form (header search).
    const input = btn.dataset.voiceFor
        ? document.getElementById(btn.dataset.voiceFor)
        : btn.closest('form')?.querySelector('input[type="search"], input[type="text"], input:not([type])');
    if (!input) {
        return;
    }
    if (!SpeechAPI) {
        toast('Voice typing is not supported in this browser');
        return;
    }
    if (voiceRec) {
        const wasMine = voiceBtn === btn;
        stopVoice();
        if (wasMine) {
            return;
        }
    }
    const append = btn.dataset.voiceMode === 'append';
    const base = append ? input.value.trim() : '';
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
        if (isPhoneField(input)) {
            // Collapse "0 1 7 ..." / "zero one seven ..." to "017...".
            input.value = phoneFromSpeech(append && base ? base + ' ' + text : text).slice(0, 11);
        } else if (input.id === 'coCoupon') {
            input.value = normalizeDigits(text).replace(/\s+/g, '').toUpperCase();
        } else {
            input.value = append && base ? base + ' ' + text.trim() : text;
        }
        input.dispatchEvent(new Event('input', { bubbles: true }));
        input.dispatchEvent(new Event('change', { bubbles: true }));
    };
    rec.onend = () => stopVoice();
    rec.onerror = (e) => {
        stopVoice();
        if (e.error === 'not-allowed' || e.error === 'service-not-allowed') {
            toast('Microphone blocked — allow access and try again');
        } else if (e.error === 'no-speech') {
            toast('Did not hear anything — try again');
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

// Product thumbnail with emoji fallback. Single pattern for every
// client-rendered spot (drawer, cart, checkout, wishlist, AI search).
function cbImg(p, cls) {
    const safe = String(p.name || '').replace(/"/g, '&quot;');
    if (p.image) {
        return `<img src="${p.image}" alt="${safe}" loading="lazy" class="${cls} shrink-0 rounded-lg bg-slate-50 object-cover">`;
    }
    return `<span class="${cls} grid shrink-0 place-items-center rounded-lg bg-slate-50 text-4xl">${p.emoji || ''}</span>`;
}

// Expose for page-level scripts (home slider, product gallery, checkout calc).
window.cbHelpers = { $, $$, tk, toast, panel, closeAll, openDrawer, bump, voiceLang, setVoiceLang, cbImg, phoneFromSpeech, normalizePhoneField, isPhoneField };
window.addEventListener('DOMContentLoaded', () => window.ChinaBay.sync());
