<?php

namespace QuerySpy;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Events\QueryExecuted;
use QuerySpy\Collectors\QueryCollector;
use QuerySpy\Listeners\QueryListener;
use QuerySpy\Services\QueryAnalyzer;
use QuerySpy\Services\LogStore;
use QuerySpy\Console\Commands\ClearCommand;
use QuerySpy\Console\Commands\SummaryCommand;
use QuerySpy\Console\Commands\WatchCommand;
use QuerySpy\Console\Commands\ExportCommand;
use QuerySpy\Http\Middleware\QuerySpyHeaders;

class QuerySpyServiceProvider extends ServiceProvider
{
    protected float $startTime;

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/queryspy.php', 'queryspy');

        $this->app->singleton(LogStore::class, fn() => new LogStore());
        $this->app->singleton(QueryListener::class);

        if ($this->app->runningInConsole()) {
            $this->commands([
                ClearCommand::class,
                SummaryCommand::class,
                WatchCommand::class,
                ExportCommand::class,
            ]);
        }

        $this->app['router']->aliasMiddleware('queryspy.headers', QuerySpyHeaders::class);
    }

    public function boot(): void
    {
        $this->registerPublishables();

        // Always register views and routes so the dashboard is always accessible,
        // regardless of environment or URL — shouldRun() only gates monitoring.
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'queryspy');
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        if (!$this->shouldRun()) return;

        $this->startTime = microtime(true);
        QueryCollector::start();

        if (config('queryspy.inject_headers', true)) {
            $this->app['router']->pushMiddlewareToGroup('web', QuerySpyHeaders::class);
        }

        DB::listen(function (QueryExecuted $query) {
            app(QueryListener::class)->handle($query);
        });

        app()->terminating(function () {
            $this->handleTermination();
        });
    }

    protected function shouldRun(): bool
    {
        if (!config('queryspy.enabled', true)) return false;
        if (!in_array(app()->environment(), config('queryspy.environments', ['local', 'development', 'staging']), true)) return false;
        if (app()->runningInConsole()) return false;

        $path = request()->getPathInfo();
        foreach (config('queryspy.ignore_urls', []) as $pattern) {
            $regex = str_replace(['\*', '\?'], ['.*', '.'], preg_quote($pattern, '/'));
            if (preg_match('/^' . $regex . '$/i', $path)) return false;
        }

        return true;
    }

    protected function handleTermination(): void
    {
        $duration = microtime(true) - $this->startTime;
        $report   = QueryAnalyzer::analyze();

        $hasIssue =
            $report['too_many_queries']              ||
            $report['memory_alert']                  ||
            !empty($report['slow_queries'])          ||
            !empty($report['n_plus_one'])            ||
            !empty($report['duplicate_queries'])     ||
            !empty($report['missing_index_hints'])   ||
            !empty($report['select_star'])           ||
            $duration > config('queryspy.slow_request_seconds', 2);

        if (!$hasIssue) return;

        $entry = [
            'id'         => uniqid('qs_', true),
            'timestamp'  => now()->toIso8601String(),
            'url'        => request()->fullUrl(),
            'method'     => request()->method(),
            'route'      => optional(request()->route())->getName(),
            'duration_s' => round($duration, 4),
            'report'     => $report,
        ];

        app(LogStore::class)->push($entry);

        logger()->channel(config('queryspy.log_channel', 'stack'))->warning('QuerySpy Alert', [
            'url'      => $entry['url'],
            'method'   => $entry['method'],
            'duration' => $entry['duration_s'] . 's',
            'severity' => $report['severity_level'],
        ]);
    }

    protected function registerPublishables(): void
    {
        if (!$this->app->runningInConsole()) return;

        $this->publishes([
            __DIR__ . '/../config/queryspy.php' => config_path('queryspy.php'),
        ], 'queryspy-config');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views/vendor/queryspy'),
        ], 'queryspy-views');
    }
}
