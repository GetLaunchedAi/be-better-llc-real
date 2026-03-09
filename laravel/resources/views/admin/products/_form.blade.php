{{-- Shared product form fields — used in both create and edit views --}}
@php $p = $product ?? null; @endphp

{{-- Optimistic lock version for concurrent edit detection --}}
@if($p)
    <input type="hidden" name="lock_version" value="{{ $p->lock_version ?? 1 }}" />
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Left column: Core fields --}}
    <div class="lg:col-span-2 space-y-6">

        {{-- Basic info --}}
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5">
            <h2 class="text-base font-semibold text-gray-900 mb-4">Basic information</h2>

            <div class="space-y-4">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                    <input type="text" name="title" id="title" value="{{ old('title', $p->title ?? '') }}" required
                           @if(!$p) @input="autoSlug($event)" @endif
                           class="w-full rounded-md border px-3 py-2 text-sm focus:ring-1 outline-none @error('title') border-red-400 focus:border-red-500 focus:ring-red-500 @else border-gray-300 focus:border-brand-500 focus:ring-brand-500 @enderror" />
                    @error('title')
                        <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label for="subtitle" class="block text-sm font-medium text-gray-700 mb-1">Subtitle</label>
                    <input type="text" name="subtitle" id="subtitle" value="{{ old('subtitle', $p->subtitle ?? '') }}"
                           class="w-full rounded-md border px-3 py-2 text-sm focus:ring-1 outline-none @error('subtitle') border-red-400 focus:border-red-500 focus:ring-red-500 @else border-gray-300 focus:border-brand-500 focus:ring-brand-500 @enderror" />
                    @error('subtitle')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="slug" class="block text-sm font-medium text-gray-700 mb-1">
                        Slug
                        <span class="text-xs text-gray-400 font-normal">(auto-generated if blank)</span>
                    </label>
                    <div class="flex items-center gap-1">
                        <span class="text-sm text-gray-400">/products/</span>
                        <input type="text" name="slug" id="slug" value="{{ old('slug', $p->slug ?? '') }}" placeholder="auto-generated"
                               pattern="[a-z0-9\-]*"
                               class="flex-1 rounded-md border px-3 py-2 text-sm focus:ring-1 outline-none @error('slug') border-red-400 focus:border-red-500 focus:ring-red-500 @else border-gray-300 focus:border-brand-500 focus:ring-brand-500 @enderror" />
                        <span class="text-sm text-gray-400">/</span>
                    </div>
                    @error('slug')
                        <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label for="details" class="block text-sm font-medium text-gray-700 mb-1">
                        Long description
                        <span class="text-xs text-gray-400 font-normal" id="details-counter"></span>
                    </label>
                    <textarea name="details" id="details" rows="5" maxlength="10000"
                              oninput="document.getElementById('details-counter').textContent = '(' + this.value.length + '/10000)'"
                              class="w-full rounded-md border px-3 py-2 text-sm focus:ring-1 outline-none @error('details') border-red-400 focus:border-red-500 focus:ring-red-500 @else border-gray-300 focus:border-brand-500 focus:ring-brand-500 @enderror">{{ old('details', $p->details ?? '') }}</textarea>
                    @error('details')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Pricing --}}
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5">
            <h2 class="text-base font-semibold text-gray-900 mb-4">Pricing</h2>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700 mb-1">Price <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 text-sm">$</span>
                        <input type="number" name="price" id="price" step="0.01" min="0" required
                               value="{{ old('price', $p ? number_format($p->price, 2, '.', '') : '') }}"
                               class="w-full rounded-md border pl-7 pr-3 py-2 text-sm focus:ring-1 outline-none @error('price') border-red-400 focus:border-red-500 focus:ring-red-500 @else border-gray-300 focus:border-brand-500 focus:ring-brand-500 @enderror" />
                    </div>
                    @error('price')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="compare_at" class="block text-sm font-medium text-gray-700 mb-1">Compare-at price</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-400 text-sm">$</span>
                        <input type="number" name="compare_at" id="compare_at" step="0.01" min="0"
                               value="{{ old('compare_at', $p && $p->compare_at ? number_format($p->compare_at, 2, '.', '') : '') }}"
                               class="w-full rounded-md border pl-7 pr-3 py-2 text-sm focus:ring-1 outline-none @error('compare_at') border-red-400 focus:border-red-500 focus:ring-red-500 @else border-gray-300 focus:border-brand-500 focus:ring-brand-500 @enderror" />
                    </div>
                    @error('compare_at')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- Extras --}}
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5">
            <h2 class="text-base font-semibold text-gray-900 mb-4">Extra fields</h2>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div>
                    <label for="badge" class="block text-sm font-medium text-gray-700 mb-1">Badge</label>
                    <input type="text" name="badge" id="badge" value="{{ old('badge', $p->badge ?? '') }}"
                           placeholder="e.g. New, Sale"
                           class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none" />
                </div>
                <div>
                    <label for="rating" class="block text-sm font-medium text-gray-700 mb-1">Rating</label>
                    <input type="number" name="rating" id="rating" step="0.01" min="0" max="5"
                           value="{{ old('rating', $p->rating ?? '') }}"
                           class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none" />
                </div>
                <div>
                    <label for="review_count" class="block text-sm font-medium text-gray-700 mb-1">Reviews</label>
                    <input type="number" name="review_count" id="review_count" min="0"
                           value="{{ old('review_count', $p->review_count ?? '') }}"
                           class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none" />
                </div>
                <div>
                    <label for="giveaway_entries" class="block text-sm font-medium text-gray-700 mb-1">Giveaway entries</label>
                    <input type="number" name="giveaway_entries" id="giveaway_entries" min="0"
                           value="{{ old('giveaway_entries', $p->giveaway_entries ?? '') }}"
                           class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none" />
                </div>
            </div>
        </div>

        {{-- SEO --}}
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5" x-data="{ open: false }">
            <button type="button" @click="open = !open" class="flex items-center justify-between w-full text-left">
                <h2 class="text-base font-semibold text-gray-900">SEO / Meta</h2>
                <svg class="w-5 h-5 text-gray-400 transition-transform" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>

            <div x-show="open" x-cloak class="mt-4 space-y-4">
                <div>
                    <label for="meta_title" class="block text-sm font-medium text-gray-700 mb-1">Meta title</label>
                    <input type="text" name="meta_title" id="meta_title" value="{{ old('meta_title', $p->meta_title ?? '') }}"
                           class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none" />
                </div>
                <div>
                    <label for="meta_description" class="block text-sm font-medium text-gray-700 mb-1">Meta description</label>
                    <textarea name="meta_description" id="meta_description" rows="2"
                              class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none">{{ old('meta_description', $p->meta_description ?? '') }}</textarea>
                </div>
                <div>
                    <label for="canonical_url" class="block text-sm font-medium text-gray-700 mb-1">Canonical URL</label>
                    <input type="text" name="canonical_url" id="canonical_url" value="{{ old('canonical_url', $p->canonical_url ?? '') }}"
                           class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none" />
                </div>
            </div>
        </div>
    </div>

    {{-- Right sidebar --}}
    <div class="space-y-6">

        {{-- Status --}}
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5">
            <h2 class="text-base font-semibold text-gray-900 mb-3">Status</h2>
            <select name="status" class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 outline-none">
                <option value="active" {{ old('status', $p->status ?? 'draft') === 'active' ? 'selected' : '' }}>Active</option>
                <option value="draft" {{ old('status', $p->status ?? 'draft') === 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="archived" {{ old('status', $p->status ?? 'draft') === 'archived' ? 'selected' : '' }}>Archived</option>
            </select>
        </div>

        {{-- Primary image --}}
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5">
            <h2 class="text-base font-semibold text-gray-900 mb-3">Primary image</h2>
            <div class="mb-3">
                <img id="primary-img-preview"
                     src="{{ str_replace([' ', '(', ')'], ['%20', '%28', '%29'], old('image', $p->image ?? '/assets/img/placeholder.jpg')) }}"
                     alt="Primary image"
                     class="w-full aspect-square object-cover rounded-lg bg-gray-100 border border-gray-200" />
            </div>
            <input type="text" name="image" id="image" value="{{ old('image', $p->image ?? '/assets/img/placeholder.jpg') }}"
                   class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none"
                   placeholder="/assets/img/placeholder.jpg"
                   oninput="document.getElementById('primary-img-preview').src = this.value || '/assets/img/placeholder.jpg'" />
            <p class="mt-1 text-xs text-gray-400">Path or URL. Upload images in the Media section after saving.</p>
        </div>

        {{-- Collections --}}
        @php $selectedCols = old('collections', $p ? $p->collections->pluck('id')->toArray() : []); @endphp
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5"
             x-data="collectionManager({{ json_encode($collections->map(fn($c) => ['id' => $c->id, 'title' => $c->title])) }}, {{ json_encode(array_map('intval', $selectedCols)) }})">
            <h2 class="text-base font-semibold text-gray-900 mb-3">Collections</h2>

            {{-- Dropdown toggle --}}
            <div class="relative">
                <button type="button" @click="open = !open"
                        class="w-full flex items-center justify-between rounded-md border border-gray-300 bg-white px-3 py-2 text-sm text-left focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none">
                    <span x-text="selectedLabel()" class="truncate text-gray-700"></span>
                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0 transition-transform" :class="open && 'rotate-180'" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </button>

                {{-- Dropdown panel --}}
                <div x-show="open" x-cloak x-transition @click.away="open = false"
                     class="absolute z-20 mt-1 w-full rounded-md border border-gray-200 bg-white shadow-lg">

                    {{-- Search filter --}}
                    <div class="p-2 border-b border-gray-100">
                        <input type="text" x-model="search" placeholder="Search collections..."
                               @click.stop
                               class="w-full rounded-md border border-gray-300 px-2.5 py-1.5 text-xs focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none" />
                    </div>

                    {{-- Collection list --}}
                    <div class="max-h-48 overflow-y-auto p-1">
                        <template x-for="col in filteredCollections()" :key="col.id">
                            <label class="flex items-center gap-2 px-2 py-1.5 rounded hover:bg-gray-50 cursor-pointer text-sm text-gray-700">
                                <input type="checkbox"
                                       :value="col.id"
                                       :checked="selected.includes(col.id)"
                                       @change="toggleCollection(col.id)"
                                       class="rounded border-gray-300 text-brand-600" />
                                <span x-text="col.title"></span>
                            </label>
                        </template>

                        {{-- Inline new-collection items --}}
                        <template x-for="(name, i) in newCollections" :key="'new-'+i">
                            <div class="flex items-center gap-2 px-2 py-1.5 text-sm text-gray-700">
                                <input type="checkbox" checked disabled class="rounded border-gray-300 text-brand-600" />
                                <span x-text="name" class="flex-1"></span>
                                <button type="button" @click="newCollections.splice(i, 1)" class="text-gray-400 hover:text-red-600 text-xs">&times;</button>
                            </div>
                        </template>

                        <p x-show="filteredCollections().length === 0 && newCollections.length === 0" class="px-2 py-1.5 text-xs text-gray-400">No collections found.</p>
                    </div>

                    {{-- Add new collection --}}
                    <div class="border-t border-gray-100 p-2">
                        <p class="text-xs text-gray-500 mb-1.5">Add new collection:</p>
                        <div class="flex gap-2">
                            <input type="text" x-model="newName" placeholder="Collection name"
                                   @keydown.enter.prevent="addCollection" @click.stop
                                   class="flex-1 rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none" />
                            <button type="button" @click.stop="addCollection"
                                    class="rounded bg-gray-100 px-2.5 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-200 border border-gray-300">Add</button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Hidden inputs for form submission --}}
            <template x-for="id in selected" :key="'sel-'+id">
                <input type="hidden" name="collections[]" :value="id" />
            </template>
            <template x-for="(name, i) in newCollections" :key="'newcol-'+i">
                <input type="hidden" name="new_collections[]" :value="name" />
            </template>

            {{-- Selected badges --}}
            <div class="flex flex-wrap gap-1.5 mt-2" x-show="selected.length > 0 || newCollections.length > 0">
                <template x-for="id in selected" :key="'badge-'+id">
                    <span class="inline-flex items-center gap-1 rounded-full bg-brand-50 px-2 py-0.5 text-xs text-brand-700">
                        <span x-text="collectionTitle(id)"></span>
                        <button type="button" @click="toggleCollection(id)" class="text-brand-400 hover:text-red-600">&times;</button>
                    </span>
                </template>
                <template x-for="(name, i) in newCollections" :key="'nbadge-'+i">
                    <span class="inline-flex items-center gap-1 rounded-full bg-green-50 px-2 py-0.5 text-xs text-green-700">
                        <span x-text="name"></span>
                        <button type="button" @click="newCollections.splice(i, 1)" class="text-green-400 hover:text-red-600">&times;</button>
                    </span>
                </template>
            </div>
        </div>

        {{-- Tags --}}
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5" x-data="tagManager()">
            <h2 class="text-base font-semibold text-gray-900 mb-3">Tags</h2>

            @php $selectedTags = old('tags', $p ? $p->tags->pluck('id')->toArray() : []); @endphp
            <div class="space-y-2 max-h-36 overflow-y-auto mb-3">
                @foreach($tags as $t)
                <label class="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" name="tags[]" value="{{ $t->id }}"
                           {{ in_array($t->id, $selectedTags) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-brand-600" />
                    {{ $t->name }}
                </label>
                @endforeach
            </div>

            <div class="border-t border-gray-100 pt-3">
                <p class="text-xs text-gray-500 mb-2">Add new tag:</p>
                <div class="flex gap-2">
                    <input type="text" x-model="newTag" placeholder="Tag name" @keydown.enter.prevent="addTag"
                           class="flex-1 rounded-md border border-gray-300 px-2 py-1.5 text-xs focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none" />
                    <button type="button" @click="addTag"
                            class="rounded bg-gray-100 px-2.5 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-200 border border-gray-300">Add</button>
                </div>
                <template x-for="(tag, i) in newTags" :key="i">
                    <div class="flex items-center gap-1 mt-1.5">
                        <input type="hidden" name="new_tags[]" :value="tag" />
                        <span class="inline-flex items-center rounded-full bg-brand-50 px-2 py-0.5 text-xs text-brand-700" x-text="tag"></span>
                        <button type="button" @click="newTags.splice(i, 1)" class="text-gray-400 hover:text-red-600 text-xs">&times;</button>
                    </div>
                </template>
            </div>
        </div>

        {{-- Save actions --}}
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5">
            <button type="submit"
                    class="w-full rounded-md bg-brand-900 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-brand-800">
                {{ $p ? 'Update product' : 'Create product' }}
            </button>

            @if($p)
            <div class="mt-3 flex gap-2">
                <form method="POST" action="{{ route('admin.products.duplicate', $p) }}" class="flex-1">
                    @csrf
                    <button type="submit" class="w-full rounded-md bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 border border-gray-300">
                        Duplicate
                    </button>
                </form>
                <a href="{{ $p->url }}" target="_blank"
                   class="flex-1 rounded-md bg-gray-100 px-3 py-2 text-sm font-medium text-gray-700 hover:bg-gray-200 border border-gray-300 text-center">
                    View ↗
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function collectionManager(allCollections, initialSelected) {
    return {
        open: false,
        search: '',
        newName: '',
        collections: allCollections,          // [{id, title}, ...]
        selected: initialSelected || [],      // [id, id, ...]
        newCollections: [],                    // ['name', 'name', ...]
        toggleCollection(id) {
            const idx = this.selected.indexOf(id);
            if (idx === -1) {
                this.selected.push(id);
            } else {
                this.selected.splice(idx, 1);
            }
        },
        addCollection() {
            const name = this.newName.trim();
            if (name && !this.newCollections.includes(name)) {
                this.newCollections.push(name);
                this.newName = '';
            }
        },
        filteredCollections() {
            if (!this.search) return this.collections;
            const q = this.search.toLowerCase();
            return this.collections.filter(c => c.title.toLowerCase().includes(q));
        },
        collectionTitle(id) {
            const col = this.collections.find(c => c.id === id);
            return col ? col.title : '';
        },
        selectedLabel() {
            const count = this.selected.length + this.newCollections.length;
            if (count === 0) return 'Select collections...';
            if (count === 1) {
                if (this.selected.length === 1) return this.collectionTitle(this.selected[0]);
                return this.newCollections[0];
            }
            return count + ' collections selected';
        }
    }
}

function tagManager() {
    return {
        newTag: '',
        newTags: [],
        addTag() {
            const tag = this.newTag.trim();
            if (tag && !this.newTags.includes(tag)) {
                this.newTags.push(tag);
                this.newTag = '';
            }
        }
    }
}

/**
 * Auto-generate slug from title (only on create, not edit).
 * Called via @input on the title field when product is new.
 */
function autoSlug(e) {
    const slugField = document.getElementById('slug');
    if (!slugField || slugField.dataset.manualEdit === 'true') return;

    const title = e.target.value;
    const slug = title
        .toLowerCase()
        .replace(/[^a-z0-9\s-]/g, '')
        .replace(/\s+/g, '-')
        .replace(/-+/g, '-')
        .replace(/^-|-$/g, '');

    slugField.value = slug;
}

// Mark slug as manually edited if user types in it directly
document.addEventListener('DOMContentLoaded', function() {
    const slugField = document.getElementById('slug');
    if (slugField) {
        slugField.addEventListener('input', function() {
            this.dataset.manualEdit = 'true';
        });
    }
});
</script>
@endpush

