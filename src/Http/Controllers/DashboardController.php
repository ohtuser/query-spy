<?php

namespace QuerySpy\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use QuerySpy\Services\LogStore;

class DashboardController extends Controller
{
    protected LogStore $store;

    public function __construct(LogStore $store)
    {
        $this->store = $store;
    }

    public function index(Request $request)
    {
        if ($this->unauthorized($request)) {
            return view('queryspy::auth');
        }
        $entries = $this->store->all();
        $stats   = $this->store->stats();
        return view('queryspy::dashboard', compact('entries', 'stats'));
    }

    public function clear(Request $request)
    {
        if ($this->unauthorized($request)) abort(403);
        $this->store->clear();
        return redirect()->route('queryspy.dashboard', $request->only('password'))
            ->with('message', 'All QuerySpy logs cleared.');
    }

    public function api(Request $request)
    {
        if ($this->unauthorized($request)) abort(403);

        $entries = $this->store->all();

        if ($sev = $request->get('severity')) {
            $entries = array_values(array_filter($entries,
                fn($e) => ($e['report']['severity_level'] ?? '') === $sev
            ));
        }

        $perPage = max(1, min(200, (int) $request->get('per_page', 50)));
        $page    = max(1, (int) $request->get('page', 1));
        $total   = count($entries);

        return response()->json([
            'data'  => array_slice($entries, ($page - 1) * $perPage, $perPage),
            'stats' => $this->store->stats(),
            'meta'  => [
                'total'     => $total,
                'page'      => $page,
                'per_page'  => $perPage,
                'last_page' => (int) ceil($total / $perPage),
            ],
        ]);
    }

    public function exportCsv(Request $request)
    {
        if ($this->unauthorized($request)) abort(403);

        $entries  = $this->store->all();
        $filename = 'queryspy_' . now()->format('Ymd_His') . '.csv';

        return response()->stream(function () use ($entries) {
            $h = fopen('php://output', 'w');
            fputcsv($h, [
                'ID', 'Timestamp', 'Method', 'URL', 'Route',
                'Duration(s)', 'Queries', 'DB Time(ms)', 'Memory(MB)',
                'Severity', 'Score', 'N+1', 'Slow', 'Dupes',
                'Memory Alert', 'Too Many Queries', 'SELECT*',
            ]);
            foreach ($entries as $e) {
                $r = $e['report'];
                fputcsv($h, [
                    $e['id'], $e['timestamp'], $e['method'], $e['url'], $e['route'] ?? '',
                    $e['duration_s'], $r['total_queries'], $r['total_time_ms'], $r['peak_memory_mb'],
                    $r['severity_level'], $r['severity_score'],
                    count($r['n_plus_one']        ?? []),
                    count($r['slow_queries']      ?? []),
                    count($r['duplicate_queries'] ?? []),
                    ($r['memory_alert']     ?? false) ? 'Yes' : 'No',
                    ($r['too_many_queries'] ?? false) ? 'Yes' : 'No',
                    count($r['select_star'] ?? []),
                ]);
            }
            fclose($h);
        }, 200, [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    protected function unauthorized(Request $request): bool
    {
        $pw = config('queryspy.password');
        return $pw && $request->get('password') !== $pw;
    }
}
