{{-- Conversational AI concierge. Backend-first: POSTs to /api/ai/chat
     (ShopConcierge agent over OpenRouter with live catalogue tools);
     falls back to the client-side recommend() when offline. --}}
<div x-data="cbAiAssistant()" @ai-search-open.window="openWith($event.detail?.q || '')" @keydown.escape.window="close()">
    <div x-show="open" x-cloak x-transition.opacity.duration.200ms @click="close()" class="fixed inset-0 z-[70] bg-black/50" aria-hidden="true"></div>

    <div x-show="open" x-cloak x-transition.transform.duration.200ms
        class="fixed inset-x-0 bottom-0 z-[71] mx-auto flex h-[85dvh] w-full flex-col overflow-hidden rounded-t-2xl bg-white shadow-2xl sm:inset-0 sm:m-auto sm:h-[600px] sm:max-w-lg sm:rounded-2xl"
        role="dialog" aria-modal="true" aria-label="Bay AI assistant">
        <div class="flex items-center gap-3 border-b border-slate-100 bg-brand px-4 py-3 text-white">
            <span class="grid size-9 shrink-0 place-items-center rounded-full bg-white/20 text-lg" aria-hidden="true">✨</span>
            <div class="min-w-0 flex-1 text-start">
                <b class="block text-sm">Bay AI · বে এআই</b>
                <span class="block truncate text-[11px] text-white/80" x-text="busy ? 'typing…' : 'Ask for products, prices, gifts'"></span>
            </div>
            <button @click="close()" class="grid size-9 shrink-0 place-items-center rounded-full bg-white/15 hover:bg-white/30" aria-label="Close assistant">
                <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18" /></svg>
            </button>
        </div>

        <div x-ref="thread" class="flex-1 space-y-3 overflow-y-auto bg-slate-50 px-4 py-4 text-sm">
            <template x-for="(m, i) in messages" :key="i">
                <div class="flex gap-2" :class="m.me ? 'flex-row-reverse' : ''">
                    <span x-show="!m.me" class="grid size-8 shrink-0 place-items-center rounded-full bg-brand/10 text-base" aria-hidden="true">✨</span>
                    <div class="max-w-[85%] rounded-2xl px-3.5 py-2.5 text-start shadow-sm"
                        :class="m.me ? 'ms-auto rounded-br-md bg-brand text-white' : 'rounded-bl-md bg-white text-ink'">
                        <p x-html="m.html"></p>
                    </div>
                </div>
            </template>
            <div x-show="busy" class="flex gap-2">
                <span class="grid size-8 shrink-0 place-items-center rounded-full bg-brand/10 text-base" aria-hidden="true">✨</span>
                <div class="flex items-center gap-1 rounded-2xl rounded-bl-md bg-white px-4 py-3 shadow-sm" aria-label="Assistant is typing">
                    <span class="size-1.5 animate-bounce rounded-full bg-slate-400"></span>
                    <span class="size-1.5 animate-bounce rounded-full bg-slate-400 [animation-delay:150ms]"></span>
                    <span class="size-1.5 animate-bounce rounded-full bg-slate-400 [animation-delay:300ms]"></span>
                </div>
            </div>
        </div>

        <div class="border-t border-slate-100 bg-white px-3 pb-3 pt-2">
            <div class="flex gap-2 overflow-x-auto pb-2 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
                <template x-for="chip in chips" :key="chip">
                    <button @click="ask(chip)" class="shrink-0 whitespace-nowrap rounded-full border border-slate-200 bg-slate-50 px-3 py-1.5 text-[11px] font-medium hover:border-brand hover:text-brand" x-text="chip"></button>
                </template>
            </div>
            <form @submit.prevent="send()" class="flex items-center gap-2">
                <input x-ref="box" x-model="input" type="text" autocomplete="off" placeholder="Type here… / এখানে লিখুন…" class="min-w-0 flex-1 rounded-full border border-slate-200 bg-slate-50 px-4 py-2.5 text-sm outline-none focus:border-brand" aria-label="Message Bay AI">
                <button type="button" data-voice aria-label="{{ __('shop.voice_search') }}" title="{{ __('shop.voice_search') }}" class="grid size-10 shrink-0 place-items-center rounded-full text-slate-400 transition hover:bg-brand/10 hover:text-brand">
                    <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2.5a3 3 0 0 0-3 3v6a3 3 0 0 0 6 0v-6a3 3 0 0 0-3-3Z" /><path d="M19 10.5v.5a7 7 0 0 1-14 0v-.5M12 18v3.5M8.5 21.5h7" /></svg>
                </button>
                <button class="grid size-10 shrink-0 place-items-center rounded-full bg-brand text-white transition hover:bg-brand-dark active:scale-95 disabled:opacity-40" :disabled="busy || !input.trim()" aria-label="Send message">
                    <svg class="size-5 rtl:-scale-x-100" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h13M13 6l6 6-6 6" /></svg>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
function cbAiAssistant() {
    const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => (
        { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const tk = (n) => '\u09F3 ' + Number(n).toLocaleString('en-US');

    function catalog() {
        try {
            return Object.values(window.CB_PRODUCTS || {});
        } catch {
            return [];
        }
    }

    function rich(text) {
        let h = esc(text);
        h = h.replace(/\[([^\]]+)\]\((https?:\/\/[^\s)]+|\/\S+?)\)/g, (m, label, url) => {
            let href = url.replace(/&amp;/g, '&');
            const self = href.match(/^https?:\/\/(?:www\.)?chinabaybd\.com(\/.*)?$/i);
            if (self) { href = self[1] || '/'; }
            return `<a class="font-bold text-brand underline" href="${href}">${label}</a>`;
        });
        h = h.replace(/https?:\/\/(?:www\.)?chinabaybd\.com(\/[^\s<]*)?/gi, (m, path) => {
            const href = path || '/';
            return `<a class="font-bold text-brand underline" href="${href}">${href}</a>`;
        });
        h = h.replace(/\n/g, '<br>');
        h = h.replace(/(^|[\s(])(\/(?:contact|shop|track-order|flash-sale|shipping|returns|about|cart|checkout|account(?:\/\S*)?))/g, '$1<a class="font-bold text-brand underline" href="$2">$2</a>');
        h = h.replace(/(\+880\d{9,11})/g, '<a class="font-bold text-brand underline" href="tel:$1">$1</a>');
        h = h.replace(/([\w.+-]+@[\w-]+\.[\w.]+)/g, '<a class="font-bold text-brand underline" href="mailto:$1">$1</a>');
        return h;
    }

    function card(p) {
        const thumb = window.cbHelpers?.cbImg(p, 'size-11') ?? `<span class="grid size-11 shrink-0 place-items-center rounded-lg bg-white text-2xl">${p.emoji}</span>`;
        return `<div class="mt-2 flex items-center gap-2.5 rounded-xl border border-slate-100 bg-slate-50/60 p-2 text-start">`
            + thumb
            + `<span class="min-w-0 flex-1"><b class="block truncate text-xs">${esc(p.name)}</b>`
            + `<span class="block text-[11px] font-bold text-brand">${tk(p.price)} <s class="font-normal text-slate-400">${tk(p.old)}</s></span></span>`
            + `<a href="/product/${p.slug}" class="shrink-0 rounded-md bg-brand px-2.5 py-1.5 text-[11px] font-bold text-white">View</a>`
            + `<button data-add="${p.id}" class="shrink-0 rounded-md border border-brand px-2.5 py-1.5 text-[11px] font-bold text-brand" aria-label="Add ${esc(p.name)} to cart">Add</button></div>`;
    }

    function recommend(q) {
        const all = catalog();
        if (!all.length) {
            return { html: `I can help once the catalogue loads here. Meanwhile try the shop: <a class="font-bold text-brand underline" href="/shop?q=${encodeURIComponent(q)}">search \u201C${esc(q)}\u201D</a>`, picks: [] };
        }
        const text = q.toLowerCase();
        let cap = null;
        const num = text.replace(/,/g, '').match(/(\d[\d\s]*)/);
        if (num && /(under|below|less|budget|max|within|kom|<|৳|taka|tk)/.test(text)) {
            cap = parseInt(num[1].replace(/\s/g, ''), 10);
        }
        const stop = new Set(['the', 'a', 'an', 'for', 'me', 'my', 'show', 'want', 'need', 'buy', 'under', 'below', 'taka', 'tk', 'pls', 'please', 'any', 'best', 'good', 'ta', 'ki', 'ache', 'dao', 'din', 'ekta']);
        const words = text.split(/[^a-z\u0980-\u09FF0-9]+/gi).filter((w) => w && !stop.has(w) && w.length > 1 && !/^\d+$/.test(w));
        const scored = all.map((p) => {
            const hay = `${p.name} ${p.sub} ${p.cat}`.toLowerCase();
            let s = 0;
            words.forEach((w) => { if (hay.includes(w)) { s += w.length > 4 ? 2 : 1; } });
            return [s, p];
        }).filter(([s, p]) => (s > 0 || !words.length) && (cap === null || p.price <= cap));
        scored.sort((a, b) => b[0] - a[0] || a[1].price - b[1].price);
        const picks = scored.slice(0, 3).map(([, p]) => p);
        if (!picks.length) {
            return { html: `Hmm, nothing matches \u201C${esc(q)}\u201D${cap ? ` under ${tk(cap)}` : ''} right now. Try fewer words — or <a class="font-bold text-brand underline" href="/shop">browse everything</a>.`, picks: [] };
        }
        const head = cap
            ? `Top picks under ${tk(cap)} for \u201C${esc(q)}\u201D:`
            : `Here is what I found for \u201C${esc(q)}\u201D:`;
        return { html: esc(head) + picks.map(card).join(''), picks };
    }

    return {
        open: false,
        busy: false,
        input: '',
        greeted: false,
        messages: [],
        chips: ['T-shirt in ৳1000', 'Gift for kids', 'Any discount?', 'Talk to human'],
        openWith(q) {
            this.open = true;
            document.body.classList.add('overflow-hidden');
            if (!this.greeted) {
                this.greeted = true;
                const who = window.CB_USER?.name ? ' ' + String(window.CB_USER.name).trim().split(/\s+/)[0] : '';
                const greet = `Assalamu Alaikum${who}! I am Bay AI ✨ — tell me what you are looking for (product, budget, gift…) and I will pick from our China-imported collection.`;
                this.messages.push({ me: false, html: esc(greet), text: greet });
            }
            if (q && q.trim()) {
                this.ask(q.trim());
            } else {
                this.$nextTick(() => this.$refs.box?.focus());
            }
        },
        close() {
            this.open = false;
            document.body.classList.remove('overflow-hidden');
        },
        ask(q) {
            this.pushUser(q);
            this.think(q);
        },
        send() {
            const q = this.input.trim();
            if (!q || this.busy) { return; }
            this.input = '';
            this.pushUser(q);
            this.think(q);
        },
        pushUser(q) {
            this.messages.push({ me: true, html: esc(q), text: q });
            this.scroll();
        },
        think(q) {
            if (/track.*order|order.*track|order.*status|kothay|kobe/i.test(q)) {
                const text = 'You can track any order at /track-order — just enter your order ID or phone number.';
                this.messages.push({ me: false, html: `You can track any order here: <a class="font-bold text-brand underline" href="/track-order">Track Order</a> — just enter your order ID or phone number.`, text });
                this.scroll();
                return;
            }
            this.backend(q);
        },
        pastTurns() {
            return this.messages.filter((m) => m.text).slice(-12).map((m) => ({
                role: m.me ? 'user' : 'assistant',
                content: String(m.text).slice(0, 1000),
            }));
        },
        async backend(q) {
            this.busy = true;
            this.scroll();
            try {
                const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
                const res = await fetch('/api/ai/chat', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': token },
                    body: JSON.stringify({ message: q, history: this.pastTurns() }),
                });
                if (!res.ok) { throw new Error('chat ' + res.status); }
                const data = await res.json();
                this.busy = false;
                const reply = String(data.reply || 'Sorry, I missed that — please try again.');
                const cards = (data.products || []).map(card).join('');
                const names = (data.products || []).map((p) => `\n- ${p.name} (${tk(p.price)})`).join('');
                this.messages.push({ me: false, html: rich(reply) + cards, text: reply + names });
            } catch {
                this.local(q);
                return;
            }
            this.scroll();
            window.ChinaBay?.sync?.();
            this.$nextTick(() => this.$refs.box?.focus());
        },
        local(q) {
            setTimeout(() => {
                const r = recommend(q);
                this.busy = false;
                const plain = r.html.replace(/<[^>]+>/g, '');
                this.messages.push({ me: false, html: r.html, text: plain });
                this.scroll();
                window.ChinaBay?.sync?.();
                this.$nextTick(() => this.$refs.box?.focus());
            }, 650);
        },
        scroll() {
            this.$nextTick(() => {
                const t = this.$refs.thread;
                if (t) { t.scrollTop = t.scrollHeight; }
            });
        },
    };
}
</script>
