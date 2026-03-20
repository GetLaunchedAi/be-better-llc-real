<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class PageController extends Controller
{
    public function index()
    {
        $collections = Page::collections()->navOrdered()->get();
        $statics = Page::staticPages()->orderBy('title')->get();

        return view('admin.pages.index', compact('collections', 'statics'));
    }

    public function edit(Page $page)
    {
        return view('admin.pages.edit', compact('page'));
    }

    public function update(Request $request, Page $page)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:100',
            'nav_label' => 'nullable|string|max:100',
            'nav_section' => 'required|in:primary,meta',
            'nav_sort_order' => 'required|integer|min:0',
            'in_nav' => 'nullable',
            'hero_image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
            'hero_alt' => 'nullable|string|max:255',
            'content_image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
            'content_image_alt' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:5000',
        ]);

        $validated['in_nav'] = $request->boolean('in_nav');

        $imageSlots = ['hero_image', 'content_image'];
        foreach ($imageSlots as $slot) {
            unset($validated[$slot]);

            if (! $request->hasFile($slot)) {
                continue;
            }

            $file = $request->file($slot);
            $storedPath = $file->storeAs(
                'uploads/pages',
                now()->timestamp . '-' . $page->slug . '-' . $slot . '.' . $file->getClientOriginalExtension(),
                'public'
            );
            $newPath = '/storage/' . $storedPath;

            $oldPath = $page->{$slot};
            if (is_string($oldPath) && str_starts_with($oldPath, '/storage/uploads/pages/')) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $oldPath));
            }

            $validated[$slot] = $newPath;
        }

        if ($request->boolean('remove_hero_image') && ! $request->hasFile('hero_image')) {
            if ($page->hero_image && str_starts_with($page->hero_image, '/storage/uploads/pages/')) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $page->hero_image));
            }
            $validated['hero_image'] = null;
            $validated['hero_alt'] = null;
        }

        if ($request->boolean('remove_content_image') && ! $request->hasFile('content_image')) {
            if ($page->content_image && str_starts_with($page->content_image, '/storage/uploads/pages/')) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $page->content_image));
            }
            $validated['content_image'] = null;
            $validated['content_image_alt'] = null;
        }

        $page->update($validated);

        Cache::forget('nav:items:json');

        return redirect()
            ->route('admin.pages.edit', $page)
            ->with('success', 'Page updated.');
    }

    public function toggleNav(Request $request, Page $page)
    {
        $page->update(['in_nav' => ! $page->in_nav]);

        Cache::forget('nav:items:json');

        return redirect()
            ->route('admin.pages.index')
            ->with('success', $page->title . ($page->in_nav ? ' added to' : ' removed from') . ' navigation.');
    }
}
