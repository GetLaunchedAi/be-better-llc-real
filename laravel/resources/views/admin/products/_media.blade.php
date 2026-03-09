{{-- Media / Image manager --}}
@php
    $images = $product->images->sortBy('sort_order');
    $productVariants = $product->variants;
@endphp

<div class="space-y-6">
    {{-- Upload zone --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5"
         x-data="imageUploader()"
         @dragover.prevent="dragover = true"
         @dragleave.prevent="dragover = false"
         @drop.prevent="handleDrop($event)">

        <h2 class="text-base font-semibold text-gray-900 mb-1">Upload images</h2>
        <p class="text-sm text-gray-500 mb-4">Drag and drop images or click to browse. Max 5 MB per file, JPEG/PNG/GIF/WebP.</p>

        <form method="POST" action="{{ route('admin.images.upload', $product) }}" enctype="multipart/form-data" x-ref="uploadForm">
            @csrf
            <div class="relative">
                <div :class="dragover && 'drag-over'"
                     class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center cursor-pointer hover:border-brand-400 transition-colors"
                     @click="$refs.fileInput.click()">
                    <svg class="mx-auto h-10 w-10 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <p class="text-sm text-gray-600">
                        <span class="font-medium text-brand-700">Click to upload</span> or drag and drop
                    </p>
                    <p class="text-xs text-gray-400 mt-1">JPEG, PNG, GIF, WebP up to 5MB</p>
                </div>
                <input type="file" name="images[]" multiple accept="image/*" class="hidden" x-ref="fileInput"
                       @change="previewFiles($event)" />
            </div>

            @if($productVariants->isNotEmpty())
            <div class="mt-3">
                <label class="text-sm font-medium text-gray-700">Assign to variant (optional):</label>
                <select name="variant_id" class="mt-1 rounded-md border border-gray-300 px-3 py-1.5 text-sm focus:border-brand-500 outline-none">
                    <option value="">— Product-level —</option>
                    @foreach($productVariants->sortBy(['color','size']) as $v)
                        <option value="{{ $v->id }}">{{ $v->color }} / {{ $v->size }} ({{ $v->sku }})</option>
                    @endforeach
                </select>
            </div>
            @endif

            {{-- Previews --}}
            <div x-show="previews.length > 0" class="mt-4 grid grid-cols-4 sm:grid-cols-6 gap-3">
                <template x-for="(src, i) in previews" :key="i">
                    <div class="aspect-square rounded-lg border border-gray-200 overflow-hidden bg-gray-50">
                        <img :src="src" class="w-full h-full object-cover" />
                    </div>
                </template>
            </div>

            <div x-show="previews.length > 0" class="mt-3">
                <button type="submit" class="rounded-md bg-brand-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-800">
                    Upload <span x-text="previews.length"></span> image(s)
                </button>
            </div>
        </form>
    </div>

    {{-- Existing images grid --}}
    @if($images->isNotEmpty())
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5" x-data="imageGrid()">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-semibold text-gray-900">
                Product images <span class="text-sm font-normal text-gray-400">({{ $images->count() }})</span>
            </h2>
            <p class="text-xs text-gray-400">Drag to reorder. Changes save automatically.</p>
        </div>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4" id="image-grid">
            @foreach($images as $img)
            <div class="group relative border border-gray-200 rounded-lg overflow-hidden bg-gray-50 cursor-move"
                 draggable="true"
                 data-image-id="{{ $img->id }}"
                 @dragstart="dragStart($event, {{ $img->id }})"
                 @dragover.prevent="dragOver($event)"
                 @drop.prevent="drop($event, {{ $img->id }})">

                <div class="aspect-square">
                    <img src="{{ str_replace([' ', '(', ')'], ['%20', '%28', '%29'], $img->path) }}" alt="{{ $img->alt_text }}" class="w-full h-full object-cover" loading="lazy" />
                </div>

                {{-- Overlay actions --}}
                <div class="absolute inset-0 bg-black/0 group-hover:bg-black/40 transition-colors flex items-center justify-center gap-2 opacity-0 group-hover:opacity-100">
                    {{-- Set as primary --}}
                    <form method="POST" action="{{ route('admin.images.primary', [$product, $img]) }}" class="inline">
                        @csrf
                        <button type="submit" class="rounded-full bg-white/90 p-2 text-gray-700 hover:bg-white shadow-sm" title="Set as primary">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                        </button>
                    </form>

                    {{-- Delete --}}
                    <form method="POST" action="{{ route('admin.images.destroy', [$product, $img]) }}"
                          onsubmit="return confirm('Delete this image?')" class="inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="rounded-full bg-white/90 p-2 text-red-600 hover:bg-white shadow-sm" title="Delete">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </div>

                {{-- Info row --}}
                <div class="p-2 text-xs">
                    <form method="POST" action="{{ route('admin.images.update', [$product, $img]) }}" class="space-y-1.5">
                        @csrf @method('PUT')
                        <input type="text" name="alt_text" value="{{ $img->alt_text }}" placeholder="Alt text"
                               class="w-full rounded border-gray-200 text-xs py-1 px-1.5 focus:border-brand-500 outline-none" />

                        @if($productVariants->isNotEmpty())
                        <select name="variant_id" class="w-full rounded border-gray-200 text-xs py-1 px-1 focus:border-brand-500 outline-none">
                            <option value="">Product-level</option>
                            @foreach($productVariants->sortBy(['color','size']) as $v)
                                <option value="{{ $v->id }}" {{ $img->variant_id == $v->id ? 'selected' : '' }}>{{ $v->color }}/{{ $v->size }}</option>
                            @endforeach
                        </select>
                        @endif

                        <button type="submit" class="text-brand-700 hover:underline text-xs">Save</button>
                    </form>

                    @if($product->image === $img->path)
                        <span class="inline-flex items-center rounded-full bg-green-100 px-1.5 py-0.5 text-xs text-green-700 mt-1">Primary</span>
                    @endif
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @else
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-8 text-center">
        <p class="text-gray-500 text-sm">No images uploaded yet.</p>
    </div>
    @endif
</div>

@push('scripts')
<script>
function imageUploader() {
    return {
        dragover: false,
        previews: [],
        handleDrop(e) {
            this.dragover = false;
            const dt = e.dataTransfer;
            if (dt.files.length > 0) {
                this.$refs.fileInput.files = dt.files;
                this.previewFiles({ target: { files: dt.files } });
            }
        },
        previewFiles(e) {
            this.previews = [];
            const files = e.target.files;
            for (let i = 0; i < files.length; i++) {
                if (files[i].type.startsWith('image/')) {
                    const url = URL.createObjectURL(files[i]);
                    this.previews.push(url);
                }
            }
        }
    }
}

function imageGrid() {
    let draggedId = null;

    return {
        dragStart(e, id) {
            draggedId = id;
            e.dataTransfer.effectAllowed = 'move';
        },
        dragOver(e) {
            e.dataTransfer.dropEffect = 'move';
        },
        drop(e, targetId) {
            if (draggedId === targetId) return;

            // Get current order
            const grid = document.getElementById('image-grid');
            const items = [...grid.querySelectorAll('[data-image-id]')];
            const order = items.map(el => parseInt(el.dataset.imageId));

            // Reorder
            const fromIdx = order.indexOf(draggedId);
            const toIdx = order.indexOf(targetId);
            order.splice(fromIdx, 1);
            order.splice(toIdx, 0, draggedId);

            // Reorder DOM
            order.forEach(id => {
                const el = grid.querySelector(`[data-image-id="${id}"]`);
                if (el) grid.appendChild(el);
            });

            // Save via AJAX
            fetch("{{ route('admin.images.reorder', $product) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ order: order })
            });

            draggedId = null;
        }
    }
}
</script>
@endpush

