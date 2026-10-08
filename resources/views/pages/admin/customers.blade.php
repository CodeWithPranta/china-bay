@extends('layouts.admin')
@section('title', 'Customers — ChinaBay Admin')
@section('heading', 'Customers')
@section('content')
@php
$rows = [['Farhana Islam','01799-112233',6,'৳24,100','Gold','✓'],['Rahim Uddin','01711-222333',4,'৳15,400','Silver','✓'],['Nusrat Jahan','01811-444555',3,'৳8,900','Silver','✓'],['Tanvir Ahmed','01911-666777',5,'৳21,700','Gold','—'],['Mim Akter','01611-888999',2,'৳3,200','New','✓'],['Arif Hossain','01511-000111',1,'৳4,398','New','—'],['Sadia Rahman','01311-222444',3,'৳9,800','Silver','✓']];
@endphp
<div class="card flex gap-2"><input class="input sm:max-w-xs" placeholder="🔍 Search name / phone…"><button class="btn px-4 py-2.5 text-sm">Search</button></div>
<div class="card mt-4 overflow-x-auto p-0">
    <table class="w-full min-w-[720px] text-left text-sm">
        <thead class="bg-slate-50 text-xs text-slate-500"><tr><th class="px-4 py-3">Name</th><th>Phone</th><th>Orders</th><th>Spent</th><th>Loyalty</th><th>Verified</th></tr></thead>
        <tbody>@foreach ($rows as [$n,$ph,$o,$sp,$l,$v])<tr class="border-t border-slate-100"><td class="px-4 py-2.5 font-bold">{{ $n }}</td><td class="px-2 py-2.5">{{ $ph }}</td><td class="px-2 py-2.5">{{ $o }}</td><td class="px-2 py-2.5 font-bold">{{ $sp }}</td><td class="px-2 py-2.5"><span class="rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-bold text-amber-700">{{ $l }}</span></td><td class="px-2 py-2.5">{{ $v }}</td></tr>@endforeach</tbody>
    </table>
</div>
@endsection
