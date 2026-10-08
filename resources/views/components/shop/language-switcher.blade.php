@php
    $current = $appLocale ?? app()->getLocale();
    $locales = $availableLocales ?? config('app.available_locales', ['en']);
    $names = ['en' => 'English', 'bn' => 'বাংলা', 'ar' => 'العربية'];
@endphp
<div x-data="{ open: false }" class="relative" @keydown.escape.window="open = false">
    <button
        type="button"
        @click="open = !open"
        aria-haspopup="listbox"
        :aria-expanded="open.toString()"
        aria-label="{{ __('shop.language') }}"
        class="inline-flex items-center gap-1.5 rounded-full bg-white/15 px-3 py-1.5 text-[11px] font-semibold text-white transition hover:bg-white/25 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white"
    >
        <span aria-hidden="true">🌐</span>
        <span>{{ $names[$current] ?? strtoupper($current) }}</span>
        <svg class="size-3 transition-transform" :class="{ 'rotate-180': open }" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="m6 9 6 6 6-6"/></svg>
    </button>
    <ul
        x-show="open"
        x-cloak
        x-transition.opacity.duration.150ms
        @click.outside="open = false"
        role="listbox"
        aria-label="{{ __('shop.language') }}"
        class="absolute end-0 top-full z-50 mt-2 w-36 overflow-hidden rounded-lg border border-slate-100 bg-white py-1 text-ink shadow-xl"
    >
        @foreach ($locales as $locale)
            <li role="option" aria-selected="{{ $locale === $current ? 'true' : 'false' }}">
                <a
                    href="{{ route('locale.switch', $locale) }}"
                    hreflang="{{ $locale }}"
                    class="flex items-center justify-between gap-2 px-3 py-2 text-start text-xs hover:bg-slate-50 hover:text-brand {{ $locale === $current ? 'font-bold text-brand' : 'font-medium' }}"
                >
                    <span>{{ $names[$locale] ?? strtoupper($locale) }}</span>
                    @if ($locale === $current)
                        <span aria-hidden="true">✓</span>
                    @endif
                </a>
            </li>
        @endforeach
    </ul>
    <noscript>
        <span class="ms-2 inline-flex gap-1">
            @foreach ($locales as $locale)
                <a href="{{ route('locale.switch', $locale) }}" hreflang="{{ $locale }}" class="underline">{{ $names[$locale] ?? $locale }}</a>
            @endforeach
        </span>
    </noscript>
</div>
