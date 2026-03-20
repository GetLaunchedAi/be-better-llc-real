@extends('admin.layouts.app')

@section('content')
@php
    $imageSlots = [
        ['field' => 'hero_image', 'alt' => 'hero_alt', 'label' => 'Hero image'],
        ['field' => 'new_arrivals_banner_image', 'alt' => 'new_arrivals_banner_alt', 'label' => 'New Arrivals banner image'],
        ['field' => 'grip_main_image', 'alt' => 'grip_main_alt', 'label' => 'Grip section main image'],
        ['field' => 'grip_tile_1_image', 'alt' => 'grip_tile_1_alt', 'label' => 'Grip tile 1 image'],
        ['field' => 'grip_tile_2_image', 'alt' => 'grip_tile_2_alt', 'label' => 'Grip tile 2 image'],
        ['field' => 'grip_tile_3_image', 'alt' => 'grip_tile_3_alt', 'label' => 'Grip tile 3 image'],
        ['field' => 'holiday_tile_1_image', 'alt' => 'holiday_tile_1_alt', 'label' => 'Holiday deal tile 1 image'],
        ['field' => 'holiday_tile_2_image', 'alt' => 'holiday_tile_2_alt', 'label' => 'Holiday deal tile 2 image'],
        ['field' => 'featured_tile_1_image', 'alt' => 'featured_tile_1_alt', 'label' => 'Featured promo tile 1 image'],
        ['field' => 'featured_tile_2_image', 'alt' => 'featured_tile_2_alt', 'label' => 'Featured promo tile 2 image'],
        ['field' => 'featured_tile_3_image', 'alt' => 'featured_tile_3_alt', 'label' => 'Featured promo tile 3 image'],
    ];
@endphp

<div class="mb-6">
    <nav class="text-sm text-gray-500 mb-2">
        <a href="{{ route('admin.products.index') }}" class="hover:text-brand-700">Admin</a>
        <span class="mx-1">/</span>
        <span class="text-gray-900">Homepage Content</span>
    </nav>
    <h1 class="text-2xl font-bold text-gray-900">Homepage Marketing Images</h1>
    <p class="text-sm text-gray-500 mt-1">Upload and update homepage media without editing template files.</p>
</div>

<form method="POST" action="{{ route('admin.homepage-content.update') }}" enctype="multipart/form-data" class="space-y-5">
    @csrf
    @method('PUT')

    @foreach($imageSlots as $slot)
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-5">
            <h2 class="text-base font-semibold text-gray-900">{{ $slot['label'] }}</h2>

            <div class="mt-3 grid gap-4 md:grid-cols-[160px_1fr] md:items-start">
                <div class="aspect-square rounded-md border border-gray-200 overflow-hidden bg-gray-50">
                    <img
                        src="{{ str_replace([' ', '(', ')'], ['%20', '%28', '%29'], $content->{$slot['field']} ?: '/assets/img/placeholder.jpg') }}"
                        alt=""
                        class="h-full w-full object-cover"
                        loading="lazy"
                    />
                </div>

                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Replace image</label>
                        <input
                            type="file"
                            name="{{ $slot['field'] }}"
                            accept="image/*"
                            class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none"
                        />
                        <p class="text-xs text-gray-400 mt-1">Accepted: JPEG, PNG, GIF, WebP. Max 5MB.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Alt text</label>
                        <input
                            type="text"
                            name="{{ $slot['alt'] }}"
                            value="{{ old($slot['alt'], $content->{$slot['alt']} ?? '') }}"
                            class="block w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-brand-500 focus:outline-none"
                            maxlength="255"
                            placeholder="Optional descriptive alt text"
                        />
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <div class="pt-1">
        <button type="submit" class="rounded-md bg-brand-900 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-800">
            Save homepage content
        </button>
    </div>
</form>
@endsection
