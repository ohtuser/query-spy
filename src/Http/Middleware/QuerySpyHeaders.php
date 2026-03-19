<?php

namespace QuerySpy\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use QuerySpy\Services\QueryAnalyzer;
use Symfony\Component\HttpFoundation\Response;

class QuerySpyHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if (!config('queryspy.inject_headers', true)) {
            return $response;
        }

        try {
            $report = QueryAnalyzer::analyze();
            $response->headers->set('X-QuerySpy-Queries',    $report['total_queries']);
            $response->headers->set('X-QuerySpy-Time-Ms',    $report['total_time_ms']);
            $response->headers->set('X-QuerySpy-Memory-MB',  $report['peak_memory_mb']);
            $response->headers->set('X-QuerySpy-Severity',   $report['severity_level']);
            $response->headers->set('X-QuerySpy-Score',      $report['severity_score']);
            $response->headers->set('X-QuerySpy-N1',         count($report['n_plus_one']));
            $response->headers->set('X-QuerySpy-Slow',       count($report['slow_queries']));
            $response->headers->set('X-QuerySpy-Duplicates', count($report['duplicate_queries']));
        } catch (\Throwable) {
            // Never break the app
        }

        return $response;
    }
}
