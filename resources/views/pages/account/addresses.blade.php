@extends('layouts.app')
@section('title', 'Addresses — ChinaBay')
@section('content')
<div class="flex flex-col gap-4 py-6 lg:flex-row" x-data="{ form: false }">
    <x-account.sidebar />
    <div class="min-w-0 flex-1 space-y-4">
        <div class="flex items-center justify-between"><h1 class="text-lg font-extrabold">Saved Addresses 📍</h1><button @click="form = !form" class="btn px-4 py-2 text-xs">+ Add New</button></div>
        <div class="grid gap-3 md:grid-cols-2">
            @foreach ([['Home 🏠','Rahim Uddin · 01600 000000','House 12, Road 5, Dhanmondi, Dhaka 1205',true],['Office 🏢','Rahim Uddin · 01700 000000','Level 7, Gulshan Avenue, Dhaka 1212',false]] as [$t,$p,$a,$d])
                <div class="card text-xs">
                    <div class="flex items-center justify-between"><b>{{ $t }}</b>@if($d)<span class="rounded-full bg-emerald-100 px-2 py-0.5 font-semibold text-emerald-700">Default</span>@endif</div>
                    <p class="mt-1.5 text-slate-600">{{ $p }}<br>{{ $a }}</p>
                    <div class="mt-2.5 flex gap-2"><button @click="form = true" class="btn-outline px-3 py-1.5">Edit</button><button onclick="window.cbHelpers.toast('Address removed')" class="px-3 py-1.5 text-red-500 hover:underline">Delete</button></div>
                </div>
            @endforeach
        </div>
        <div x-show="form" class="card">
            <h2 class="font-bold">Add / Edit Address</h2>
            <div class="mt-3 grid gap-3 text-sm sm:grid-cols-2">
                <div><label class="label">Name</label><input class="input" placeholder="Receiver name"></div>
                <div><label class="label">Phone</label><input class="input" placeholder="01XXXXXXXXX"></div>
                <div><label class="label">District / জেলা</label><select class="input"><option>Dhaka</option><option>Chattogram</option><option>Sylhet</option><option>Khulna</option><option>Rajshahi</option></select></div>
                <div><label class="label">Thana / থানা</label><input class="input" placeholder="Dhanmondi"></div>
                <div class="sm:col-span-2"><label class="label">Full Address</label><textarea class="input" rows="2" placeholder="House, road, area..."></textarea></div>
                <label class="flex items-center gap-2 text-xs sm:col-span-2"><input type="checkbox" class="accent-[#c8102e]"> Set as default address</label>
            </div>
            <div class="mt-3 flex gap-2"><button @click="form = false; window.cbHelpers.toast('Address saved')" class="btn px-5 py-2.5 text-xs">Save Address</button><button @click="form = false" class="btn-outline px-5 py-2.5 text-xs">Cancel</button></div>
        </div>
    </div>
</div>
@endsection
