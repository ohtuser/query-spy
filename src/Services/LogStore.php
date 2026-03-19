<?php

namespace QuerySpy\Services;

use Illuminate\Support\Facades\File;

class LogStore
{
    protected string $path;

    public function __construct()
    {
        $this->path = rtrim(
        config('queryspy.storage_path') ?? storage_path('queryspy'),
            '/'
        );

        if (!File::isDirectory($this->path)) {
            File::makeDirectory($this->path, 0755, true);
            File::put($this->path . '/.gitignore', "*\n!.gitignore\n");
        }

        // Auto-add to storage/.gitignore so users don't have to do it manually
        $storageGitignore = storage_path('.gitignore');
        $line = '/queryspy/';

        if (File::exists($storageGitignore)) {
            $contents = File::get($storageGitignore);
            if (!str_contains($contents, $line)) {
                File::append($storageGitignore, PHP_EOL . $line . PHP_EOL);
            }
        } else {
            File::put($storageGitignore, $line . PHP_EOL);
        }
    }

    protected function filePath(): string
    {
        return $this->path . '/entries.json';
    }

    public function all(): array
    {
        try {
            $file = $this->filePath();
            if (!File::exists($file)) return [];
            $data = json_decode(File::get($file), true);
            return is_array($data) ? $data : [];
        } catch (\Throwable) {
            return [];
        }
    }

    public function push(array $entry): void
    {
        $entries = $this->all();
        array_unshift($entries, $entry);
        $entries = array_slice($entries, 0, (int) config('queryspy.max_log_entries', 200));
        File::put($this->filePath(), json_encode($entries, JSON_PRETTY_PRINT));
    }

    public function clear(): void
    {
        File::put($this->filePath(), '[]');
    }

    public function stats(): array
    {
        $entries = $this->all();

        if (empty($entries)) {
            return ['total' => 0, 'critical' => 0, 'warning' => 0, 'notice' => 0, 'ok' => 0,
                    'avg_queries' => 0, 'avg_duration' => 0, 'avg_memory_mb' => 0,
                    'avg_score' => 0, 'total_n_plus_one' => 0];
        }

        $levels = array_count_values(
            array_map(fn($e) => $e['report']['severity_level'] ?? 'ok', $entries)
        );
        $reports = array_column($entries, 'report');

        return [
            'total'            => count($entries),
            'critical'         => $levels['critical'] ?? 0,
            'warning'          => $levels['warning']  ?? 0,
            'notice'           => $levels['notice']   ?? 0,
            'ok'               => $levels['ok']       ?? 0,
            'avg_queries'      => round(array_sum(array_column($reports, 'total_queries')) / count($entries), 1),
            'avg_duration'     => round(array_sum(array_column($entries, 'duration_s'))    / count($entries), 3),
            'avg_memory_mb'    => round(array_sum(array_column($reports, 'peak_memory_mb'))/ count($entries), 1),
            'avg_score'        => round(array_sum(array_column($reports, 'severity_score'))/ count($entries), 0),
            'total_n_plus_one' => array_sum(array_map(fn($e) => count($e['report']['n_plus_one'] ?? []), $entries)),
        ];
    }
}
