{{-- Variant matrix management --}}
@php
    $variants = $product->variants->sortBy(['color', 'size']);
    $existingSizes = $variants->pluck('size')->unique()->values()->toArray();
    $existingColors = $variants->pluck('color')->unique()->values()->toArray();
@endphp

<div class="space-y-6">
    {{-- Generator --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5" x-data="variantGenerator()">
        <h2 class="text-base font-semibold text-gray-900 mb-1">Generate variant matrix</h2>
        <p class="text-sm text-gray-500 mb-4">Add sizes and colors, then generate all combinations. Existing combinations are skipped.</p>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
            {{-- Sizes --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Sizes</label>
                <div class="flex flex-wrap gap-2 mb-2">
                    <template x-for="(size, i) in sizes" :key="'s-'+i">
                        <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700">
                            <span x-text="size"></span>
                            <button type="button" @click="sizes.splice(i, 1)" class="text-gray-400 hover:text-red-600">&times;</button>
                        </span>
                    </template>
                </div>
                <div class="flex gap-2">
                    <input type="text" x-model="newSize" @keydown.enter.prevent="addSize" placeholder="e.g. XL"
                           class="flex-1 rounded-md border border-gray-300 px-3 py-1.5 text-sm focus:border-brand-500 outline-none" />
                    <button type="button" @click="addSize" class="rounded bg-gray-100 border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-200">Add</button>
                </div>
                <div class="flex flex-wrap gap-1 mt-2">
                    <template x-for="preset in ['XS','S','M','L','XL','2XL','3XL']">
                        <button type="button" @click="if(!sizes.includes(preset)) sizes.push(preset)"
                                class="rounded bg-gray-50 border border-gray-200 px-2 py-0.5 text-xs text-gray-500 hover:bg-gray-100" x-text="preset"></button>
                    </template>
                </div>
            </div>

            {{-- Colors --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Colors</label>
                <div class="flex flex-wrap gap-2 mb-2">
                    <template x-for="(color, i) in colors" :key="'c-'+i">
                        <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700">
                            <span x-text="color"></span>
                            <button type="button" @click="colors.splice(i, 1)" class="text-gray-400 hover:text-red-600">&times;</button>
                        </span>
                    </template>
                </div>
                <div class="flex gap-2">
                    <input type="text" x-model="newColor" @keydown.enter.prevent="addColor" placeholder="e.g. Navy"
                           class="flex-1 rounded-md border border-gray-300 px-3 py-1.5 text-sm focus:border-brand-500 outline-none" />
                    <button type="button" @click="addColor" class="rounded bg-gray-100 border border-gray-300 px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-200">Add</button>
                </div>
                <div class="flex flex-wrap gap-1 mt-2">
                    <template x-for="preset in ['Black','White','Navy','Stone','Red','Grey','Camo']">
                        <button type="button" @click="if(!colors.includes(preset)) colors.push(preset)"
                                class="rounded bg-gray-50 border border-gray-200 px-2 py-0.5 text-xs text-gray-500 hover:bg-gray-100" x-text="preset"></button>
                    </template>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <form method="POST" action="{{ route('admin.variants.generate', $product) }}" x-ref="genForm">
                @csrf
                <template x-for="size in sizes"><input type="hidden" name="sizes[]" :value="size" /></template>
                <template x-for="color in colors"><input type="hidden" name="colors[]" :value="color" /></template>
                <button type="submit" :disabled="sizes.length === 0 || colors.length === 0"
                        class="rounded-md bg-brand-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-800 disabled:opacity-50 disabled:cursor-not-allowed">
                    Generate <span x-text="sizes.length * colors.length"></span> variants
                </button>
            </form>
            <p class="text-xs text-gray-400">Duplicates are automatically skipped.</p>
        </div>
    </div>

    {{-- Existing variants table --}}
    @if($variants->isNotEmpty())
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden" x-data="variantTable()">
        <div class="p-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3">
            <h2 class="text-base font-semibold text-gray-900">
                Existing variants <span class="text-sm font-normal text-gray-400">({{ $variants->count() }})</span>
            </h2>

            <div x-show="selectedVars.length > 0" class="flex items-center gap-2">
                <span class="text-xs text-gray-500" x-text="selectedVars.length + ' selected'"></span>

                {{-- Bulk activate/deactivate --}}
                <form method="POST" action="{{ route('admin.variants.bulk-toggle', $product) }}" class="inline">
                    @csrf
                    <template x-for="id in selectedVars"><input type="hidden" name="variant_ids[]" :value="id" /></template>
                    <input type="hidden" name="is_active" value="1" />
                    <button type="submit" class="rounded bg-green-50 border border-green-200 px-2.5 py-1 text-xs font-medium text-green-700 hover:bg-green-100">Activate</button>
                </form>
                <form method="POST" action="{{ route('admin.variants.bulk-toggle', $product) }}" class="inline">
                    @csrf
                    <template x-for="id in selectedVars"><input type="hidden" name="variant_ids[]" :value="id" /></template>
                    <input type="hidden" name="is_active" value="0" />
                    <button type="submit" class="rounded bg-yellow-50 border border-yellow-200 px-2.5 py-1 text-xs font-medium text-yellow-700 hover:bg-yellow-100">Deactivate</button>
                </form>

                {{-- Bulk price --}}
                <form method="POST" action="{{ route('admin.variants.bulk-price', $product) }}" class="inline-flex items-center gap-1">
                    @csrf
                    <template x-for="id in selectedVars"><input type="hidden" name="variant_ids[]" :value="id" /></template>
                    <input type="number" name="price_override" step="0.01" min="0" placeholder="$" class="w-20 rounded border-gray-300 text-xs py-1 px-2" />
                    <button type="submit" class="rounded bg-blue-50 border border-blue-200 px-2.5 py-1 text-xs font-medium text-blue-700 hover:bg-blue-100">Set price</button>
                </form>
            </div>
        </div>

        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="w-10 px-4 py-2"><input type="checkbox" @change="toggleAllVars($event)" class="rounded border-gray-300" /></th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Size</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Color</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Price override</th>
                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-500 uppercase">Active</th>
                    <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach($variants as $v)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-2"><input type="checkbox" :value="{{ $v->id }}" x-model.number="selectedVars" class="rounded border-gray-300" /></td>
                    <td class="px-4 py-2 text-sm text-gray-900">{{ $v->size }}</td>
                    <td class="px-4 py-2 text-sm text-gray-900">{{ $v->color }}</td>
                    <td class="px-4 py-2">
                        <form method="POST" action="{{ route('admin.variants.update', [$product, $v]) }}" class="inline-flex items-center gap-1">
                            @csrf @method('PUT')
                            <input type="text" name="sku" value="{{ $v->sku }}" class="w-40 rounded border-gray-300 text-xs py-1 px-2 focus:border-brand-500 outline-none" />
                    </td>
                    <td class="px-4 py-2 text-right">
                            <input type="number" name="price_override" step="0.01" min="0"
                                   value="{{ $v->price_override ? number_format($v->price_override, 2, '.', '') : '' }}"
                                   placeholder="Base"
                                   class="w-24 rounded border-gray-300 text-xs py-1 px-2 text-right focus:border-brand-500 outline-none" />
                    </td>
                    <td class="px-4 py-2 text-center">
                            <input type="hidden" name="is_active" value="0" />
                            <input type="checkbox" name="is_active" value="1" {{ $v->is_active ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-brand-600" />
                    </td>
                    <td class="px-4 py-2 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <button type="submit" class="rounded p-1 text-gray-400 hover:text-brand-700 hover:bg-brand-50" title="Save">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </button>
                            </div>
                        </form>
                        <form method="POST" action="{{ route('admin.variants.destroy', [$product, $v]) }}"
                              onsubmit="return confirm('Delete variant {{ $v->sku }}?')" class="inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="rounded p-1 text-gray-400 hover:text-red-700 hover:bg-red-50" title="Delete">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-8 text-center">
        <p class="text-gray-500 text-sm">No variants yet. Use the generator above to create size × color combinations.</p>
    </div>
    @endif
</div>

@push('scripts')
<script>
function variantGenerator() {
    return {
        sizes: @json($existingSizes),
        colors: @json($existingColors),
        newSize: '',
        newColor: '',
        addSize() {
            const s = this.newSize.trim();
            if (s && !this.sizes.includes(s)) { this.sizes.push(s); this.newSize = ''; }
        },
        addColor() {
            const c = this.newColor.trim();
            if (c && !this.colors.includes(c)) { this.colors.push(c); this.newColor = ''; }
        }
    }
}

function variantTable() {
    return {
        selectedVars: [],
        toggleAllVars(e) {
            this.selectedVars = e.target.checked ? @json($variants->pluck('id')) : [];
        }
    }
}
</script>
@endpush

