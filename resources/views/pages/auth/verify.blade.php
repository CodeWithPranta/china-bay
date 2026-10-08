@extends('layouts.app')
@section('title', 'Verify OTP — ChinaBay')
@section('content')
<div class="mx-auto max-w-md py-10" x-data="{ t: 60 }" x-init="setInterval(() => { if (t > 0) t-- }, 1000)">
    <div class="card p-6 text-center sm:p-8">
        <span class="mx-auto grid size-12 place-items-center rounded-full bg-brand/10 text-2xl">📩</span>
        <h1 class="mt-3 text-xl font-extrabold">Verify <span class="text-brand">OTP</span></h1>
        <p class="mt-1 text-xs text-slate-500">6-digit code sent to <b>01600 000000</b> · <a href="/auth/register" class="text-brand">Change</a></p>
        <div id="otpBoxes" class="mt-5 flex justify-center gap-2">
            @for ($i = 0; $i < 6; $i++)
                <input maxlength="1" inputmode="numeric" class="otp size-11 rounded-lg border border-slate-200 text-center text-lg font-bold outline-none focus:border-brand focus:ring-2 focus:ring-brand/15">
            @endfor
        </div>
        <a href="/account" class="btn mt-5 w-full py-3 text-sm">Verify & Continue →</a>
        <p class="mt-3 text-xs text-slate-500"><template x-if="t > 0"><span>Resend in <b x-text="t + 's'"></b></span></template><template x-if="t === 0"><button @click="t = 60; window.cbHelpers?.toast('OTP resent')" class="font-semibold text-brand">Resend OTP</button></template></p>
    </div>
</div>
@push('scripts')
<script>
const boxes = [...document.querySelectorAll('.otp')];
boxes.forEach((b, i) => {
    b.addEventListener('input', () => { b.value = b.value.replace(/\D/g, ''); if (b.value && i < 5) boxes[i + 1].focus(); });
    b.addEventListener('keydown', (e) => { if (e.key === 'Backspace' && !b.value && i > 0) boxes[i - 1].focus(); });
});
boxes[0]?.focus();
</script>
@endpush
@endsection
