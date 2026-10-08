@extends('layouts.app')
@section('title', 'Coupons — ChinaBay')
@section('content')
<div class="flex flex-col gap-4 py-6 lg:flex-row">
    <x-account.sidebar />
    <div class="min-w-0 flex-1 space-y-4">
        <h1 class="text-lg font-extrabold">My Coupons 🎟</h1>
        <div class="grid gap-3 md:grid-cols-3">
            @foreach ([['CHINABAY10','10% off','Min ৳500 · Exp 31 Dec 2026','bg-red-50 border-red-100'],['FLASH50','৳50 off','Min ৳999 · Exp 15 Oct 2026','bg-amber-50 border-amber-100'],['EID20','20% off','Min ৳2,000 · Exp 30 Nov 2026','bg-emerald-50 border-emerald-100']] as [$c,$t,$m,$bg])
                <div class="rounded-lg border border-dashed p-4 text-center {{ $bg }}">
                    <b class="text-lg tracking-widest">{{ $c }}</b><p class="text-xs font-semibold">{{ $t }}</p><p class="text-[11px] text-slate-500">{{ $m }}</p>
                    <button onclick="navigator.clipboard?.writeText('{{ $c }}');window.cbHelpers.toast('Copied {{ $c }}')" class="btn-outline mt-2 w-full py-1.5 text-xs">📋 Copy</button>
                </div>
            @endforeach
        </div>
        <div class="card flex flex-wrap items-center gap-3">
            <div class="flex-1 text-sm"><b>🎁 Refer & Earn ৳100</b><p class="text-xs text-slate-500">Share code <b class="text-brand">RAHIM100</b> — you + friend both get ৳100 off.</p></div>
            <button onclick="navigator.clipboard?.writeText('RAHIM100');window.cbHelpers.toast('Referral code copied')" class="btn-outline px-4 py-2 text-xs">Copy Code</button>
            <button onclick="window.cbHelpers.toast('Share link copied')" class="btn px-4 py-2 text-xs">Share →</button>
        </div>
    </div>
</div>
@endsection
