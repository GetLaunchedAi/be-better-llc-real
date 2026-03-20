@extends('admin.layouts.app')

@section('content')
<div class="mb-6">
    <nav class="text-sm text-gray-500 mb-2">
        <a href="{{ route('admin.products.index') }}" class="hover:text-brand-700">Admin</a>
        <span class="mx-1">/</span>
        <span class="text-gray-900">Pages</span>
    </nav>
    <h1 class="text-2xl font-bold text-gray-900">Pages</h1>
    <p class="text-sm text-gray-500 mt-1">Manage page content and control which pages appear in the site navigation.</p>
</div>

{{-- Collections --}}
<div class="mb-8">
    <h2 class="text-lg font-semibold text-gray-900 mb-3">Collections</h2>
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Page</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">URL</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">In Nav</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nav Label</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Images</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($collections as $page)
                <tr class="{{ $page->in_nav ? '' : 'bg-gray-50/50' }}">
                    <td class="px-4 py-3">
                        <span class="text-sm font-medium text-gray-900">{{ $page->title }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <a href="{{ $page->url }}" target="_blank" class="text-sm text-brand-700 hover:underline">{{ $page->url }}</a>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <form method="POST" action="{{ route('admin.pages.toggle-nav', $page) }}" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-full transition {{ $page->in_nav ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-100 text-gray-400 hover:bg-gray-200' }}" title="{{ $page->in_nav ? 'Remove from nav' : 'Add to nav' }}">
                                @if($page->in_nav)
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                                @else
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                                @endif
                            </button>
                        </form>
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-sm text-gray-600">{{ $page->nav_label ?: '—' }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-1">
                            <span class="inline-flex items-center rounded px-1.5 py-0.5 text-xs {{ $page->hero_image ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-400' }}">Hero</span>
                            <span class="inline-flex items-center rounded px-1.5 py-0.5 text-xs {{ $page->content_image ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-400' }}">Content</span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.pages.edit', $page) }}" class="text-sm font-medium text-brand-700 hover:text-brand-900">Edit</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Static Pages --}}
<div class="mb-8">
    <h2 class="text-lg font-semibold text-gray-900 mb-3">Static Pages</h2>
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Page</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">URL</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">In Nav</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nav Label</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Images</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($statics as $page)
                <tr class="{{ $page->in_nav ? '' : 'bg-gray-50/50' }}">
                    <td class="px-4 py-3">
                        <span class="text-sm font-medium text-gray-900">{{ $page->title }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <a href="{{ $page->url }}" target="_blank" class="text-sm text-brand-700 hover:underline">{{ $page->url }}</a>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <form method="POST" action="{{ route('admin.pages.toggle-nav', $page) }}" class="inline">
                            @csrf
                            <button type="submit" class="inline-flex items-center justify-center w-8 h-8 rounded-full transition {{ $page->in_nav ? 'bg-green-100 text-green-700 hover:bg-green-200' : 'bg-gray-100 text-gray-400 hover:bg-gray-200' }}" title="{{ $page->in_nav ? 'Remove from nav' : 'Add to nav' }}">
                                @if($page->in_nav)
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg>
                                @else
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M6 18L18 6M6 6l12 12"/></svg>
                                @endif
                            </button>
                        </form>
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-sm text-gray-600">{{ $page->nav_label ?: '—' }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-1">
                            <span class="inline-flex items-center rounded px-1.5 py-0.5 text-xs {{ $page->hero_image ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-400' }}">Hero</span>
                            <span class="inline-flex items-center rounded px-1.5 py-0.5 text-xs {{ $page->content_image ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-400' }}">Content</span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('admin.pages.edit', $page) }}" class="text-sm font-medium text-brand-700 hover:text-brand-900">Edit</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
