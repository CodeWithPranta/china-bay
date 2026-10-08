@extends('layouts.app')
@section('title', 'Notifications — ChinaBay')
@section('content')
<div class="flex flex-col gap-4 py-6 lg:flex-row">
    <x-account.sidebar />
    <div class="min-w-0 flex-1">
        <div class="card">
            <div class="flex items-center justify-between"><h1 class="text-lg font-extrabold">Notifications 🔔</h1><button id="markAll" class="text-xs font-semibold text-brand">Mark all as read</button></div>
            <div id="notifs" class="mt-3 space-y-2 text-xs">
                @foreach ([['📦','Order CB-1001 shipped!','Your parcel left Dhaka hub · 2h ago',1],['🎟','FLASH50 — ৳50 off today','Flash sale ends midnight · 5h ago',1],['💰','Price drop: Smart Watch','৳3,499 → ৳2,999 · 1d ago',1],['🪙','+20 points earned','Review approved · 2d ago',0],['📦','Order CB-1003 delivered','Rate your items · 24 Sep',0]] as [$e,$t,$s,$u])
                    <div class="notif flex gap-3 rounded-lg border border-slate-100 p-3 {{ $u ? 'bg-brand/[0.03]' : '' }}" data-unread="{{ $u }}">
                        <span class="text-xl">{{ $e }}</span><div class="flex-1"><b>{{ $t }}</b><br><span class="text-slate-500">{{ $s }}</span></div>@if($u)<span class="dot mt-1.5 size-2 shrink-0 rounded-full bg-brand"></span>@endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@push('scripts')
<script>
document.getElementById('markAll')?.addEventListener('click', () => {
    document.querySelectorAll('.notif').forEach((n) => { n.dataset.unread = '0'; n.classList.remove('bg-brand/[0.03]'); n.querySelector('.dot')?.remove(); });
    window.cbHelpers.toast('All notifications read');
});
</script>
@endpush
@endsection
