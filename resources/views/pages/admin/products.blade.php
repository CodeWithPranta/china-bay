@extends('layouts.admin')
@section('title', 'Products — ChinaBay Admin')
@section('heading', 'Products')
@section('content')
@php $products = \App\Support\Catalog::products(); $cats = \App\Support\Catalog::categories(); @endphp
<div x-data="{ open: false, toast: '' }">
    <div class="card flex flex-col gap-2 sm:flex-row">
        <input class="input sm:max-w-xs" placeholder="🔍 Search products…">
        <select class="input sm:max-w-52"><option>All categories</option>@foreach ($cats as $c)<option>{{ $c['en'] }}</option>@endforeach</select>
        <button @click="open = true" class="btn px-4 py-2.5 text-sm sm:ml-auto">+ Add product</button>
    </div>
    <div class="card mt-4 overflow-x-auto p-0">
        <table class="w-full min-w-[760px] text-left text-sm">
            <thead class="bg-slate-50 text-xs text-slate-500"><tr><th class="px-4 py-3">Product</th><th>Category</th><th>Price</th><th>Stock</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
            <tbody>
            @foreach ($products as $p)
                <tr class="border-t border-slate-100 hover:bg-slate-50/60">
                    <td class="px-4 py-2.5"><span class="flex items-center gap-2"><x-shop.product-thumb :product="$p" class="size-10" /> <span><b>{{ $p['name'] }}</b><span class="block text-[11px] text-slate-500">{{ $p['sub'] }}</span></span></span></td>
                    <td class="px-2 py-2.5 text-xs">{{ $p['cat'] }}</td>
                    <td class="px-2 py-2.5 font-bold">৳{{ number_format($p['price']) }} <span class="text-[11px] font-normal text-slate-400 line-through">৳{{ number_format($p['old']) }}</span></td>
                    <td class="px-2 py-2.5"><span class="rounded-full px-2 py-0.5 text-[11px] font-bold {{ $p['stock'] < 15 ? 'bg-red-100 text-brand' : 'bg-emerald-100 text-emerald-700' }}">{{ $p['stock'] }}</span></td>
                    <td class="px-2 py-2.5"><button @click="toast='Status toggled ✓'" class="relative inline-flex h-5 w-9 items-center rounded-full {{ $p['stock'] > 0 ? 'bg-emerald-500' : 'bg-slate-300' }}"><span class="size-4 translate-x-{{ $p['stock'] > 0 ? '4' : '1' }} rounded-full bg-white"></span></button></td>
                    <td class="px-4 py-2.5 text-right"><button @click="toast='Edit {{ $p['name'] }} (demo)'" class="btn-outline px-2.5 py-1.5 text-xs">Edit</button> <button @click="toast='Deleted (demo)'" class="rounded-md bg-red-50 px-2.5 py-1.5 text-xs font-bold text-brand">Del</button></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div x-show="open" class="fixed inset-0 z-40 flex justify-end bg-navy/50" x-cloak>
        <div x-show="open" x-transition class="w-full max-w-md overflow-y-auto bg-white p-5" @click.outside="open = false">
            <div class="flex items-center justify-between"><h3 class="font-extrabold">Add product</h3><button @click="open = false" class="btn-outline px-2.5 py-1.5 text-xs">✕</button></div>
            <div class="mt-3 space-y-3 text-sm">
                <div><label class="label">Title EN</label><input class="input" placeholder="Smart Watch"><button @click="toast='AI title: Smart Fit Watch Pro ⌚'" class="mt-1 text-xs font-bold text-brand">✨ AI title</button></div>
                <div><label class="label">Title BN</label><input class="input" placeholder="স্মার্ট ওয়াচ"><button @click="toast='বাংলা কপি তৈরি ✓'" class="mt-1 text-xs font-bold text-brand">✨ Bangla copy</button></div>
                <div class="grid grid-cols-2 gap-2"><div><label class="label">Category</label><select class="input"><option>Gadgets</option><option>Electronics</option></select></div><div><label class="label">Stock</label><input type="number" class="input" value="50"></div></div>
                <div class="grid grid-cols-2 gap-2"><div><label class="label">Price ৳</label><input type="number" class="input" value="2999"></div><div><label class="label">Discount %</label><input type="number" class="input" value="10"></div></div>
                <div><label class="label">Variants</label><div class="flex gap-2"><input class="input" placeholder="Black / M"><input class="input" placeholder="+৳0"></div><button @click="toast='Variant row added (demo)'" class="mt-1 text-xs font-bold text-brand">+ Add variant</button></div>
                <div><label class="label">Image</label><div class="grid h-20 place-items-center rounded-lg border-2 border-dashed border-slate-200 text-xs text-slate-400">📷 Drop image / placeholder</div></div>
                <div class="flex flex-wrap gap-2 text-xs font-bold"><button @click="toast='SEO keywords: smart watch, dam, fitness ✓'" class="btn-outline px-2.5 py-1.5">✨ SEO keywords</button><button @click="toast='Caption copied 📋'" class="btn-outline px-2.5 py-1.5">✨ Social caption</button></div>
                <button @click="open = false; toast='Product saved ✓'" class="btn w-full py-2.5">Save product</button>
            </div>
        </div>
    </div>
    <div x-show="toast" x-transition class="fixed bottom-5 right-5 z-50 rounded-lg bg-navy px-4 py-3 text-sm font-bold text-white shadow-lg" x-text="toast" @click="toast=''" x-cloak></div>
</div>
@endsection
