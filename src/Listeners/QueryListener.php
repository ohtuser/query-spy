<?php

namespace QuerySpy\Listeners;

use Illuminate\Database\Events\QueryExecuted;
use QuerySpy\Collectors\QueryCollector;

class QueryListener
{
    public function handle(QueryExecuted $query): void
    {
        $file   = null;
        $line   = null;
        $caller = null;

        foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 60) as $frame) {
            if (empty($frame['file'])) continue;
            $f = $frame['file'];
            if (str_contains($f, '/vendor/') || str_contains($f, 'QuerySpy')) continue;
            $file   = $f;
            $line   = $frame['line'] ?? null;
            $caller = isset($frame['class'])
                ? $frame['class'] . ($frame['type'] ?? '::') . ($frame['function'] ?? '')
                : ($frame['function'] ?? null);
            break;
        }

        $bindings = array_map(function ($b) {
            return is_string($b) && strlen($b) > 100 ? substr($b, 0, 100) . '...' : $b;
        }, $query->bindings);

        QueryCollector::add([
            'sql'        => $query->sql,
            'full_sql'   => self::buildFullSql($query->sql, $bindings),
            'bindings'   => $bindings,
            'time'       => $query->time,
            'connection' => $query->connectionName,
            'file'       => $file,
            'line'       => $line,
            'caller'     => $caller,
            'timestamp'  => microtime(true),
        ]);
    }

    protected static function buildFullSql(string $sql, array $bindings): string
    {
        foreach ($bindings as $binding) {
            $value = is_numeric($binding) ? $binding : "'" . addslashes((string) $binding) . "'";
            $sql   = preg_replace('/\?/', (string) $value, $sql, 1);
        }
        return $sql;
    }
}
