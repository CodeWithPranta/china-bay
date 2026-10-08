@extends('layouts.app')
@section('title', 'Loyalty Points — ChinaBay')
@section('content')
<div class="flex flex-col gap-4 py-6 lg:flex-row">
    <x-account.sidebar />
    <div class="min-w-0 flex-1 space-y-4">
        <div class="card bg-gradient-to-r from-brand to-brand-dark text-center text-white">
            <p class="text-xs opacity-80">🪙 Points Balance</p><b class="text-4xl">120</b><p class="text-xs opacity-80">= ৳120 discount · Gold tier · 80 pts to Platinum</p>
            <div class="mx-auto mt-2 h-2 max-w-xs overflow-hidden rounded-full bg-white/30"><div class="h-full w-3/5 rounded-full bg-white"></div></div>
        </div>
        <div class="card text-xs">
            <h2 class="mb-2 font-bold">Earn / Burn History</h2>
            <table class="w-full"><tbody>
                @foreach ([['+20','Order CB-1003 delivered','24 Sep 2026','text-emerald-600'],['−50','Redeemed on CB-1002','02 Oct 2026','text-red-500'],['+30','Referral bonus — Karim joined','18 Sep 2026','text-emerald-600'],['+10','Product review approved','10 Sep 2026','text-emerald-600']] as [$p,$t,$d,$c])
                    <tr class="border-t border-slate-100"><td class="py-2 font-bold {{ $c }}">{{ $p }}</td><td>{{ $t }}</td><td class="text-right text-slate-400">{{ $d }}</td></tr>
                @endforeach
            </tbody></table>
        </div>
        <div class="card text-xs text-slate-600"><h2 class="mb-1.5 font-bold text-ink">🎁 Referral Bonus — কীভাবে পাবেন?</h2><ul class="list-disc space-y-1 pl-4"><li>প্রতি বন্ধু জয়েন করলে <b>+30 pts</b> (৳30)।</li><li>বন্ধু প্রথম অর্ডার করলে আরও <b>+20 pts</b>।</li><li>100 pts = ৳100 ছাড়, min order ৳500। Points expire after 12 months.</li></ul></div>
    </div>
</div>
@endsection
