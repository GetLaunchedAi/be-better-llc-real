<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CanonicalRedirect
{
    /**
     * Normalize trailing slashes: redirect `/products/slug/` → `/products/slug`.
     * Also handles double-slash cleanup and lowercase enforcement.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $path = $request->getPathInfo();

        // Strip trailing slash (except root "/")
        if ($path !== '/' && str_ends_with($path, '/')) {
            $canonical = rtrim($path, '/');

            // Preserve query string
            $qs = $request->getQueryString();
            $url = $canonical . ($qs ? '?' . $qs : '');

            return redirect($url, 301);
        }

        // Lowercase enforcement — redirect uppercase paths
        $lower = strtolower($path);
        if ($path !== $lower) {
            $qs = $request->getQueryString();
            $url = $lower . ($qs ? '?' . $qs : '');

            return redirect($url, 301);
        }

        return $next($request);
    }
}

