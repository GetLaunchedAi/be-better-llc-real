<?php

namespace App\Http\Controllers;

use App\Models\HomepageContent;
use Illuminate\Support\Facades\Cache;

class HomepageContentController extends Controller
{
    /**
     * JSON Feed — /homepage-content.json
     * Returns CMS-managed homepage marketing images.
     */
    public function json()
    {
        $ttl = config('cache.ttl.home_page', 600);
        $payload = Cache::remember('homepage:content:json', $ttl, function () {
            $content = HomepageContent::singleton();

            return $content->toPublicPayload();
        });

        return response()->json($payload);
    }
}
