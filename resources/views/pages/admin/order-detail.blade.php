@extends('layouts.admin')
@section('title', 'Order {{ $code }} — ChinaBay Admin')
@section('heading', 'Order {{ $code }}')
@section('content')
<a href="/admin/orders" class="text-xs font-bold text-brand hover:underline">← All orders</a>
<div class="mt-3"><livewire:admin.orders-manager :preset="$code" /></div>
@endsection
