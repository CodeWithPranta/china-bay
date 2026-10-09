@extends('layouts.app')
@section('title', 'Shop – ChinaBay')

@section('content')
<nav class="mt-3 text-xs text-slate-500" aria-label="Breadcrumb"><a href="/" class="hover:text-brand">Home</a> / <span class="text-ink font-semibold">Shop</span></nav>

<livewire:shop.catalog-grid />
@endsection

@push('scripts')
<script>
window.CB_PRODUCTS = @json(\App\Support\Catalog::keyedById());
window.ChinaBay?.sync?.();
</script>
@endpush
