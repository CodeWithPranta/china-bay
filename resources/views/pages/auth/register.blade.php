@extends('layouts.app')
@section('title', 'Register — ChinaBay')
@section('content')
<div class="mx-auto max-w-md py-10">
    <div class="card p-6 sm:p-8">
        <h1 class="text-center text-2xl font-extrabold">Create <span class="text-brand">Account</span></h1>
        <p class="mt-1 text-center text-xs text-slate-500">একাউন্ট খুলুন · Get ৳50 coupon + 20 loyalty points</p>
        <form action="/auth/verify" class="mt-5 space-y-3" onsubmit="return true;">
            <div><label class="label">Full Name / নাম</label><input required class="input" placeholder="Rahim Uddin"></div>
            <div class="grid grid-cols-2 gap-2">
                <div><label class="label">Phone</label><input required class="input" placeholder="01XXXXXXXXX"></div>
                <div><label class="label">Email</label><input type="email" class="input" placeholder="you@mail.com"></div>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div><label class="label">Password</label><input required type="password" class="input" placeholder="Min 6 chars"></div>
                <div><label class="label">District / জেলা</label><select class="input"><option>Dhaka</option><option>Chattogram</option><option>Sylhet</option><option>Rajshahi</option><option>Khulna</option><option>Barishal</option><option>Rangpur</option><option>Mymensingh</option></select></div>
            </div>
            <p class="rounded-lg bg-amber-50 p-2.5 text-[11px] text-amber-700">📩 We will send a 6-digit OTP to verify your phone number.</p>
            <label class="flex items-start gap-2 text-xs text-slate-600"><input required type="checkbox" class="mt-0.5 accent-[#c8102e]"><span>I agree to the <a href="/terms" class="text-brand">Terms</a> & <a href="/privacy" class="text-brand">Privacy Policy</a></span></label>
            <button class="btn w-full py-3 text-sm">Register & Verify OTP →</button>
        </form>
        <p class="mt-4 text-center text-xs text-slate-500">Already have an account? <a href="/auth/login" class="font-semibold text-brand">Login</a></p>
    </div>
</div>
@endsection
