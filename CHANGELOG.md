# Changelog

All notable changes to QuerySpy are documented here.
This project follows [Semantic Versioning](https://semver.org/).

---

## [1.0.0] — 2025

### Added
- N+1 query detection with SQL normalisation (bindings replaced with `?`)
- Slow query detection with configurable ms threshold
- Duplicate SQL detection per request
- SELECT * detection with source file:line
- Leading-wildcard LIKE index hint detection
- Memory tracking — peak MB, growth per request, configurable alert
- Per-request severity scoring (0–100) and labelling (ok / notice / warning / critical)
- Structured JSON log store (`storage/queryspy/entries.json`)
- Beautiful dark developer dashboard at `/queryspy`
- Timeline chart (Chart.js) — request duration + query count per request
- Dashboard filter chips — by severity and issue type
- Dashboard full-text search — URL, route, SQL
- Pagination — 25 entries per page
- Auto-refresh toggle (10s polling)
- Copy SQL button on every SQL block
- Export as CSV and JSON from dashboard
- `X-QuerySpy-*` response headers for browser Network tab
- Optional dashboard password protection
- Environment guard — skips production and CLI by default
- Ignored URL patterns (telescope, horizon, livewire, etc.)
- Ignored SQL patterns (sessions, etc.)
- Artisan command: `queryspy:summary` — table of recent entries
- Artisan command: `queryspy:watch` — live tail with real-time output
- Artisan command: `queryspy:clear` — wipe all logs
- Artisan command: `queryspy:export` — CSV or JSON file export
- JSON API with pagination and severity filtering
- Laravel 10 and 11 support
- PHP 8.1+ support
- Auto-discovery via Laravel package discovery
