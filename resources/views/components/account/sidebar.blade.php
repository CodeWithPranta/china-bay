@php
    $menu = [
        ['Dashboard', '/account', '◧'],
        ['Orders', '/account/orders', '📦'],
        ['Addresses', '/account/addresses', '📍'],
        ['Wishlist', '/account/wishlist', '♡'],
        ['Reviews', '/account/reviews', '★'],
        ['Coupons', '/account/coupons', '🎟'],
        ['Loyalty Points', '/account/loyalty', '🪙'],
        ['Notifications', '/account/notifications', '🔔'],
        ['Profile', '/account/profile', '👤'],
    ];
@endphp
<aside class="card h-fit shrink-0 p-2 text-sm lg:w-60">
    <div class="flex items-center gap-3 rounded-lg bg-slate-50 p-3">
        <span class="grid size-11 place-items-center rounded-full bg-brand text-lg font-bold text-white">R</span>
        <div class="min-w-0">
            <b class="block truncate text-[13px]">Rahim Uddin</b>
            <span class="text-[11px] text-slate-500">01600 000000 · 120 pts</span>
        </div>
    </div>
    <nav class="mt-2 flex flex-row gap-1 overflow-x-auto lg:flex-col">
        @foreach ($menu as [$t, $h, $i])
            <a href="{{ $h }}" class="flex shrink-0 items-center gap-2 rounded-md px-3 py-2.5 font-medium {{ request()->is(ltrim($h, '/')) ? 'bg-brand/10 text-brand' : 'text-slate-600 hover:bg-slate-50 hover:text-brand' }}">
                <span>{{ $i }}</span> {{ $t }}
            </a>
        @endforeach
        <a href="/track-order" class="flex shrink-0 items-center gap-2 rounded-md px-3 py-2.5 text-slate-600 hover:bg-slate-50 hover:text-brand"><span>🔎</span> Track Order</a>
    </nav>
</aside>
