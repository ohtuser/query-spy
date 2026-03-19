<?php

namespace QuerySpy\Console\Commands;

use Illuminate\Console\Command;
use QuerySpy\Services\LogStore;

class SummaryCommand extends Command
{
    protected $signature   = 'queryspy:summary {--limit=20}';
    protected $description = 'Show a summary table of recent QuerySpy entries';

    public function handle(LogStore $store): int
    {
        $entries = array_slice($store->all(), 0, (int) $this->option('limit'));
        $stats   = $store->stats();

        if (empty($entries)) {
            $this->warn('No entries found. Browse your app to generate data.');
            return self::SUCCESS;
        }

        $this->newLine();
        $this->line('<fg=bright-magenta;options=bold> QuerySpy Summary</>');
        $this->newLine();

        $this->table(['Stat', 'Value'], [
            ['Total',    $stats['total']],
            ['<fg=red>Critical</>',  $stats['critical']],
            ['<fg=yellow>Warning</>', $stats['warning']],
            ['<fg=blue>Notice</>',   $stats['notice']],
            ['<fg=green>Clean</>',   $stats['ok']],
            ['Avg Queries',   $stats['avg_queries']],
            ['Avg Duration',  $stats['avg_duration'] . 's'],
            ['Avg Memory',    $stats['avg_memory_mb'] . ' MB'],
        ]);

        $this->newLine();

        $rows = array_map(function ($e) {
            $sev   = $e['report']['severity_level'] ?? 'ok';
            $color = match ($sev) {
                'critical' => 'red', 'warning' => 'yellow', 'notice' => 'blue', default => 'green',
            };
            $r      = $e['report'];
            $issues = implode(', ', array_filter([
                count($r['n_plus_one']        ?? []) ? 'N+1×'  . count($r['n_plus_one'])        : null,
                count($r['slow_queries']      ?? []) ? 'Slow×' . count($r['slow_queries'])      : null,
                count($r['duplicate_queries'] ?? []) ? 'Dupe×' . count($r['duplicate_queries']) : null,
                ($r['memory_alert'] ?? false)        ? 'Mem'                                     : null,
            ]));
            return [
                "<fg={$color}>" . strtoupper($sev) . '</>',
                $e['method'],
                mb_strimwidth(parse_url($e['url'], PHP_URL_PATH), 0, 45, '…'),
                $e['duration_s'] . 's',
                $r['total_queries'],
                $issues ?: '—',
            ];
        }, $entries);

        $this->table(['Sev', 'Method', 'Path', 'Time', 'Queries', 'Issues'], $rows);
        $this->newLine();
        return self::SUCCESS;
    }
}
