@extends('admin.layouts.app')

@section('content')
<div class="mb-6">
    <nav class="text-sm text-gray-500 mb-2">
        <a href="{{ route('admin.products.index') }}" class="hover:text-brand-700">Admin</a>
        <span class="mx-1">/</span>
        <a href="{{ route('admin.pages.index') }}" class="hover:text-brand-700">Pages</a>
        <span class="mx-1">/</span>
        <span class="text-gray-900">{{ $page->title }}</span>
    </nav>
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $page->title }}</h1>
            <p class="text-sm text-gray-500 mt-1">
                <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium {{ $page->type === 'collection' ? 'bg-purple-50 text-purple-700' : 'bg-blue-50 text-blue-700' }}">
                    {{ ucfirst($page->type) }}
                </span>
                <a href="{{ $page->url }}" target="_blank" class="ml-2 text-brand-700 hover:underline">{{ $page->url }} ↗</a>
            </p>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('admin.pages.update', $page) }}" enctype="multipart/form-data" class="space-y-6">
    @csrf
    @method('PUT')

    {{-- Navigation Settings --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5">
        <h2 class="text-base font-semibold text-gray-900 mb-4">Navigation Settings</h2>

        <div class="space-y-4">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="hidden" name="in_nav" value="0">
                <input type="checkbox" name="in_nav" value="1" {{ $page->in_nav ? 'checked' : '' }}
                       class="rounded border-gray-300 text-brand-700 focus:ring-brand-500 h-5 w-5">
                <div>
                    <span class="text-sm font-medium text-gray-900">Show in navigation</span>
                    <p class="text-xs text-gray-500">Display this page as a link in the site header</p>
                </div>
            </label>

            <div class="grid gap-4 sm:grid-cols-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Page Title</label>
                    <input type="text" name="title" value="{{ old('title', $page->title) }}"
                           class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none"
                           required maxlength="100">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nav Label</label>
                    <input type="text" name="nav_label" value="{{ old('nav_label', $page->nav_label) }}"
                           class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none"
                           maxlength="100" placeholder="Defaults to page title">
                    <p class="text-xs text-gray-400 mt-1">Text displayed in the nav bar</p>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nav Section</label>
                        <select name="nav_section"
                                class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                            <option value="primary" {{ $page->nav_section === 'primary' ? 'selected' : '' }}>Primary</option>
                            <option value="meta" {{ $page->nav_section === 'meta' ? 'selected' : '' }}>Meta / Footer</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Sort Order</label>
                        <input type="number" name="nav_sort_order" value="{{ old('nav_sort_order', $page->nav_sort_order) }}"
                               class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none"
                               min="0" required>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Page Content --}}
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5">
        <h2 class="text-base font-semibold text-gray-900 mb-4">Page Content</h2>

        <div class="space-y-5">
            {{-- Hero Image --}}
            <div>
                <h3 class="text-sm font-medium text-gray-700 mb-2">Hero Image</h3>
                <div class="grid gap-4 md:grid-cols-[160px_1fr] md:items-start">
                    <div class="aspect-square rounded-md border border-gray-200 overflow-hidden bg-gray-50">
                        @if($page->hero_image)
                            <img src="{{ $page->hero_image }}" alt="" class="h-full w-full object-cover" loading="lazy">
                        @else
                            <div class="h-full w-full flex items-center justify-center text-gray-300">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                        @endif
                    </div>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Upload image</label>
                            <input type="file" name="hero_image" accept="image/*"
                                   class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                            <p class="text-xs text-gray-400 mt-1">JPEG, PNG, GIF, WebP. Max 5MB.</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Alt text</label>
                            <input type="text" name="hero_alt" value="{{ old('hero_alt', $page->hero_alt) }}"
                                   class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none"
                                   maxlength="255" placeholder="Describe the image">
                        </div>
                        @if($page->hero_image)
                        <label class="flex items-center gap-2 text-sm text-red-600 cursor-pointer">
                            <input type="checkbox" name="remove_hero_image" value="1" class="rounded border-gray-300 text-red-600">
                            Remove current image
                        </label>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Content Image --}}
            <div>
                <h3 class="text-sm font-medium text-gray-700 mb-2">Content Image</h3>
                <div class="grid gap-4 md:grid-cols-[160px_1fr] md:items-start">
                    <div class="aspect-square rounded-md border border-gray-200 overflow-hidden bg-gray-50">
                        @if($page->content_image)
                            <img src="{{ $page->content_image }}" alt="" class="h-full w-full object-cover" loading="lazy">
                        @else
                            <div class="h-full w-full flex items-center justify-center text-gray-300">
                                <svg class="w-10 h-10" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                        @endif
                    </div>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Upload image</label>
                            <input type="file" name="content_image" accept="image/*"
                                   class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none">
                            <p class="text-xs text-gray-400 mt-1">JPEG, PNG, GIF, WebP. Max 5MB.</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Alt text</label>
                            <input type="text" name="content_image_alt" value="{{ old('content_image_alt', $page->content_image_alt) }}"
                                   class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none"
                                   maxlength="255" placeholder="Describe the image">
                        </div>
                        @if($page->content_image)
                        <label class="flex items-center gap-2 text-sm text-red-600 cursor-pointer">
                            <input type="checkbox" name="remove_content_image" value="1" class="rounded border-gray-300 text-red-600">
                            Remove current image
                        </label>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Description --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" rows="4"
                          class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none"
                          maxlength="5000" placeholder="Page description or content...">{{ old('description', $page->description) }}</textarea>
            </div>
        </div>
    </div>

    <div class="flex items-center gap-3 pt-1">
        <button type="submit" class="rounded-md bg-brand-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-800">
            Save page
        </button>
        <a href="{{ route('admin.pages.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Cancel</a>
    </div>
</form>
@endsection
