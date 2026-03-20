<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Support\Facades\Cache;

class NavItemController extends Controller
{
    public function json()
    {
        $ttl = config('cache.ttl.home_page', 600);
        $payload = Cache::remember('nav:items:json', $ttl, function () {
            return Page::toNavPayload();
        });

        return response()->json($payload);
    }
}
