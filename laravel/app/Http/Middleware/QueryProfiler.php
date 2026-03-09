<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Logs slow or excessive queries per request.
 *
 * Enable via .env: QUERY_LOG_ENABLED=true
 * Threshold:       QUERY_LOG_SLOW_MS=100
 * Max count warn:  QUERY_LOG_MAX_COUNT=20
 */
class QueryProfiler
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('app.debug') && ! env('QUERY_LOG_ENABLED', false)) {
            return $next($request);
        }

        DB::enableQueryLog();

        $response = $next($request);

        $queries = DB::getQueryLog();
        $totalTime = array_sum(array_column($queries, 'time'));
        $count = count($queries);
        $slowThreshold = (float) env('QUERY_LOG_SLOW_MS', 100);
        $maxCount = (int) env('QUERY_LOG_MAX_COUNT', 20);

        // Log slow individual queries
        foreach ($queries as $query) {
            if ($query['time'] >= $slowThreshold) {
                Log::channel('query')->warning('Slow query', [
                    'sql' => $query['query'],
                    'bindings' => $query['bindings'],
                    'time_ms' => $query['time'],
                    'url' => $request->fullUrl(),
                    'method' => $request->method(),
                ]);
            }
        }

        // Log request summary if too many queries (N+1 detection)
        if ($count > $maxCount) {
            Log::channel('query')->warning('High query count (possible N+1)', [
                'count' => $count,
                'total_ms' => round($totalTime, 2),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
            ]);
        }

        // Always log summary in debug mode
        if (config('app.debug')) {
            Log::channel('query')->debug('Request query summary', [
                'count' => $count,
                'total_ms' => round($totalTime, 2),
                'url' => $request->path(),
            ]);
        }

        DB::disableQueryLog();

        return $response;
    }
}

