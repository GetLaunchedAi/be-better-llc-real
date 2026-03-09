@extends('admin.layouts.app')
@section('content')
<div class="mb-6">
    <nav class="text-sm text-gray-500 mb-2">
        <a href="{{ route('admin.products.index') }}" class="hover:text-brand-700">Products</a>
        <span class="mx-1">/</span>
        <span class="text-gray-900">{{ $product->title }}</span>
    </nav>
    <div class="flex items-center justify-between gap-4">
        <h1 class="text-2xl font-bold text-gray-900 truncate">{{ $product->title }}</h1>
        <form method="POST" action="{{ route('admin.products.destroy', $product) }}"
              onsubmit="return confirm('Permanently delete this product?')">
            @csrf @method('DELETE')
            <button type="submit" class="text-sm text-red-600 hover:text-red-800">Delete product</button>
        </form>
    </div>
</div>

{{-- Tabs --}}
<div x-data="{ tab: '{{ request('tab', 'details') }}' }" class="space-y-6">
    <div class="border-b border-gray-200 bg-white rounded-t-lg">
        <nav class="flex gap-0 -mb-px overflow-x-auto">
            <button type="button" @click="tab = 'details'"
                    :class="tab === 'details' ? 'border-brand-500 text-brand-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap border-b-2 py-3 px-5 text-sm font-medium transition-colors">
                Details
            </button>
            <button type="button" @click="tab = 'variants'"
                    :class="tab === 'variants' ? 'border-brand-500 text-brand-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap border-b-2 py-3 px-5 text-sm font-medium transition-colors">
                Variants <span class="ml-1 text-xs text-gray-400">({{ $product->variants->count() }})</span>
            </button>
            <button type="button" @click="tab = 'media'"
                    :class="tab === 'media' ? 'border-brand-500 text-brand-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap border-b-2 py-3 px-5 text-sm font-medium transition-colors">
                Media <span class="ml-1 text-xs text-gray-400">({{ $product->images->count() }})</span>
            </button>
            <button type="button" @click="tab = 'preview'"
                    :class="tab === 'preview' ? 'border-brand-500 text-brand-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap border-b-2 py-3 px-5 text-sm font-medium transition-colors">
                Preview
            </button>
            <button type="button" @click="tab = 'activity'"
                    :class="tab === 'activity' ? 'border-brand-500 text-brand-700' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                    class="whitespace-nowrap border-b-2 py-3 px-5 text-sm font-medium transition-colors">
                Activity log
            </button>
        </nav>
    </div>

    {{-- Tab: Details --}}
    <div x-show="tab === 'details'" x-cloak>
        <form method="POST" action="{{ route('admin.products.update', $product) }}">
            @csrf @method('PUT')
            @include('admin.products._form', ['product' => $product])
        </form>
    </div>

    {{-- Tab: Variants --}}
    <div x-show="tab === 'variants'" x-cloak>
        @include('admin.products._variants', ['product' => $product])
    </div>

    {{-- Tab: Media --}}
    <div x-show="tab === 'media'" x-cloak>
        @include('admin.products._media', ['product' => $product])
    </div>

    {{-- Tab: Preview --}}
    <div x-show="tab === 'preview'" x-cloak>
        @include('admin.products._preview', ['product' => $product])
    </div>

    {{-- Tab: Activity log --}}
    <div x-show="tab === 'activity'" x-cloak>
        @include('admin.products._activity', ['recentLogs' => $recentLogs])
    </div>
</div>
@endsection

