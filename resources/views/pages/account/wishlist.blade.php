@extends('layouts.app')
@section('title', 'Wishlist — ChinaBay')
@php use App\Support\Catalog; $map = collect(Catalog::products())->keyBy('id'); @endphp
@section('content')
<div class="flex flex-col gap-4 py-6 lg:flex-row">
    <x-account.sidebar />
    <div class="min-w-0 flex-1">
        <h1 class="text-lg font-extrabold">My Wishlist ♡ <span id="wN" class="text-sm font-normal text-slate-400"></span></h1>
        <div id="wishGrid" class="mt-3 grid grid-cols-2 gap-3 lg:grid-cols-3"></div>
        <p id="wishEmpty" class="card mt-3 hidden text-center text-sm text-slate-500">No saved items yet. Tap ♡ on any product. <a href="/shop" class="text-brand font-semibold">Browse shop →</a></p>
    </div>
</div>
@push('scripts')
<script>
window.CB_PRODUCTS = @json($map);
(function () {
    const grid = document.getElementById('wishGrid'), empty = document.getElementById('wishEmpty'), n = document.getElementById('wN');
    function render() {
        const wish = window.ChinaBay?.wish || [];
        n.textContent = wish.length ? `(${wish.length})` : '';
        empty.classList.toggle('hidden', wish.length > 0);
        grid.innerHTML = wish.map((id) => {
            const p = window.CB_PRODUCTS[id];
            if (!p) return '';
            return `<article class="card !p-3 text-xs"><span class="grid h-20 place-items-center text-5xl">${p.emoji}</span><b class="mt-2 block truncate">${p.name}</b><p class="text-brand font-bold">৳ ${Number(p.price).toLocaleString()}</p><div class="mt-2 flex gap-1.5"><button data-add="${p.id}" class="btn flex-1 py-1.5 text-[11px]">🛒 Move to Cart</button><button data-wish="${p.id}" class="btn-outline px-2.5 py-1.5">♥</button></div></article>`;
        }).join('');
    }
    document.addEventListener('chinabay:sync', render);
    render();
})();
</script>
@endpush
@endsection
