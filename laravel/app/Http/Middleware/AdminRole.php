<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminRole
{
    /**
     * Require the 'admin' role (not just editor) for destructive operations.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user || ! $user->isAdmin()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Forbidden — admin role required'], 403);
            }

            return back()->with('error', 'You do not have permission for this action.');
        }

        return $next($request);
    }
}

