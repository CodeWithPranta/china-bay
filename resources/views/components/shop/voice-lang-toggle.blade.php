{{-- Voice dictation language toggle (bn-BD / en-US). Persists to
     localStorage cb_voice_lang; app.js voiceLang() reads it. --}}
<div x-data="{ lang: localStorage.getItem('cb_voice_lang') || ((document.documentElement.lang || '').toLowerCase().startsWith('bn') ? 'bn-BD' : 'en-US') }"
    @chinabay:voice-lang.window="lang = $event.detail"
    class="flex items-center gap-2 rounded-lg bg-brand/[.06] px-3 py-2 text-xs">
    <span class="font-semibold text-brand">🎤 Voice · ভয়েস</span>
    <div class="flex rounded-full bg-white p-0.5 shadow-sm" role="group" aria-label="Dictation language">
        <button type="button" @click="window.cbHelpers.setVoiceLang('bn-BD')"
            :class="lang === 'bn-BD' ? 'bg-brand text-white' : 'text-slate-500 hover:text-brand'"
            class="rounded-full px-3 py-1 font-semibold transition">বাংলা</button>
        <button type="button" @click="window.cbHelpers.setVoiceLang('en-US')"
            :class="lang === 'en-US' ? 'bg-brand text-white' : 'text-slate-500 hover:text-brand'"
            class="rounded-full px-3 py-1 font-semibold transition">English</button>
    </div>
    <span class="hidden text-slate-500 sm:inline">Tap 🎤 on any field &amp; speak — no need to type</span>
</div>
