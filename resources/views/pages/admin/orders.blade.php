@extends('layouts.admin')
@section('title', 'Orders — ChinaBay Admin')
@section('heading', 'Orders')
@section('content')
@php
$orders = [
    ['CB-1008','Rahim Uddin','01711-222333','Dhaka',2,'৳3,998','COD','Pending'],
    ['CB-1007','Nusrat Jahan','01811-444555','Chattogram',1,'৳1,999','bKash','Confirmed'],
    ['CB-1006','Tanvir Ahmed','01911-666777','Sylhet',3,'৳5,197','COD','Shipped'],
    ['CB-1005','Mim Akter','01611-888999','Dhaka',1,'৳899','Nagad','Delivered'],
    ['CB-1004','Arif Hossain','01511-000111','Khulna',2,'৳4,398','COD','Pending'],
    ['CB-1003','Sadia Rahman','01311-222444','Rajshahi',1,'৳2,999','bKash','Cancelled'],
    ['CB-1002','Karim Sheikh','01411-333555','Barishal',4,'৳7,596','SSLCommerz','Delivered'],
    ['CB-1001','Farhana Islam','01799-112233','Dhaka',1,'৳2,999','COD','Confirmed'],
];
@endphp
<div x-data="{ toast: '' }">
    <div class="card flex flex-col gap-2 sm:flex-row">
        <input class="input sm:max-w-xs" placeholder="🔍 Search order / phone / customer…">
        <select class="input sm:max-w-44"><option>All statuses</option><option>Pending</option><option>Confirmed</option><option>Shipped</option><option>Delivered</option><option>Cancelled</option></select>
        <input type="date" class="input sm:max-w-44">
        <button class="btn px-4 py-2.5 text-sm">Filter</button>
    </div>
    <div class="card mt-4 overflow-x-auto p-0">
        <table class="w-full min-w-[820px] text-left text-sm">
            <thead class="bg-slate-50 text-xs text-slate-500"><tr><th class="px-4 py-3">Order</th><th>Customer</th><th>District</th><th>Items</th><th>Total</th><th>Payment</th><th>Status</th><th class="text-right">Actions</th></tr></thead>
            <tbody>
            @foreach ($orders as [$id,$c,$ph,$d,$n,$t,$pay,$s])
                <tr class="border-t border-slate-100 hover:bg-slate-50/60">
                    <td class="px-4 py-3 font-bold text-brand"><a href="/admin/orders/CB-1001" class="hover:underline">{{ $id }}</a></td>
                    <td class="px-2 py-3">{{ $c }}<span class="block text-[11px] text-slate-500">{{ $ph }}</span></td>
                    <td class="px-2 py-3">{{ $d }}</td><td class="px-2 py-3">{{ $n }}</td><td class="px-2 py-3 font-bold">{{ $t }}</td>
                    <td class="px-2 py-3"><span class="rounded bg-slate-100 px-2 py-0.5 text-[11px] font-bold">{{ $pay }}</span></td>
                    <td class="px-2 py-3"><span class="rounded-full px-2 py-0.5 text-[11px] font-bold {{ $s==='Pending'?'bg-amber-100 text-amber-700':($s==='Delivered'?'bg-emerald-100 text-emerald-700':($s==='Cancelled'?'bg-red-100 text-brand':'bg-sky-100 text-sky-700')) }}">{{ $s }}</span></td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex justify-end gap-1">
                            <a href="/admin/orders/CB-1001" class="btn-outline px-2.5 py-1.5 text-xs">View</a>
                            <button @click="toast='{{ $id }} confirmed ✓'" class="rounded-md bg-emerald-600 px-2.5 py-1.5 text-xs font-bold text-white">✓</button>
                            <button @click="toast='{{ $id }} cancelled'" class="rounded-md bg-brand px-2.5 py-1.5 text-xs font-bold text-white">✕</button>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-3 flex items-center justify-between text-sm">
        <p class="text-slate-500">Showing 8 of 36 orders</p>
        <div class="flex gap-1"><span class="rounded-md bg-navy px-3 py-1.5 font-bold text-white">1</span><span class="btn-outline px-3 py-1.5">2</span><span class="btn-outline px-3 py-1.5">3</span></div>
    </div>
    <div x-show="toast" x-transition class="fixed bottom-5 right-5 z-50 rounded-lg bg-navy px-4 py-3 text-sm font-bold text-white shadow-lg" x-text="toast" @click="toast=''" x-cloak></div>
</div>
@endsection
