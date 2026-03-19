# 🔍 QuerySpy

**Real-time Laravel query performance monitoring.** Drop it in, browse your app, and instantly see N+1 queries, slow SQL, duplicates, memory spikes, and more — all in a beautiful developer dashboard.

[![Packagist](https://img.shields.io/packagist/v/tuser/query-spy)](https://packagist.org/packages/tuser/query-spy)
[![PHP](https://img.shields.io/badge/PHP-8.1%2B-blue)](https://php.net)
[![Laravel](https://img.shields.io/badge/Laravel-10%7C11-red)](https://laravel.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-green)](LICENSE)

---

## ✨ Features

| | Feature | Description |
|---|---|---|
| 🔁 | **N+1 Detection** | Finds repeated SQL patterns caused by missing eager-loading |
| 🐢 | **Slow Queries** | Flags individual queries over your ms threshold |
| 📄 | **Duplicate SQL** | Catches identical queries run multiple times per request |
| 🧠 | **Memory Tracking** | Peak memory, growth per request, configurable alert threshold |
| ⭐ | **SELECT \* Detection** | Warns when all columns are fetched unnecessarily |
| 🔍 | **Index Hints** | Detects leading-wildcard `LIKE '%...'` that force full table scans |
| 📊 | **Severity Score** | Every request scored 0–100 and labelled OK / Notice / Warning / Critical |
| 🖥 | **Dashboard** | Dark, dev-friendly UI at `/queryspy` |
| 📈 | **Timeline Chart** | Dual-axis Chart.js bar+line showing request time and query count |
| 📋 | **Pagination** | 25 entries per page with smart controls |
| ♻️ | **Auto-refresh** | 10-second polling toggle on the dashboard |
| 📋 | **Copy SQL** | One-click copy button on every SQL block |
| 📤 | **Export** | Download as JSON or CSV directly from the dashboard |
| 🌐 | **Response Headers** | `X-QuerySpy-*` headers visible in your browser's Network tab |
| 🔐 | **Password Protection** | Optional dashboard password via `.env` |
| 🌍 | **Environment Guard** | Skips production, CLI commands, and ignored URL patterns |
| 🖨 | **Artisan Commands** | `summary`, `watch`, `clear`, `export` |

---

## 📦 Installation

```bash
composer require tuser/query-spy
```

> Laravel auto-discovers the service provider — no manual registration needed.

**Publish the config:**

```bash
php artisan vendor:publish --tag=queryspy-config
```

---

## ⚙️ Configuration

Add any of these to your `.env` (all are optional):

```env
QS_ENABLED=true
QS_SLOW_QUERY_MS=100          # Flag queries slower than this (ms)
QS_MAX_QUERIES=50             # Alert when queries/request exceed this
QS_N_PLUS_ONE=5               # Same SQL pattern N times = N+1 warning
QS_SLOW_REQUEST_SECONDS=2     # Flag requests slower than this
QS_MEMORY_THRESHOLD_MB=64     # Alert when peak memory exceeds this (MB)
QS_INJECT_HEADERS=true        # Add X-QuerySpy-* headers to responses
QS_LOG_CHANNEL=stack          # Laravel log channel for alerts
QS_DASHBOARD_URL=/queryspy    # Dashboard URL
QS_PASSWORD=                  # Optional dashboard password
QS_MAX_LOG_ENTRIES=200        # Max entries kept in storage
```

Full reference: `config/queryspy.php`

---

## 🖥 Dashboard

Visit **`/queryspy`** in your browser after installing.

### What you get:
- **7 stat cards** — Total, Critical, Warning, Notice, Clean, Avg Duration, Total N+1
- **Sidebar** — avg queries, avg memory, avg severity score with progress bar
- **Timeline chart** — request duration (bars) + query count (line), coloured by severity
- **Filters** — by severity level or issue type (N+1, slow, dupes, memory)
- **Search** — by URL, route name, or any SQL text
- **Pagination** — 25 per page
- **Auto-refresh** — 10-second toggle
- **Export CSV / JSON** — one-click download
- **Per-request drill-down** with tabs:
  - **Summary** — all metrics, memory, index hints, SELECT* warnings
  - **All Queries** — time badge, full SQL with Copy button, source `file:line`
  - **N+1** — pattern, count, total time, eager-load suggestion, source
  - **Slow** — full SQL, caller, source
  - **Duplicates** — count, source

---

## 🌐 Response Headers

When `QS_INJECT_HEADERS=true`, every response includes:

```
X-QuerySpy-Queries:    14
X-QuerySpy-Time-Ms:    87.4
X-QuerySpy-Memory-MB:  12.5
X-QuerySpy-Severity:   warning
X-QuerySpy-Score:      45
X-QuerySpy-N1:         2
X-QuerySpy-Slow:       1
X-QuerySpy-Duplicates: 0
```

Visible in your browser's **Network tab → select request → Headers**.

---

## 🖨 Artisan Commands

```bash
# Pretty table of recent entries
php artisan queryspy:summary
php artisan queryspy:summary --limit=50

# Live tail — prints new entries as they arrive
php artisan queryspy:watch
php artisan queryspy:watch --interval=5

# Wipe all stored logs
php artisan queryspy:clear

# Export to file
php artisan queryspy:export --format=csv
php artisan queryspy:export --format=json
php artisan queryspy:export --format=csv --output=/tmp/report.csv
```

---

## 📡 JSON API

All endpoints accept `?password=` when `QS_PASSWORD` is set.

```
GET  /queryspy/api               → paginated JSON log
GET  /queryspy/api?severity=critical
GET  /queryspy/api?page=2&per_page=20
GET  /queryspy/export/csv        → CSV download
POST /queryspy/clear             → clear all logs
```

**API response shape:**
```json
{
  "data":  [ ... ],
  "stats": { "total": 42, "critical": 3, "warning": 8, ... },
  "meta":  { "total": 42, "page": 1, "per_page": 50, "last_page": 1 }
}
```

---

## 🌍 Environment Guard

QuerySpy only runs in:

```php
// config/queryspy.php
'environments' => ['local', 'development', 'staging'],
```

It **never runs** in `production` by default, in CLI/Artisan commands, or for URLs matching `ignore_urls` patterns (`/queryspy*`, `/_ignition*`, `/telescope*`, `/horizon*`).

---

## 🔐 Password Protection

```env
QS_PASSWORD=my-secret
```

The dashboard shows a password form. Pass `?password=my-secret` to API endpoints.

---

## 🗂 Issue Types Explained

### 🔁 N+1 Queries
The same SQL pattern (with bindings normalised to `?`) runs 5+ times per request. Classic sign of missing `with('relation')` in Eloquent.

### 🐢 Slow Queries
Any individual query taking longer than `QS_SLOW_QUERY_MS` (default 100ms). Includes full SQL with real binding values and the source `file:line`.

### 📄 Duplicate SQL
Identical SQL (including bindings) run more than once — usually a missing cache or a logic loop.

### 🧠 Memory
Peak PHP memory exceeds `QS_MEMORY_THRESHOLD_MB`. Also tracks memory growth across the request lifecycle.

### ⭐ SELECT *
`SELECT *` on wide tables fetches unnecessary columns, wasting memory and network bandwidth.

### 🔍 Index Hints
`LIKE '%value'` (leading wildcard) can't use a B-tree index and results in a full table scan. Consider a full-text index or a dedicated search engine.

---

## 🛠 Publishing

```bash
# Config
php artisan vendor:publish --tag=queryspy-config

# Blade views (to customise the dashboard)
php artisan vendor:publish --tag=queryspy-views
```

---

## 📝 Changelog

See [CHANGELOG.md](CHANGELOG.md).

---

## 📄 License

MIT © QuerySpy
