<?php

namespace QuerySpy\Console\Commands;

use Illuminate\Console\Command;
use QuerySpy\Services\LogStore;

class ExportCommand extends Command
{
    protected $signature   = 'queryspy:export {--format=csv} {--output=}';
    protected $description = 'Export QuerySpy logs to CSV or JSON';

    public function handle(LogStore $store): int
    {
        $format  = strtolower($this->option('format'));
        $entries = $store->all();

        if (empty($entries)) {
            $this->warn('No entries to export.');
            return self::SUCCESS;
        }

        $default = storage_path('queryspy/export_' . now()->format('Ymd_His') . '.' . $format);
        $output  = $this->option('output') ?: $default;

        if ($format === 'json') {
            file_put_contents($output, json_encode($entries, JSON_PRETTY_PRINT));
        } elseif ($format === 'csv') {
            $h = fopen($output, 'w');
            fputcsv($h, ['ID', 'Timestamp', 'Method', 'URL', 'Route', 'Duration(s)',
                         'Queries', 'DB Time(ms)', 'Memory(MB)', 'Severity', 'Score',
                         'N+1', 'Slow', 'Dupes', 'Memory Alert', 'Too Many', 'SELECT*']);
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
        } else {
            $this->error('Unsupported format. Use csv or json.');
            return self::FAILURE;
        }

        $this->info('Exported ' . count($entries) . ' entries → ' . $output);
        return self::SUCCESS;
    }
}
