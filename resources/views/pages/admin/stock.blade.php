@extends('layouts.admin')
@section('title', 'Stock — ChinaBay Admin')
@section('heading', 'Stock')
@section('content')
@php $low = collect(\App\Support\Catalog::products())->sortBy('stock')->take(6); @endphp
<div x-data="{ toast: '' }">
    <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm"><b class="text-brand">⚠ {{ $low->count() }} products below safety stock (15).</b> Reorder suggested for Robot Vacuum Mini & Air Fryer.</div>
    <div class="card mt-4 overflow-x-auto p-0">
        <table class="w-full min-w-[680px] text-left text-sm">
            <thead class="bg-slate-50 text-xs text-slate-500"><tr><th class="px-4 py-3">Product</th><th>Variant</th><th>Current</th><th>Adjust</th><th class="text-right">Save</th></tr></thead>
            <tbody x-data>
            @foreach ($low as $p)
                <tr class="border-t border-slate-100" x-data="{ q: {{ $p['stock'] }} }">
                    <td class="px-4 py-2.5"><span class="text-xl">{{ $p['emoji'] }}</span> <b>{{ $p['name'] }}</b></td>
                    <td class="px-2 py-2.5 text-xs">Standard / Black</td>
                    <td class="px-2 py-2.5"><span class="rounded-full bg-red-100 px-2 py-0.5 text-[11px] font-bold text-brand" x-text="q"></span></td>
                    <td class="px-2 py-2.5"><div class="flex items-center gap-1"><button @click="q > 0 && q--" class="btn-outline size-7">−</button><input x-model="q" type="number" class="input w-16 !px-2 text-center"><button @click="q++" class="btn-outline size-7">+</button></div></td>
                    <td class="px-4 py-2.5 text-right"><button @click="$root.toast='Stock updated ✓'" class="btn px-3 py-1.5 text-xs">Save</button></td>
                </tr>
                <tr class="bg-slate-50/60 text-xs text-slate-500"><td class="px-8 py-1.5" colspan="5">↳ Variant: {{ $p['name'] }} — White · stock {{ max(2, intdiv($p['stock'], 2)) }} · SKU-V{{ $p['id'] }}W</td></tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div x-show="toast" x-transition class="fixed bottom-5 right-5 z-50 rounded-lg bg-navy px-4 py-3 text-sm font-bold text-white shadow-lg" x-text="toast" @click="toast=''" x-cloak></div>
</div>
@endsection
