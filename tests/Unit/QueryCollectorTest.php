<?php

namespace QuerySpy\Tests\Unit;

use PHPUnit\Framework\TestCase;
use QuerySpy\Collectors\QueryCollector;

class QueryCollectorTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        QueryCollector::reset();
    }

    public function test_starts_empty(): void
    {
        $this->assertCount(0, QueryCollector::all());
        $this->assertEquals(0, QueryCollector::count());
    }

    public function test_adds_queries(): void
    {
        QueryCollector::add(['sql' => 'select 1', 'full_sql' => 'select 1', 'bindings' => [], 'time' => 5, 'connection' => 'mysql', 'file' => null, 'line' => null, 'caller' => null, 'timestamp' => microtime(true)]);
        QueryCollector::add(['sql' => 'select 2', 'full_sql' => 'select 2', 'bindings' => [], 'time' => 8, 'connection' => 'mysql', 'file' => null, 'line' => null, 'caller' => null, 'timestamp' => microtime(true)]);

        $this->assertEquals(2, QueryCollector::count());
        $this->assertEquals(13, QueryCollector::totalTime());
    }

    public function test_reset_clears_queries(): void
    {
        QueryCollector::add(['sql' => 'select 1', 'full_sql' => 'select 1', 'bindings' => [], 'time' => 5, 'connection' => 'mysql', 'file' => null, 'line' => null, 'caller' => null, 'timestamp' => microtime(true)]);
        QueryCollector::reset();

        $this->assertEquals(0, QueryCollector::count());
    }

    public function test_peak_memory_is_positive(): void
    {
        $this->assertGreaterThan(0, QueryCollector::peakMemoryMb());
    }
}
