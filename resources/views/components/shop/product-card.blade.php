@props(['product'])
@php
    $off = $product['off'] ?? 0;
@endphp
<article class="relative flex flex-col rounded-lg border border-slate-100 p-3 shadow-sm transition hover:shadow-md">
    <span class="badge-off absolute left-2 top-2">-{{ $off }}%</span>
    <button data-wish="{{ $product['id'] }}" aria-label="Toggle wishlist" class="absolute right-2 top-1.5 text-xl text-slate-300 transition hover:scale-110 hover:text-brand">♡</button>
    <a href="/product/{{ $product['slug'] }}" class="grid h-28 place-items-center text-6xl" aria-label="{{ $product['name'] }}">{{ $product['emoji'] }}</a>
    <h3 class="mt-2 text-[11px] font-semibold leading-snug">
        <a href="/product/{{ $product['slug'] }}" class="hover:text-brand">{{ $product['name'] }}<br><span class="font-normal text-slate-500">{{ $product['sub'] }}</span></a>
    </h3>
    <p class="mt-1 text-[10px] text-slate-400"><s>৳ {{ number_format($product['old']) }}</s> <b class="text-sm text-brand">৳ {{ number_format($product['price']) }}</b></p>
    <p class="text-[10px] text-amber-400">★★★★★ <span class="text-slate-400">({{ $product['reviews'] }})</span></p>
    <div class="mt-auto flex gap-1.5 pt-2">
        <button data-add="{{ $product['id'] }}" class="btn w-full py-1.5 text-[11px]">🛒 Add to Cart</button>
    </div>
</article>
