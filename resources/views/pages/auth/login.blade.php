@extends('layouts.app')
@section('title', 'Login — ChinaBay')
@section('content')
<div class="mx-auto max-w-md py-10" x-data="{ tab: 'password' }">
    <div class="card p-6 sm:p-8">
        <h1 class="text-center text-2xl font-extrabold">স্বাগতম <span class="text-brand">ChinaBay</span>-তে</h1>
        <p class="mt-1 text-center text-xs text-slate-500">Login to track orders, earn points & checkout faster</p>
        <div class="mt-5 grid grid-cols-2 rounded-full bg-slate-100 p-1 text-sm font-semibold">
            <button @click="tab='password'" :class="tab==='password' ? 'bg-white shadow text-brand' : 'text-slate-500'" class="rounded-full py-2">Password</button>
            <button @click="tab='otp'" :class="tab==='otp' ? 'bg-white shadow text-brand' : 'text-slate-500'" class="rounded-full py-2">OTP Login</button>
        </div>
        <div x-show="tab==='password'" class="mt-5 space-y-3">
            <div><label class="label">Phone / Email</label><input class="input" placeholder="01XXXXXXXXX or you@mail.com"></div>
            <div><label class="label">Password</label><input type="password" class="input" placeholder="••••••••"></div>
            <div class="flex justify-end text-xs"><a href="/auth/verify" class="text-brand hover:underline">Forgot password?</a></div>
            <a href="/account" class="btn w-full py-3 text-sm">Login →</a>
        </div>
        <div x-show="tab==='otp'" x-cloak class="mt-5 space-y-3">
            <div><label class="label">Mobile Number</label><input class="input" placeholder="01XXXXXXXXX"></div>
            <a href="/auth/verify" class="btn w-full py-3 text-sm">Send OTP →</a>
            <p class="text-center text-[11px] text-slate-500">OTP will arrive via SMS within 60 seconds</p>
        </div>
        <div class="my-4 flex items-center gap-2 text-[11px] text-slate-400"><span class="h-px flex-1 bg-slate-100"></span>or continue with<span class="h-px flex-1 bg-slate-100"></span></div>
        <div class="grid grid-cols-2 gap-2">
            <button class="btn-outline py-2.5 text-xs" onclick="toast('Google login — demo only')">G Google</button>
            <button class="btn-outline py-2.5 text-xs" onclick="toast('Facebook login — demo only')">f Facebook</button>
        </div>
        <p class="mt-4 text-center text-xs text-slate-500">New here? <a href="/auth/register" class="font-semibold text-brand">Create account</a></p>
        <p class="mt-3 rounded-lg bg-slate-50 p-2.5 text-center text-[11px] text-slate-500">🔒 2FA protected · OTP + password · নিরাপদ লগইন</p>
    </div>
</div>
<script>function toast(m){window.cbHelpers?.toast(m);}</script>
@endsection
