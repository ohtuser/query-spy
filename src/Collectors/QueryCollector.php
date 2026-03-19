<?php

namespace QuerySpy\Collectors;

class QueryCollector
{
    protected static array $queries = [];
    protected static ?float $startMemory = null;

    public static function start(): void
    {
        self::$queries = [];
        self::$startMemory = memory_get_usage(true);
    }

    public static function add(array $query): void
    {
        foreach (config('queryspy.ignore_sql', []) as $pattern) {
            if (preg_match($pattern, $query['sql'])) {
                return;
            }
        }
        self::$queries[] = $query;
    }

    public static function all(): array
    {
        return self::$queries;
    }

    public static function count(): int
    {
        return count(self::$queries);
    }

    public static function totalTime(): float
    {
        return array_sum(array_column(self::$queries, 'time'));
    }

    public static function peakMemoryMb(): float
    {
        return round(memory_get_peak_usage(true) / 1024 / 1024, 2);
    }

    public static function memoryGrowthMb(): float
    {
        if (self::$startMemory === null) return 0;
        return round((memory_get_usage(true) - self::$startMemory) / 1024 / 1024, 2);
    }

    public static function reset(): void
    {
        self::$queries = [];
        self::$startMemory = null;
    }
}
