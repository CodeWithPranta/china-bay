@extends('layouts.admin')
@section('title', 'Settings — ChinaBay Admin')
@section('heading', 'Settings')
@section('content')
@php $zones = \App\Support\ShopDemo::deliveryZones(); @endphp
<div x-data="{ tab: 'delivery', toast: '' }">
    <div class="flex flex-wrap gap-2">
        @foreach (['delivery'=>'🚚 Delivery','payments'=>'💳 Payments','notify'=>'🔔 Notifications','roles'=>'👥 Roles & 2FA','backup'=>'🛡 Backup & Fraud'] as $k=>$l)
        <button @click="tab='{{ $k }}'" :class="tab==='{{ $k }}'?'btn px-3 py-2 text-xs':'btn-outline px-3 py-2 text-xs'">{{ $l }}</button>
        @endforeach
    </div>
    <div x-show="tab==='delivery'" class="card mt-3 overflow-x-auto">
        <h3 class="mb-2 font-bold">Delivery zones & charges (৳)</h3>
        <table class="w-full min-w-[480px] text-left text-sm"><thead class="text-xs text-slate-500"><tr><th class="py-2">District</th><th>Inside</th><th>Outside</th></tr></thead><tbody>
        @foreach ($zones as $z)<tr class="border-t border-slate-100"><td class="py-1.5 font-semibold">{{ $z['district'] }}</td><td><input type="number" value="{{ $z['charge_inside'] }}" class="input max-w-28 !py-1.5"></td><td><input type="number" value="{{ $z['charge_outside'] }}" class="input max-w-28 !py-1.5"></td></tr>@endforeach
        </tbody></table>
        <button @click="toast='Delivery rates saved ✓'" class="btn mt-3 px-4 py-2 text-sm">Save</button>
    </div>
    <div x-show="tab==='payments'" class="card mt-3 space-y-3 text-sm" x-cloak>
        @foreach ([['bKash','017XXXXXXXX','on'],['Nagad','018XXXXXXXX','on'],['SSLCommerz','store_id ••••','off']] as [$n,$k,$on])
        <div class="flex items-center gap-3 rounded-lg bg-slate-50 p-3"><b class="flex-1">{{ $n }}<span class="block text-xs font-normal text-slate-500">{{ $k }}</span></b><button @click="toast='{{ $n }} toggled (demo)'" class="relative inline-flex h-5 w-9 items-center rounded-full {{ $on==='on'?'bg-emerald-500':'bg-slate-300' }}"><span class="size-4 rounded-full bg-white"></span></button></div>
        @endforeach
        <button @click="toast='Payment keys saved ✓'" class="btn px-4 py-2 text-sm">Save keys</button>
    </div>
    <div x-show="tab==='notify'" class="card mt-3 space-y-3 text-sm" x-cloak>
        @foreach (['SMS (BulkSMSBD)','Email (SMTP)','WhatsApp API'] as $n)<div class="flex items-center gap-3 rounded-lg bg-slate-50 p-3"><b class="flex-1">{{ $n }}</b><button @click="toast='{{ $n }} toggled'" class="relative inline-flex h-5 w-9 items-center rounded-full bg-emerald-500"><span class="size-4 rounded-full bg-white"></span></button></div>@endforeach
        <div class="grid gap-2 sm:grid-cols-2"><div><label class="label">FB Pixel ID</label><input class="input" value="1234567890"></div><div><label class="label">GA4 ID</label><input class="input" value="G-ABC123"></div></div>
        <button @click="toast='Notification settings saved ✓'" class="btn px-4 py-2 text-sm">Save</button>
    </div>
    <div x-show="tab==='roles'" class="card mt-3 overflow-x-auto" x-cloak>
        <table class="w-full min-w-[480px] text-left text-sm"><thead class="text-xs text-slate-500"><tr><th class="py-2">User</th><th>Role</th><th>2FA</th></tr></thead><tbody>
        @foreach ([['Admin — admin@chinabay.com','Admin','✓'],['Manager — ops@chinabay.com','Manager','✓'],['Support — help@chinabay.com','Support','—']] as [$u,$r,$f])
        <tr class="border-t border-slate-100"><td class="py-2">{{ $u }}</td><td>{{ $r }}</td><td>{{ $f }}</td></tr>@endforeach
        </tbody></table>
        <button @click="toast='2FA enforced ✓'" class="btn-outline mt-3 px-4 py-2 text-sm">Enforce 2FA for all</button>
    </div>
    <div x-show="tab==='backup'" class="card mt-3 space-y-2 text-sm" x-cloak>
        <p>Last backup: <b>today 03:00 AM</b> · 42 MB</p>
        <div class="flex flex-wrap gap-2"><button @click="toast='Backup started (demo)'" class="btn px-4 py-2 text-sm">⬇ Backup now</button><button @click="toast='COD limit: ৳5000 set'" class="btn-outline px-4 py-2 text-sm">COD limit ৳5,000</button><button @click="toast='Fraud screen on ✓'" class="btn-outline px-4 py-2 text-sm">Auto fraud screen: ON</button></div>
    </div>
    <div x-show="toast" x-transition class="fixed bottom-5 right-5 z-50 rounded-lg bg-navy px-4 py-3 text-sm font-bold text-white shadow-lg" x-text="toast" @click="toast=''" x-cloak></div>
</div>
@endsection
