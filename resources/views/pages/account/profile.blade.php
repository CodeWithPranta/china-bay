@extends('layouts.app')
@section('title', 'Profile — ChinaBay')
@section('content')
<div class="flex flex-col gap-4 py-6 lg:flex-row">
    <x-account.sidebar />
    <div class="min-w-0 flex-1 space-y-4">
        <div class="card">
            <h1 class="text-lg font-extrabold">Profile Settings 👤</h1>
            <div class="mt-3 grid gap-3 text-sm sm:grid-cols-2">
                <div><label class="label">Full Name</label><input class="input" value="Rahim Uddin"></div>
                <div><label class="label">Phone <span class="ml-1 rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-bold text-emerald-700">✓ Verified</span></label><input class="input" value="01600 000000"></div>
                <div><label class="label">Email</label><input class="input" value="rahim@mail.com"></div>
                <div class="grid grid-cols-2 gap-2">
                    <div><label class="label">Gender</label><select class="input"><option>Male</option><option>Female</option><option>Other</option></select></div>
                    <div><label class="label">Birthday</label><input type="date" class="input" value="1995-05-10"></div>
                </div>
                <div><label class="label">Language / ভাষা</label><select class="input"><option>বাংলা (bn)</option><option>English (en)</option></select></div>
            </div>
            <button onclick="window.cbHelpers.toast('Profile saved ✓')" class="btn mt-3 px-6 py-2.5 text-xs">Save Changes</button>
        </div>
        <div class="grid gap-4 md:grid-cols-2">
            <div class="card text-sm"><h2 class="font-bold">🔑 Change Password</h2><div class="mt-2 space-y-2"><input type="password" class="input" placeholder="Current password"><input type="password" class="input" placeholder="New password"><button onclick="window.cbHelpers.toast('Password updated')" class="btn w-full py-2.5 text-xs">Update Password</button></div></div>
            <div class="card text-sm" x-data="{ o: true, s: true, p: false }">
                <h2 class="font-bold">🔔 Notification Prefs</h2>
                <div class="mt-2 space-y-2.5 text-xs">
                    <button @click="o = !o" class="flex w-full items-center justify-between">Order updates <span :class="o ? 'bg-brand' : 'bg-slate-200'" class="relative h-5 w-10 rounded-full"><span :class="o ? 'translate-x-5' : ''" class="absolute top-0.5 left-0.5 size-4 rounded-full bg-white transition"></span></span></button>
                    <button @click="s = !s" class="flex w-full items-center justify-between">Offers & SMS <span :class="s ? 'bg-brand' : 'bg-slate-200'" class="relative h-5 w-10 rounded-full"><span :class="s ? 'translate-x-5' : ''" class="absolute top-0.5 left-0.5 size-4 rounded-full bg-white transition"></span></span></button>
                    <button @click="p = !p" class="flex w-full items-center justify-between">Price-drop alerts <span :class="p ? 'bg-brand' : 'bg-slate-200'" class="relative h-5 w-10 rounded-full"><span :class="p ? 'translate-x-5' : ''" class="absolute top-0.5 left-0.5 size-4 rounded-full bg-white transition"></span></span></button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
