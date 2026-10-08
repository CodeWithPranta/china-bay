@extends('layouts.admin')
@section('title', 'Reviews — ChinaBay Admin')
@section('heading', 'Reviews')
@section('content')
<div x-data="{ tab: 'pending', toast: '' }">
    <div class="flex gap-2"><button @click="tab='pending'" :class="tab==='pending'?'btn px-4 py-2 text-sm':'btn-outline px-4 py-2 text-sm'">Pending (4)</button><button @click="tab='approved'" :class="tab==='approved'?'btn px-4 py-2 text-sm':'btn-outline px-4 py-2 text-sm'">Approved (12)</button></div>
    <div x-show="tab==='pending'" class="mt-3 space-y-2">
        @foreach ([['Nusrat J.','TWS Earbuds','★★★★★','Sound onek valo, delivery fast chilo 👍'],['Arif H.','Smart Watch','★★★★☆','Battery backup 2 din jay, strap valo na'],['Mim A.','Vitamin C Serum','★★★★★','Original product, packing josss'],['Karim S.','Air Fryer','★★★☆☆','Daam beshi but ranna valo hoy']] as [$u,$p,$r,$t])
        <div class="card flex flex-col gap-2 sm:flex-row sm:items-center"><div class="flex-1"><b>{{ $u }}</b> <span class="text-xs text-slate-500">on {{ $p }}</span><p class="text-amber-500">{{ $r }}</p><p class="text-sm">{{ $t }}</p></div><div class="flex gap-1"><button @click="toast='Approved ✓'" class="rounded-md bg-emerald-600 px-3 py-1.5 text-xs font-bold text-white">Approve</button><button @click="toast='Rejected'" class="rounded-md bg-brand px-3 py-1.5 text-xs font-bold text-white">Reject</button></div></div>
        @endforeach
    </div>
    <div x-show="tab==='approved'" class="mt-3 space-y-2" x-cloak>
        @foreach ([['Sadia R.','Silk Saree','★★★★★','Saree ta asholei sundor 🥻'],['Tanvir A.','Power Bank','★★★★☆','Charging fast, original']] as [$u,$p,$r,$t])
        <div class="card"><b>{{ $u }}</b> <span class="text-xs text-slate-500">on {{ $p }}</span><p class="text-amber-500">{{ $r }}</p><p class="text-sm">{{ $t }}</p></div>
        @endforeach
    </div>
    <div x-show="toast" x-transition class="fixed bottom-5 right-5 z-50 rounded-lg bg-navy px-4 py-3 text-sm font-bold text-white shadow-lg" x-text="toast" @click="toast=''" x-cloak></div>
</div>
@endsection
