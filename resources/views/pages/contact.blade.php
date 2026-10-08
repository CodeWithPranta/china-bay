@extends('layouts.app')
@section('title', 'Contact — ChinaBay')
@section('content')
<div class="mx-auto max-w-4xl space-y-4 py-8">
    <div class="card text-center"><h1 class="text-2xl font-extrabold">Contact <span class="text-brand">Us</span></h1><p class="text-xs text-slate-500">যোগাযোগ করুন · Reply within 2 hours (9am–11pm)</p></div>
    <div class="grid gap-4 md:grid-cols-2">
        <div class="card"><h2 class="font-bold">✉️ Send a message</h2><div class="mt-2 space-y-2 text-sm"><input class="input" placeholder="Your name"><input class="input" placeholder="Phone / email"><textarea class="input" rows="3" placeholder="How can we help?"></textarea><button onclick="window.cbHelpers.toast('Message sent — ধন্যবাদ!')" class="btn w-full py-2.5 text-xs">Send →</button></div></div>
        <div class="space-y-3">
            <div class="card text-xs text-slate-600"><b class="text-ink">📞 +8801849415277</b><br>✉️ info@chinabaybd.com<br>🌐 www.chinabaybd.com<br>🏠 Dhaka, Bangladesh</div>
            <div class="grid grid-cols-2 gap-2">
                <a href="#" class="card text-center text-xs font-bold text-emerald-600">💬 WhatsApp<br><span class="font-normal text-slate-500">Chat now</span></a>
                <a href="#" class="card text-center text-xs font-bold text-blue-600">💬 Messenger<br><span class="font-normal text-slate-500">Chat now</span></a>
            </div>
            <div class="card grid h-36 place-items-center bg-slate-50 text-xs text-slate-400">🗺️ Map placeholder<br>Dhaka, Bangladesh</div>
        </div>
    </div>
</div>
@endsection
