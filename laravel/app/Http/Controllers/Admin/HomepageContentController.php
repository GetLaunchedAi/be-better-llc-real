<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomepageContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class HomepageContentController extends Controller
{
    public function edit()
    {
        $content = HomepageContent::singleton();

        return view('admin.homepage-content.edit', compact('content'));
    }

    public function update(Request $request)
    {
        $content = HomepageContent::singleton();

        $validated = $request->validate([
            'hero_image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
            'hero_alt' => 'nullable|string|max:255',
            'new_arrivals_banner_image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
            'new_arrivals_banner_alt' => 'nullable|string|max:255',
            'grip_main_image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
            'grip_main_alt' => 'nullable|string|max:255',
            'grip_tile_1_image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
            'grip_tile_1_alt' => 'nullable|string|max:255',
            'grip_tile_2_image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
            'grip_tile_2_alt' => 'nullable|string|max:255',
            'grip_tile_3_image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
            'grip_tile_3_alt' => 'nullable|string|max:255',
            'holiday_tile_1_image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
            'holiday_tile_1_alt' => 'nullable|string|max:255',
            'holiday_tile_2_image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
            'holiday_tile_2_alt' => 'nullable|string|max:255',
            'featured_tile_1_image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
            'featured_tile_1_alt' => 'nullable|string|max:255',
            'featured_tile_2_image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
            'featured_tile_2_alt' => 'nullable|string|max:255',
            'featured_tile_3_image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
            'featured_tile_3_alt' => 'nullable|string|max:255',
        ]);

        $slots = [
            'hero_image',
            'new_arrivals_banner_image',
            'grip_main_image',
            'grip_tile_1_image',
            'grip_tile_2_image',
            'grip_tile_3_image',
            'holiday_tile_1_image',
            'holiday_tile_2_image',
            'featured_tile_1_image',
            'featured_tile_2_image',
            'featured_tile_3_image',
        ];

        foreach ($slots as $slot) {
            unset($validated[$slot]);
        }

        foreach ($slots as $slot) {
            if (! $request->hasFile($slot)) {
                continue;
            }

            $file = $request->file($slot);
            $storedPath = $file->storeAs(
                'uploads/homepage',
                now()->timestamp . '-' . $slot . '.' . $file->getClientOriginalExtension(),
                'public'
            );
            $newPath = '/storage/' . $storedPath;

            $oldPath = $content->{$slot};
            if (is_string($oldPath) && str_starts_with($oldPath, '/storage/uploads/homepage/')) {
                Storage::disk('public')->delete(str_replace('/storage/', '', $oldPath));
            }

            $validated[$slot] = $newPath;
        }

        $content->update($validated);

        Cache::forget('homepage:content:json');

        return redirect()
            ->route('admin.homepage-content.edit')
            ->with('success', 'Homepage marketing content updated.');
    }
}
