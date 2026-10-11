@props(['for', 'mode' => 'replace', 'title' => 'Voice typing · ভয়েসে লিখুন'])
<button type="button" data-voice data-voice-for="{{ $for }}" data-voice-mode="{{ $mode }}"
    title="{{ $title }}" aria-label="{{ $title }}"
    class="grid size-8 shrink-0 place-items-center rounded-full text-slate-400 transition hover:bg-brand/10 hover:text-brand">
    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 2.5a3 3 0 0 0-3 3v6a3 3 0 0 0 6 0v-6a3 3 0 0 0-3-3Z" /><path d="M19 10.5v.5a7 7 0 0 1-14 0v-.5M12 18v3.5M8.5 21.5h7" /></svg>
</button>
