@extends('admin.layouts.app')
@section('content')
<div x-data="productList()">
    {{-- Page header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Products</h1>
            <p class="text-sm text-gray-500 mt-1">{{ $products->total() }} total products</p>
        </div>
        <a href="{{ route('admin.products.create') }}"
           class="inline-flex items-center gap-1.5 rounded-md bg-brand-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-800">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add product
        </a>
    </div>

    {{-- Filters --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm mb-6">
        <form method="GET" action="{{ route('admin.products.index') }}" class="p-4">
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="flex-1">
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Search products…"
                           class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none" />
                </div>
                <select name="status" class="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 outline-none">
                    <option value="">All statuses</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                    <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                    <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Archived</option>
                </select>
                <select name="collection" class="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 outline-none">
                    <option value="">All collections</option>
                    @foreach($collections as $c)
                        <option value="{{ $c->slug }}" {{ request('collection') === $c->slug ? 'selected' : '' }}>{{ $c->title }}</option>
                    @endforeach
                </select>
                <select name="sort" class="rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 outline-none">
                    <option value="updated_at" {{ request('sort') === 'updated_at' ? 'selected' : '' }}>Last updated</option>
                    <option value="created_at" {{ request('sort') === 'created_at' ? 'selected' : '' }}>Created date</option>
                    <option value="title" {{ request('sort') === 'title' ? 'selected' : '' }}>Title</option>
                    <option value="price" {{ request('sort') === 'price' ? 'selected' : '' }}>Price</option>
                </select>
                <button type="submit" class="rounded-md bg-gray-100 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 border border-gray-300">
                    Filter
                </button>
                @if(request()->hasAny(['q', 'status', 'collection', 'sort']))
                    <a href="{{ route('admin.products.index') }}" class="rounded-md px-3 py-2 text-sm text-gray-500 hover:text-gray-700">Clear</a>
                @endif
            </div>
        </form>
    </div>

    {{-- Bulk actions bar --}}
    <div x-show="selected.length > 0" x-cloak
         class="bg-brand-50 border border-brand-200 rounded-lg p-3 mb-4 flex flex-wrap items-center gap-3">
        <span class="text-sm font-medium text-brand-900" x-text="selected.length + ' selected'"></span>

        <form method="POST" action="{{ route('admin.bulk.status') }}" class="inline-flex items-center gap-2">
            @csrf
            <template x-for="id in selected"><input type="hidden" name="product_ids[]" :value="id" /></template>
            <select name="status" class="rounded border-gray-300 text-xs py-1 px-2">
                <option value="active">Active</option>
                <option value="draft">Draft</option>
                <option value="archived">Archived</option>
            </select>
            <button type="submit" class="rounded bg-white border border-gray-300 px-2.5 py-1 text-xs font-medium text-gray-700 hover:bg-gray-50">Set status</button>
        </form>

        <form method="POST" action="{{ route('admin.bulk.price') }}" class="inline-flex items-center gap-2">
            @csrf
            <template x-for="id in selected"><input type="hidden" name="product_ids[]" :value="id" /></template>
            <input type="number" name="price" step="0.01" min="0" placeholder="Price" class="w-24 rounded border-gray-300 text-xs py-1 px-2" />
            <button type="submit" class="rounded bg-white border border-gray-300 px-2.5 py-1 text-xs font-medium text-gray-700 hover:bg-gray-50">Set price</button>
        </form>

        <form method="POST" action="{{ route('admin.bulk.delete') }}"
              onsubmit="return confirm('Delete selected products? This cannot be undone.')" class="inline">
            @csrf
            <template x-for="id in selected"><input type="hidden" name="product_ids[]" :value="id" /></template>
            <button type="submit" class="rounded bg-red-50 border border-red-200 px-2.5 py-1 text-xs font-medium text-red-700 hover:bg-red-100">Delete</button>
        </form>

        <button @click="selected = []" class="text-xs text-gray-500 hover:text-gray-700 ml-auto">Clear selection</button>
    </div>

    {{-- Products table --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="w-10 px-4 py-3">
                        <input type="checkbox" @change="toggleAll($event)" class="rounded border-gray-300" />
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden md:table-cell">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider hidden sm:table-cell">Collections</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider hidden lg:table-cell">Variants</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($products as $product)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-4 py-3">
                        <input type="checkbox" :value="{{ $product->id }}" x-model.number="selected" class="rounded border-gray-300" />
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <img src="{{ $product->image ?? '/assets/img/placeholder.jpg' }}"
                                 alt="{{ $product->title }}"
                                 class="w-10 h-10 rounded object-cover bg-gray-100 flex-shrink-0" />
                            <div class="min-w-0">
                                <a href="{{ route('admin.products.edit', $product) }}" class="text-sm font-medium text-gray-900 hover:text-brand-700 truncate block">
                                    {{ $product->title }}
                                </a>
                                <p class="text-xs text-gray-400 truncate">/products/{{ $product->slug }}/</p>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 hidden md:table-cell">
                        @php
                            $statusColors = [
                                'active' => 'bg-green-100 text-green-800',
                                'draft' => 'bg-yellow-100 text-yellow-800',
                                'archived' => 'bg-gray-100 text-gray-600',
                            ];
                        @endphp
                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $statusColors[$product->status] ?? 'bg-gray-100 text-gray-600' }}">
                            {{ ucfirst($product->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 hidden sm:table-cell">
                        <div class="flex flex-wrap gap-1">
                            @foreach($product->collections->take(3) as $c)
                                <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-xs text-blue-700">{{ $c->title }}</span>
                            @endforeach
                            @if($product->collections->count() > 3)
                                <span class="text-xs text-gray-400">+{{ $product->collections->count() - 3 }}</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <span class="text-sm font-medium text-gray-900">${{ number_format($product->price, 2) }}</span>
                        @if($product->compare_at)
                            <span class="block text-xs text-gray-400 line-through">${{ number_format($product->compare_at, 2) }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center hidden lg:table-cell">
                        <span class="text-sm text-gray-600">{{ $product->variants->count() }}</span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <a href="{{ route('admin.products.edit', $product) }}"
                               class="rounded p-1.5 text-gray-400 hover:text-brand-700 hover:bg-brand-50" title="Edit">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <form method="POST" action="{{ route('admin.products.duplicate', $product) }}" class="inline">
                                @csrf
                                <button type="submit" class="rounded p-1.5 text-gray-400 hover:text-green-700 hover:bg-green-50" title="Duplicate">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                </button>
                            </form>
                            <form method="POST" action="{{ route('admin.products.destroy', $product) }}"
                                  onsubmit="return confirm('Delete \'{{ addslashes($product->title) }}\'?')" class="inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="rounded p-1.5 text-gray-400 hover:text-red-700 hover:bg-red-50" title="Delete">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-12 text-center">
                        <p class="text-gray-500 text-sm">No products found.</p>
                        <a href="{{ route('admin.products.create') }}" class="text-sm text-brand-700 hover:underline mt-2 inline-block">Create your first product &rarr;</a>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($products->hasPages())
    <div class="mt-4">
        {{ $products->links() }}
    </div>
    @endif
</div>

@push('scripts')
<script>
function productList() {
    return {
        selected: [],
        toggleAll(e) {
            if (e.target.checked) {
                this.selected = @json($products->pluck('id'));
            } else {
                this.selected = [];
            }
        }
    }
}
</script>
@endpush
@endsection

