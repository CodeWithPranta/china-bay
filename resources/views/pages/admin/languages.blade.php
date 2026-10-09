@extends('layouts.admin')
@section('title', 'Languages — ChinaBay Admin')
@section('heading', 'Languages')
@section('content')
<div class="space-y-6">
    <livewire:admin.locales-manager />
    <livewire:admin.translations-manager />
</div>
@endsection
