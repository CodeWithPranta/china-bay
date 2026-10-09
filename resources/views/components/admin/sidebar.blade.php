@php
    $menu = [
        ['Dashboard', '/admin', '◧'],
        ['Orders', '/admin/orders', '📦'],
        ['Products', '/admin/products', '🏷'],
        ['Stock / Inventory', '/admin/stock', '📊'],
        ['Customers', '/admin/customers', '👥'],
        ['Coupons & Campaigns', '/admin/coupons', '🎟'],
        ['Flash Sales', '/admin/flash-sales', '⚡'],
        ['Reviews', '/admin/reviews', '★'],
        ['Languages', '/admin/languages', '🌐'],
        ['Roles', '/admin/roles', '🛡'],
        ['Team', '/admin/users', '👤'],
        ['Settings', '/admin/settings', '⚙'],
    ];
@endphp
<aside x-show="sidebar" x-transition class="z-30 w-60 shrink-0 border-r border-slate-200 bg-navy text-white max-lg:fixed max-lg:inset-y-0 max-lg:left-0" aria-label="Admin menu">
    <div class="p-5">
        <a href="/admin" class="flex items-center gap-2" aria-label="ChinaBay admin"><x-shop.logo class="h-9 w-auto" /><span class="rounded bg-brand px-1.5 py-0.5 align-middle text-[10px] font-bold text-white">ADMIN</span></a>
        <p class="mt-1 text-[11px] text-slate-400">Sales · Stock · Orders</p>
    </div>
    <nav class="space-y-1 px-3 pb-6 text-sm">
        @foreach ($menu as [$t, $h, $i])
            <a href="{{ $h }}" class="flex items-center gap-3 rounded-lg px-3 py-2.5 font-medium {{ request()->is(ltrim($h, '/').'*') ? 'bg-brand text-white' : 'text-slate-300 hover:bg-white/10 hover:text-white' }}">
                <span>{{ $i }}</span> {{ $t }}
            </a>
        @endforeach
        <div class="mt-4 rounded-lg bg-white/5 p-3 text-[11px] text-slate-300">
            <b class="block text-white">2FA enabled ✓</b>
            Roles: Admin · Manager · Support
        </div>
    </nav>
</aside>
