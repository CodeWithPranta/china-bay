@extends('layouts.admin')
@section('title', 'Flash Sales — ChinaBay Admin')
@section('heading', 'Flash Sales')
@section('content')
@php $products = \App\Support\ShopDemo::products(); @endphp
<div x-data="{ toast: '', h: 5, m: 42, s: 10 }" x-init="setInterval(() => { if (s > 0) s--; else if (m > 0) { m--; s = 59 } else if (h > 0) { h--; m = 59; s = 59 } }, 1000)" class="grid gap-4 lg:grid-cols-3">
    <div class="space-y-4 lg:col-span-2">
        <div class="card !border-0 bg-navy text-white">
            <div class="flex items-center justify-between"><h3 class="font-extrabold">⚡ Live: 10.10 Gadget Blast</h3><span class="rounded-full bg-emerald-500 px-2 py-0.5 text-[11px] font-bold">LIVE</span></div>
            <p class="mt-1 font-mono text-2xl font-extrabold"><span x-text="String(h).padStart(2,'0')"></span>:<span x-text="String(m).padStart(2,'0')"></span>:<span x-text="String(s).padStart(2,'0')"></span></p>
            @foreach ([['TWS Earbuds',78],['Smart Watch',52],['Power Bank',91]] as [$n,$pc])
            <div class="mt-2 text-xs"><div class="flex justify-between"><span>{{ $n }}</span><b>{{ $pc }}% sold</b></div><div class="mt-1 h-2 rounded-full bg-white/20"><div class="h-2 rounded-full bg-brand" style="width:{{ $pc }}%"></div></div></div>
            @endforeach
        </div>
        <div class="card"><h3 class="font-bold">Create flash sale</h3><div class="mt-2 grid gap-2 text-sm sm:grid-cols-2"><input class="input" placeholder="Sale title…"><input type="datetime-local" class="input"><div class="sm:col-span-2"><label class="label">Products</label><div class="grid max-h-44 gap-1 overflow-y-auto rounded-lg border border-slate-100 p-2">@foreach ($products as $p)<label class="flex items-center gap-2 text-sm"><input type="checkbox" class="accent-[#c8102e]" {{ $p['id'] <= 3 ? 'checked' : '' }}> {{ $p['emoji'] }} {{ $p['name'] }} <span class="ml-auto font-bold">৳{{ number_format($p['price']) }}</span></label>@endforeach</div></div><button @click="toast='Flash sale scheduled ✓'" class="btn py-2.5 sm:col-span-2">Launch sale</button></div></div>
    </div>
    <div class="card h-fit"><h3 class="font-bold">Upcoming</h3><ul class="mt-2 space-y-2 text-sm"><li class="rounded-lg bg-slate-50 p-2">🎒 Travel Fest — Oct 12, 8PM</li><li class="rounded-lg bg-slate-50 p-2">💄 Beauty Hour — Oct 14, 12PM</li></ul></div>
    <div x-show="toast" x-transition class="fixed bottom-5 right-5 z-50 rounded-lg bg-navy px-4 py-3 text-sm font-bold text-white shadow-lg" x-text="toast" @click="toast=''" x-cloak></div>
</div>
@endsection
