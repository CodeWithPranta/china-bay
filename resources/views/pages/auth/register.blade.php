@extends('layouts.app')
@section('title', 'Register — ChinaBay')
@section('content')
<div class="mx-auto max-w-md py-10">
    <div class="card p-6 sm:p-8">
        <h1 class="text-center text-2xl font-extrabold">Create <span class="text-brand">Account</span></h1>
        <p class="mt-1 text-center text-xs text-slate-500">একাউন্ট খুলুন · Get ৳50 coupon + 20 loyalty points</p>
        <form method="POST" action="/auth/register" class="mt-5 space-y-3">
            @csrf
            <div>
                <label class="label" for="regName">Full Name / নাম</label>
                <input id="regName" name="name" value="{{ old('name') }}" required autocomplete="name" class="input @error('name') !border-brand @enderror" placeholder="Rahim Uddin">
                @error('name') <p class="mt-1 text-[11px] font-semibold text-brand">{{ $message }}</p> @enderror
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="label" for="regPhone">Phone / মোবাইল</label>
                    <input id="regPhone" name="phone" value="{{ old('phone') }}" required autocomplete="tel" pattern="01[0-9]{9}" class="input @error('phone') !border-brand @enderror" placeholder="01XXXXXXXXX">
                    @error('phone') <p class="mt-1 text-[11px] font-semibold text-brand">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="label" for="regEmail">Email (optional)</label>
                    <input id="regEmail" name="email" type="email" value="{{ old('email') }}" autocomplete="email" class="input @error('email') !border-brand @enderror" placeholder="you@mail.com">
                    @error('email') <p class="mt-1 text-[11px] font-semibold text-brand">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="label" for="regPass">Password</label>
                    <input id="regPass" name="password" required type="password" autocomplete="new-password" class="input @error('password') !border-brand @enderror" placeholder="Min 6 chars">
                </div>
                <div>
                    <label class="label" for="regPass2">Confirm Password</label>
                    <input id="regPass2" name="password_confirmation" required type="password" autocomplete="new-password" class="input" placeholder="Repeat password">
                </div>
            </div>
            @error('password') <p class="text-[11px] font-semibold text-brand">{{ $message }}</p> @enderror
            <p class="rounded-lg bg-amber-50 p-2.5 text-[11px] text-amber-700">📩 OTP phone verification is coming soon — you can order right away.</p>
            <label class="flex items-start gap-2 text-xs text-slate-600"><input required type="checkbox" name="terms" value="1" class="mt-0.5 accent-[#c8102e]"><span>I agree to the <a href="/terms" class="text-brand">Terms</a> & <a href="/privacy" class="text-brand">Privacy Policy</a></span></label>
            <button class="btn w-full py-3 text-sm">Create Account →</button>
        </form>
        <p class="mt-4 text-center text-xs text-slate-500">Already have an account? <a href="/auth/login" class="font-semibold text-brand">Login</a></p>
    </div>
</div>
@endsection
