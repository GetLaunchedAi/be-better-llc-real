@extends('admin.layouts.app')

@section('content')
<div class="mb-6">
    <nav class="text-sm text-gray-500 mb-2">
        <a href="{{ route('admin.products.index') }}" class="hover:text-brand-700">Admin</a>
        <span class="mx-1">/</span>
        <span class="text-gray-900">Navigation</span>
    </nav>
    <h1 class="text-2xl font-bold text-gray-900">Navigation</h1>
    <p class="text-sm text-gray-500 mt-1">Manage which pages appear in the site header and their order.</p>
</div>

<form method="POST" action="{{ route('admin.navigation.update') }}"
      x-data="navEditor()"
      @submit.prevent="submitForm($el)">
    @csrf
    @method('PUT')

    {{-- Primary Nav --}}
    <div class="mb-8">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-lg font-semibold text-gray-900">Primary Navigation</h2>
            <button type="button" @click="addItem('primary')"
                    class="inline-flex items-center gap-1.5 rounded-md bg-brand-900 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-brand-800">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14m-7-7h14"/></svg>
                Add link
            </button>
        </div>

        <div class="space-y-2">
            <template x-for="(item, idx) in primary" :key="item._key">
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4 flex items-start gap-3"
                     :class="{ 'opacity-50': !item.is_visible }">

                    <div class="flex flex-col gap-1 pt-2">
                        <button type="button" @click="moveUp('primary', idx)"
                                class="text-gray-400 hover:text-gray-700 disabled:opacity-30"
                                :disabled="idx === 0" title="Move up">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 15l7-7 7 7"/></svg>
                        </button>
                        <button type="button" @click="moveDown('primary', idx)"
                                class="text-gray-400 hover:text-gray-700 disabled:opacity-30"
                                :disabled="idx === primary.length - 1" title="Move down">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7"/></svg>
                        </button>
                    </div>

                    <div class="flex-1 grid gap-3 sm:grid-cols-[1fr_1fr_auto]">
                        <input type="hidden" :name="`items[${itemIndex('primary', idx)}][id]`" :value="item.id || ''">
                        <input type="hidden" :name="`items[${itemIndex('primary', idx)}][type]`" value="primary">
                        <input type="hidden" :name="`items[${itemIndex('primary', idx)}][sort_order]`" :value="idx">

                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Label</label>
                            <input type="text" x-model="item.label"
                                   :name="`items[${itemIndex('primary', idx)}][label]`"
                                   class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none"
                                   required maxlength="100" placeholder="e.g. MENS">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">URL</label>
                            <input type="text" x-model="item.url"
                                   :name="`items[${itemIndex('primary', idx)}][url]`"
                                   class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none"
                                   required maxlength="255" placeholder="e.g. /collections/men/">
                        </div>
                        <div class="flex items-end gap-2">
                            <label class="flex items-center gap-2 cursor-pointer py-2">
                                <input type="checkbox" x-model="item.is_visible"
                                       :name="`items[${itemIndex('primary', idx)}][is_visible]`"
                                       value="1"
                                       class="rounded border-gray-300 text-brand-700 focus:ring-brand-500">
                                <span class="text-xs text-gray-600">Visible</span>
                            </label>
                            <button type="button" @click="removeItem('primary', idx)"
                                    class="text-red-400 hover:text-red-600 p-2" title="Remove">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </template>

            <template x-if="primary.length === 0">
                <p class="text-sm text-gray-400 italic py-4 text-center">No primary nav items. Click "Add link" to create one.</p>
            </template>
        </div>
    </div>

    {{-- Meta Nav --}}
    <div class="mb-8">
        <div class="flex items-center justify-between mb-3">
            <h2 class="text-lg font-semibold text-gray-900">Footer / Meta Links</h2>
            <button type="button" @click="addItem('meta')"
                    class="inline-flex items-center gap-1.5 rounded-md bg-brand-900 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-brand-800">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14m-7-7h14"/></svg>
                Add link
            </button>
        </div>

        <div class="space-y-2">
            <template x-for="(item, idx) in meta" :key="item._key">
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-4 flex items-start gap-3"
                     :class="{ 'opacity-50': !item.is_visible }">

                    <div class="flex flex-col gap-1 pt-2">
                        <button type="button" @click="moveUp('meta', idx)"
                                class="text-gray-400 hover:text-gray-700 disabled:opacity-30"
                                :disabled="idx === 0" title="Move up">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 15l7-7 7 7"/></svg>
                        </button>
                        <button type="button" @click="moveDown('meta', idx)"
                                class="text-gray-400 hover:text-gray-700 disabled:opacity-30"
                                :disabled="idx === meta.length - 1" title="Move down">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7"/></svg>
                        </button>
                    </div>

                    <div class="flex-1 grid gap-3 sm:grid-cols-[1fr_1fr_auto]">
                        <input type="hidden" :name="`items[${itemIndex('meta', idx)}][id]`" :value="item.id || ''">
                        <input type="hidden" :name="`items[${itemIndex('meta', idx)}][type]`" value="meta">
                        <input type="hidden" :name="`items[${itemIndex('meta', idx)}][sort_order]`" :value="idx">

                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Label</label>
                            <input type="text" x-model="item.label"
                                   :name="`items[${itemIndex('meta', idx)}][label]`"
                                   class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none"
                                   required maxlength="100" placeholder="e.g. Shipping">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">URL</label>
                            <input type="text" x-model="item.url"
                                   :name="`items[${itemIndex('meta', idx)}][url]`"
                                   class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none"
                                   required maxlength="255" placeholder="e.g. /shipping/">
                        </div>
                        <div class="flex items-end gap-2">
                            <label class="flex items-center gap-2 cursor-pointer py-2">
                                <input type="checkbox" x-model="item.is_visible"
                                       :name="`items[${itemIndex('meta', idx)}][is_visible]`"
                                       value="1"
                                       class="rounded border-gray-300 text-brand-700 focus:ring-brand-500">
                                <span class="text-xs text-gray-600">Visible</span>
                            </label>
                            <button type="button" @click="removeItem('meta', idx)"
                                    class="text-red-400 hover:text-red-600 p-2" title="Remove">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </template>

            <template x-if="meta.length === 0">
                <p class="text-sm text-gray-400 italic py-4 text-center">No meta nav items. Click "Add link" to create one.</p>
            </template>
        </div>
    </div>

    <div class="pt-1">
        <button type="submit" class="rounded-md bg-brand-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-800">
            Save navigation
        </button>
    </div>
</form>

@push('scripts')
<script>
function navEditor() {
    let keyCounter = 0;

    function withKey(item) {
        item._key = 'k' + (keyCounter++);
        return item;
    }

    return {
        primary: @json($primaryItems).map(i => withKey({ ...i, is_visible: !!i.is_visible })),
        meta: @json($metaItems).map(i => withKey({ ...i, is_visible: !!i.is_visible })),

        addItem(type) {
            this[type].push(withKey({
                id: null,
                label: '',
                url: '',
                type: type,
                sort_order: this[type].length,
                is_visible: true,
            }));
        },

        removeItem(type, idx) {
            this[type].splice(idx, 1);
        },

        moveUp(type, idx) {
            if (idx <= 0) return;
            const arr = this[type];
            [arr[idx - 1], arr[idx]] = [arr[idx], arr[idx - 1]];
        },

        moveDown(type, idx) {
            const arr = this[type];
            if (idx >= arr.length - 1) return;
            [arr[idx], arr[idx + 1]] = [arr[idx + 1], arr[idx]];
        },

        itemIndex(type, idx) {
            if (type === 'meta') return this.primary.length + idx;
            return idx;
        },

        submitForm(formEl) {
            formEl.submit();
        }
    };
}
</script>
@endpush
@endsection
