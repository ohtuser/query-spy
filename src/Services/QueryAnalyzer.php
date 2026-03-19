<?php

namespace QuerySpy\Services;

use QuerySpy\Collectors\QueryCollector;

class QueryAnalyzer
{
    public static function analyze(): array
    {
        $queries   = QueryCollector::all();
        $totalTime = QueryCollector::totalTime();
        $peakMem   = QueryCollector::peakMemoryMb();
        $count     = count($queries);

        $slow       = self::slowQueries($queries);
        $duplicates = self::duplicateQueries($queries);
        $nPlusOne   = self::detectNPlusOne($queries);
        $indexHints = self::missingIndexHints($queries);
        $selectStar = self::detectSelectStar($queries);

        $score = self::severityScore($count, $slow, $duplicates, $nPlusOne, $peakMem);

        return [
            'total_queries'       => $count,
            'total_time_ms'       => round($totalTime, 2),
            'peak_memory_mb'      => $peakMem,
            'memory_growth_mb'    => QueryCollector::memoryGrowthMb(),
            'slow_queries'        => $slow,
            'duplicate_queries'   => $duplicates,
            'n_plus_one'          => $nPlusOne,
            'missing_index_hints' => $indexHints,
            'select_star'         => $selectStar,
            'too_many_queries'    => $count > config('queryspy.max_queries', 50),
            'memory_alert'        => $peakMem > config('queryspy.memory_threshold_mb', 64),
            'severity_score'      => $score,
            'severity_level'      => self::severityLabel($score),
            'queries'             => $queries,
        ];
    }

    protected static function slowQueries(array $queries): array
    {
        $threshold = config('queryspy.slow_query_ms', 100);
        return array_values(array_filter($queries, fn($q) => $q['time'] > $threshold));
    }

    protected static function duplicateQueries(array $queries): array
    {
        $map = [];
        foreach ($queries as $q) {
            $key = trim(preg_replace('/\s+/', ' ', $q['sql']));
            if (!isset($map[$key])) {
                $map[$key] = ['sql' => $q['sql'], 'count' => 0, 'file' => $q['file'], 'line' => $q['line']];
            }
            $map[$key]['count']++;
        }
        return array_values(array_filter($map, fn($e) => $e['count'] > 1));
    }

    protected static function detectNPlusOne(array $queries): array
    {
        $threshold = config('queryspy.n_plus_one_threshold', 5);
        $patterns  = [];

        foreach ($queries as $q) {
            $key = preg_replace(['/\s+/', "/'[^']*'/", '/\b\d+\b/'], [' ', '?', '?'], $q['sql']);
            $patterns[$key][] = ['full_sql' => $q['full_sql'], 'time' => $q['time'], 'file' => $q['file'], 'line' => $q['line']];
        }

        $result = [];
        foreach ($patterns as $pattern => $hits) {
            if (count($hits) >= $threshold) {
                $result[] = [
                    'pattern'    => $pattern,
                    'count'      => count($hits),
                    'total_time' => round(array_sum(array_column($hits, 'time')), 2),
                    'file'       => $hits[0]['file'] ?? null,
                    'line'       => $hits[0]['line'] ?? null,
                    'suggestion' => 'Possible N+1 — consider eager-loading with with().',
                    'samples'    => array_slice($hits, 0, 3),
                ];
            }
        }
        return $result;
    }

    protected static function missingIndexHints(array $queries): array
    {
        $flagged = [];
        foreach ($queries as $q) {
            if (str_contains(strtolower($q['sql']), "like '%")) {
                $flagged[] = [
                    'sql'  => $q['sql'],
                    'file' => $q['file'],
                    'line' => $q['line'],
                    'hint' => 'Leading wildcard LIKE "%..." prevents index usage. Consider a full-text index.',
                ];
            }
        }
        return $flagged;
    }

    protected static function detectSelectStar(array $queries): array
    {
        $flagged = [];
        foreach ($queries as $q) {
            if (preg_match('/^\s*select\s+\*/i', $q['sql'])) {
                $flagged[] = [
                    'sql'  => $q['sql'],
                    'file' => $q['file'],
                    'line' => $q['line'],
                    'hint' => 'Avoid SELECT * — specify only the columns you need.',
                ];
            }
        }
        return $flagged;
    }

    protected static function severityScore(int $count, array $slow, array $duplicates, array $nPlusOne, float $peakMem): int
    {
        $score = 0;
        $max   = config('queryspy.max_queries', 50);

        if ($count > $max * 2)    $score += 30;
        elseif ($count > $max)    $score += 15;

        $score += min(count($slow) * 10, 30);
        $score += min(count($nPlusOne) * 15, 30);
        $score += min(count($duplicates) * 5, 10);

        $memThreshold = config('queryspy.memory_threshold_mb', 64);
        if ($peakMem > $memThreshold * 2) $score += 20;
        elseif ($peakMem > $memThreshold) $score += 10;

        return min($score, 100);
    }

    protected static function severityLabel(int $score): string
    {
        if ($score >= 70) return 'critical';
        if ($score >= 40) return 'warning';
        if ($score >= 10) return 'notice';
        return 'ok';
    }
}
