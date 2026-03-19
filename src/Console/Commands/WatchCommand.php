<?php

namespace QuerySpy\Console\Commands;

use Illuminate\Console\Command;
use QuerySpy\Services\LogStore;

class WatchCommand extends Command
{
    protected $signature   = 'queryspy:watch {--interval=2}';
    protected $description = 'Watch QuerySpy logs in real-time (Ctrl+C to stop)';

    public function handle(LogStore $store): int
    {
        $interval  = max(1, (int) $this->option('interval'));
        $lastCount = 0;

        $this->newLine();
        $this->line("<fg=bright-magenta;options=bold> QuerySpy Watch — every {$interval}s (Ctrl+C to stop)</>");
        $this->newLine();

        while (true) {
            $entries = $store->all();
            $count   = count($entries);

            if ($count > $lastCount) {
                $new = array_slice($entries, 0, $count - $lastCount);
                foreach (array_reverse($new) as $e) {
                    $sev   = $e['report']['severity_level'] ?? 'ok';
                    $color = match ($sev) {
                        'critical' => 'red', 'warning' => 'yellow', 'notice' => 'blue', default => 'green',
                    };
                    $r    = $e['report'];
                    $path = mb_strimwidth(parse_url($e['url'], PHP_URL_PATH), 0, 60, '…');
                    $this->line(sprintf(
                        ' <fg=%s>[%s]</> %s %s  <fg=gray>%dq  %sms  %sMB</>',
                        $color, strtoupper($sev), $e['method'], $path,
                        $r['total_queries'], $r['total_time_ms'], $r['peak_memory_mb']
                    ));
                    foreach ($r['n_plus_one'] ?? [] as $np) {
                        $this->line(sprintf('   <fg=red>↳ N+1</> %d× %s', $np['count'], mb_strimwidth($np['pattern'], 0, 70, '…')));
                    }
                    foreach (array_slice($r['slow_queries'] ?? [], 0, 2) as $sq) {
                        $this->line(sprintf('   <fg=yellow>↳ Slow</> %sms %s', round($sq['time'], 1), mb_strimwidth($sq['sql'], 0, 60, '…')));
                    }
                }
                $lastCount = $count;
            }

            sleep($interval);
        }

        return self::SUCCESS;
    }
}
