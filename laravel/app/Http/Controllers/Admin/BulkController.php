<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Product;
use Illuminate\Http\Request;

class BulkController extends Controller
{
    /**
     * Bulk update status for multiple products.
     */
    public function updateStatus(Request $request)
    {
        $request->validate([
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'integer|exists:products,id',
            'status' => 'required|in:active,draft,archived',
        ]);

        $ids = $request->input('product_ids');
        $status = $request->input('status');

        $count = Product::whereIn('id', $ids)->update(['status' => $status]);

        ActivityLog::log('bulk_status_update', null, [
            'product_ids' => $ids,
            'status' => $status,
            'count' => $count,
        ], "Bulk status → {$status}");

        return back()->with('success', "{$count} product(s) set to {$status}.");
    }

    /**
     * Bulk update base price for multiple products.
     */
    public function updatePrice(Request $request)
    {
        $request->validate([
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'integer|exists:products,id',
            'price' => 'required|numeric|min:0|max:99999.99',
        ]);

        $ids = $request->input('product_ids');
        $price = $request->input('price');

        $count = Product::whereIn('id', $ids)->update(['price' => $price]);

        ActivityLog::log('bulk_price_update', null, [
            'product_ids' => $ids,
            'price' => $price,
            'count' => $count,
        ], "Bulk price → \${$price}");

        return back()->with('success', "{$count} product(s) price updated to \${$price}.");
    }

    /**
     * Bulk delete products.
     */
    public function destroy(Request $request)
    {
        $request->validate([
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'integer|exists:products,id',
        ]);

        $ids = $request->input('product_ids');
        $count = Product::whereIn('id', $ids)->delete();

        ActivityLog::log('bulk_delete', null, [
            'product_ids' => $ids,
            'count' => $count,
        ], "Bulk delete ({$count} products)");

        return back()->with('success', "{$count} product(s) deleted.");
    }
}

