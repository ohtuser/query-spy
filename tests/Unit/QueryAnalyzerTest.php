<?php

namespace QuerySpy\Tests\Unit;

use PHPUnit\Framework\TestCase;
use QuerySpy\Collectors\QueryCollector;
use QuerySpy\Services\QueryAnalyzer;

class QueryAnalyzerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        QueryCollector::reset();
    }

    public function test_empty_returns_ok_severity(): void
    {
        $result = QueryAnalyzer::analyze();

        $this->assertEquals(0, $result['total_queries']);
        $this->assertEquals('ok', $result['severity_level']);
        $this->assertEquals(0, $result['severity_score']);
    }

    public function test_detects_slow_queries(): void
    {
        QueryCollector::add($this->fakeQuery('select * from users', 250));

        $result = QueryAnalyzer::analyze();

        $this->assertCount(1, $result['slow_queries']);
        $this->assertEquals('select * from users', $result['slow_queries'][0]['sql']);
    }

    public function test_detects_duplicate_queries(): void
    {
        QueryCollector::add($this->fakeQuery('select * from users where id = ?', 10));
        QueryCollector::add($this->fakeQuery('select * from users where id = ?', 10));
        QueryCollector::add($this->fakeQuery('select * from users where id = ?', 10));

        $result = QueryAnalyzer::analyze();

        $this->assertCount(1, $result['duplicate_queries']);
        $this->assertEquals(3, $result['duplicate_queries'][0]['count']);
    }

    public function test_detects_n_plus_one(): void
    {
        for ($i = 1; $i <= 6; $i++) {
            QueryCollector::add($this->fakeQuery("select * from posts where user_id = {$i}", 5));
        }

        $result = QueryAnalyzer::analyze();

        $this->assertNotEmpty($result['n_plus_one']);
        $this->assertGreaterThanOrEqual(6, $result['n_plus_one'][0]['count']);
    }

    public function test_detects_select_star(): void
    {
        QueryCollector::add($this->fakeQuery('select * from orders', 10));

        $result = QueryAnalyzer::analyze();

        $this->assertCount(1, $result['select_star']);
    }

    public function test_detects_leading_wildcard_like(): void
    {
        QueryCollector::add($this->fakeQuery("select id from users where name like '%john'", 15));

        $result = QueryAnalyzer::analyze();

        $this->assertCount(1, $result['missing_index_hints']);
    }

    public function test_too_many_queries_flag(): void
    {
        for ($i = 0; $i < 55; $i++) {
            QueryCollector::add($this->fakeQuery("select id from t where id = {$i}", 2));
        }

        $result = QueryAnalyzer::analyze();

        $this->assertTrue($result['too_many_queries']);
    }

    public function test_severity_score_increases_with_issues(): void
    {
        $resultClean = QueryAnalyzer::analyze();

        QueryCollector::reset();
        for ($i = 1; $i <= 10; $i++) {
            QueryCollector::add($this->fakeQuery("select * from users where id = {$i}", 200));
        }

        $resultIssues = QueryAnalyzer::analyze();

        $this->assertGreaterThan($resultClean['severity_score'], $resultIssues['severity_score']);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function fakeQuery(string $sql, float $time = 10): array
    {
        return [
            'sql'        => $sql,
            'full_sql'   => $sql,
            'bindings'   => [],
            'time'       => $time,
            'connection' => 'mysql',
            'file'       => '/app/Http/Controllers/UserController.php',
            'line'       => 42,
            'caller'     => 'App\Http\Controllers\UserController::index',
            'timestamp'  => microtime(true),
        ];
    }
}
