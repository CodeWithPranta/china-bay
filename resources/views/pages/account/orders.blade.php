@extends('layouts.app')
@section('title', 'My Orders — ChinaBay')
@section('content')
<div class="flex flex-col gap-4 py-6 lg:flex-row" x-data="{ f: 'all' }">
    <x-account.sidebar />
    <div class="min-w-0 flex-1">
        <div class="card">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <h1 class="text-lg font-extrabold">My Orders 📦</h1>
                <select x-model="f" class="input !w-auto text-xs"><option value="all">All orders</option><option>Placed</option><option>Confirmed</option><option>Processing</option><option>Shipped</option><option>Out for Delivery</option><option>Delivered</option></select>
            </div>
            <div class="mt-3 space-y-2.5">
                @php $orders = [['CB-1001','08 Oct 2026','৳ 4,618','Shipped','bg-sky-100 text-sky-700'],['CB-1002','02 Oct 2026','৳ 1,999','Processing','bg-amber-100 text-amber-700'],['CB-1003','24 Sep 2026','৳ 2,649','Delivered','bg-emerald-100 text-emerald-700'],['CB-1004','10 Sep 2026','৳ 899','Out for Delivery','bg-violet-100 text-violet-700'],['CB-1005','28 Aug 2026','৳ 7,399','Confirmed','bg-indigo-100 text-indigo-700'],['CB-1006','12 Aug 2026','৳ 1,299','Placed','bg-slate-100 text-slate-600']]; @endphp
                @foreach ($orders as [$id,$d,$t,$s,$c])
                    <div class="flex flex-wrap items-center gap-2 rounded-lg border border-slate-100 p-3 text-xs" x-show="f==='all' || f==='{{ $s }}'">
                        <div class="min-w-0 flex-1"><b>{{ $id }}</b> <span class="text-slate-400">· {{ $d }}</span><br><span class="font-semibold text-brand">{{ $t }}</span> <span class="rounded-full px-2 py-0.5 font-semibold {{ $c }}">{{ $s }}</span></div>
                        <a href="/account/orders/{{ $id }}" class="btn-outline px-3 py-1.5">View</a>
                        @if ($s !== 'Delivered')<button onclick="window.cbHelpers.toast('Cancel request sent')" class="rounded-md border border-red-100 px-3 py-1.5 text-red-600 hover:bg-red-50">Cancel</button>@endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection
