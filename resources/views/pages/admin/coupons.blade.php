@extends('layouts.admin')
@section('title', 'Coupons & Campaigns — ChinaBay Admin')
@section('heading', 'Coupons & Campaigns')
@section('content')
<div x-data="{ toast: '' }" class="grid gap-4 lg:grid-cols-3">
    <div class="space-y-4 lg:col-span-2">
        <div class="card overflow-x-auto p-0">
            <table class="w-full min-w-[520px] text-left text-sm">
                <thead class="bg-slate-50 text-xs text-slate-500"><tr><th class="px-4 py-3">Code</th><th>Off</th><th>Min order</th><th>Expiry</th><th>Used</th></tr></thead>
                <tbody>
                @foreach ([['EID20','20%','৳999','2026-04-10','312/500'],['WELCOME10','10%','৳499','2026-12-31','890/2000'],['FLASH50','৳50','৳1,499','2026-10-15','45/200']] as [$c,$o,$m,$e,$u])
                <tr class="border-t border-slate-100"><td class="px-4 py-2.5 font-bold text-brand">{{ $c }}</td><td class="px-2 py-2.5">{{ $o }}</td><td class="px-2 py-2.5">{{ $m }}</td><td class="px-2 py-2.5">{{ $e }}</td><td class="px-2 py-2.5">{{ $u }}</td></tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="grid gap-3 sm:grid-cols-2">
            @foreach ([['🎉 Eid Mega Sale','Up to 40% off sitewide','bg-brand'],['⚡ Weekend Gadget Fest','Extra 15% on gadgets','bg-navy']] as [$t,$d,$c])
            <div class="card {{ $c }} !border-0 text-white"><h3 class="font-extrabold">{{ $t }}</h3><p class="text-xs opacity-80">{{ $d }}</p><button @click="toast='Campaign toggled (demo)'" class="mt-2 rounded-md bg-white/20 px-3 py-1.5 text-xs font-bold">Toggle</button></div>
            @endforeach
        </div>
        <div class="card overflow-x-auto">
            <h3 class="mb-2 font-bold">🛒 Abandoned carts (3)</h3>
            <table class="w-full min-w-[480px] text-left text-sm"><tbody>
            @foreach ([['Rahim · 01711-222333','Smart Watch ×1','৳2,999'],['Mim · 01611-888999','Serum ×2','৳1,798'],['Arif · 01511-000111','Backpack ×1','৳2,199']] as [$c,$i,$t])
            <tr class="border-t border-slate-100"><td class="py-2">{{ $c }}<span class="block text-xs text-slate-500">{{ $i }}</span></td><td class="font-bold">{{ $t }}</td><td class="text-right"><button @click="toast='Reminder sent ✓'" class="btn-outline px-2.5 py-1.5 text-xs">Remind</button></td></tr>
            @endforeach
            </tbody></table>
        </div>
    </div>
    <div class="card h-fit"><h3 class="font-bold">Create coupon</h3><div class="mt-2 space-y-2 text-sm"><div><label class="label">Code</label><input class="input" placeholder="EID20"></div><div class="grid grid-cols-2 gap-2"><div><label class="label">% off</label><input type="number" class="input" value="10"></div><div><label class="label">Min order ৳</label><input type="number" class="input" value="999"></div></div><div><label class="label">Expiry</label><input type="date" class="input"></div><button @click="toast='Coupon created ✓'" class="btn w-full py-2.5">Create</button></div></div>
    <div x-show="toast" x-transition class="fixed bottom-5 right-5 z-50 rounded-lg bg-navy px-4 py-3 text-sm font-bold text-white shadow-lg" x-text="toast" @click="toast=''" x-cloak></div>
</div>
@endsection
