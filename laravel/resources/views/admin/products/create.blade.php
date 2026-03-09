@extends('admin.layouts.app')
@section('content')
<div class="mb-6">
    <nav class="text-sm text-gray-500 mb-2">
        <a href="{{ route('admin.products.index') }}" class="hover:text-brand-700">Products</a>
        <span class="mx-1">/</span>
        <span class="text-gray-900">New product</span>
    </nav>
    <h1 class="text-2xl font-bold text-gray-900">Create product</h1>
</div>

<form method="POST" action="{{ route('admin.products.store') }}">
    @csrf
    @include('admin.products._form', ['product' => null])
</form>
@endsection

