<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NavItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class NavController extends Controller
{
    public function edit()
    {
        $groups = NavItem::allGrouped();

        return view('admin.navigation.edit', [
            'primaryItems' => $groups['primary'],
            'metaItems' => $groups['meta'],
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'items' => 'present|array',
            'items.*.id' => 'nullable|integer|exists:nav_items,id',
            'items.*.label' => 'required|string|max:100',
            'items.*.url' => 'required|string|max:255',
            'items.*.type' => 'required|in:primary,meta',
            'items.*.is_visible' => 'nullable',
            'items.*.sort_order' => 'required|integer|min:0',
        ]);

        DB::transaction(function () use ($validated) {
            $submittedIds = collect($validated['items'])
                ->pluck('id')
                ->filter()
                ->all();

            NavItem::whereNotIn('id', $submittedIds)->delete();

            foreach ($validated['items'] as $data) {
                $attrs = [
                    'label' => $data['label'],
                    'url' => $data['url'],
                    'type' => $data['type'],
                    'sort_order' => $data['sort_order'],
                    'is_visible' => isset($data['is_visible']) && $data['is_visible'],
                ];

                if (! empty($data['id'])) {
                    NavItem::where('id', $data['id'])->update($attrs);
                } else {
                    NavItem::create($attrs);
                }
            }
        });

        Cache::forget('nav:items:json');

        return redirect()
            ->route('admin.navigation.edit')
            ->with('success', 'Navigation updated.');
    }
}
