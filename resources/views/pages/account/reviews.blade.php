@extends('layouts.app')
@section('title', 'Reviews — ChinaBay')
@section('content')
<div class="flex flex-col gap-4 py-6 lg:flex-row" x-data="{ open: null, stars: 5 }">
    <x-account.sidebar />
    <div class="min-w-0 flex-1 space-y-4">
        <h1 class="text-lg font-extrabold">My Reviews ★</h1>
        <div class="space-y-2.5">
            @foreach ([['🎧','TWS Wireless Earbuds','★★★★★','Sound quality excellent, delivery fast. ধন্যবাদ ChinaBay!','08 Oct 2026'],['🧴','Vitamin C Serum','★★★★☆','Original product, good packaging.','20 Sep 2026']] as [$e,$n,$s,$t,$d])
                <div class="card text-xs"><div class="flex items-center gap-2"><span class="text-2xl">{{ $e }}</span><b>{{ $n }}</b><span class="ml-auto text-amber-400">{{ $s }}</span></div><p class="mt-1.5 text-slate-600">{{ $t }}</p><p class="mt-1 text-[11px] text-slate-400">{{ $d }} · Verified purchase ✓</p></div>
            @endforeach
        </div>
        <h2 class="font-bold">Pending reviews (2)</h2>
        <div class="grid gap-3 md:grid-cols-2">
            @foreach ([['⌚','Smart Watch','CB-1001'],['🎒','Travel Backpack','CB-1003']] as $i => [$e,$n,$o])
                <div class="card text-xs">
                    <div class="flex items-center gap-2"><span class="text-2xl">{{ $e }}</span><div><b>{{ $n }}</b><br><span class="text-slate-400">{{ $o }}</span></div></div>
                    <button @click="open === {{ $i }} ? open = null : open = {{ $i }}" class="btn-outline mt-2.5 w-full py-2">✍️ Write Review</button>
                    <div x-show="open === {{ $i }}" class="mt-2.5 space-y-2 border-t border-slate-100 pt-2.5">
                        <div class="flex gap-1 text-xl">@for ($s = 1; $s <= 5; $s++)<button @click="stars = {{ $s }}" :class="stars >= {{ $s }} ? 'text-amber-400' : 'text-slate-200'">★</button>@endfor</div>
                        <textarea class="input" rows="2" placeholder="How was the product? / পণ্যটি কেমন ছিল?"></textarea>
                        <button @click="open = null; window.cbHelpers.toast('Review submitted — ধন্যবাদ!')" class="btn w-full py-2">Submit Review</button>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
