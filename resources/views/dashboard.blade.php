<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>QuerySpy — Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;500;700&family=Geist:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:#0a0b0d;--bg2:#111318;--bg3:#191c24;
  --border:#1f2330;--border2:#2a2f42;
  --text:#e2e6f0;--muted:#6b7280;
  --accent:#6366f1;--accent2:#818cf8;
  --ok:#10b981;--notice:#3b82f6;--warn:#f59e0b;--critical:#ef4444;
  --mono:'JetBrains Mono',monospace;--sans:'Geist',system-ui,sans-serif;
}
html{font-size:14px}
body{background:var(--bg);color:var(--text);font-family:var(--sans);min-height:100vh;line-height:1.6}

/* Layout */
.layout{display:grid;grid-template-columns:220px 1fr;min-height:100vh}

/* Sidebar */
.sidebar{background:var(--bg2);border-right:1px solid var(--border);display:flex;flex-direction:column;position:sticky;top:0;height:100vh;overflow-y:auto}
.logo{padding:18px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px}
.logo-icon{width:32px;height:32px;background:linear-gradient(135deg,var(--accent),#a855f7);border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0}
.logo-name{font-weight:700;font-size:1rem;letter-spacing:-0.5px}
.logo-name span{color:var(--accent2)}
.logo-sub{font-size:0.6rem;color:var(--muted)}
.nav{padding:10px 8px;flex:1}
.nav-label{font-size:0.62rem;font-weight:600;color:var(--muted);letter-spacing:1px;text-transform:uppercase;padding:8px 12px 4px}
.nav-item{display:flex;align-items:center;gap:8px;padding:7px 12px;border-radius:6px;color:var(--muted);font-size:0.82rem;cursor:pointer;transition:all .15s;text-decoration:none;margin-bottom:2px;border:none;background:none;width:100%;text-align:left}
.nav-item:hover{background:var(--bg3);color:var(--text)}
.nav-item.active{background:rgba(99,102,241,.12);color:var(--accent2)}
.nav-badge{margin-left:auto;font-size:0.68rem;padding:1px 6px;border-radius:10px;font-family:var(--mono);background:var(--bg3);color:var(--muted)}
.nav-item.active .nav-badge{background:rgba(99,102,241,.2);color:var(--accent2)}
.sidebar-footer{padding:14px;border-top:1px solid var(--border)}
.qs-stat-row{display:flex;justify-content:space-between;font-size:0.72rem;color:var(--muted);margin-bottom:5px}
.qs-stat-row span:last-child{color:var(--text);font-family:var(--mono)}
.qs-score-bar{height:3px;border-radius:3px;background:var(--bg3);overflow:hidden;margin-bottom:12px}
.qs-score-fill{height:100%;transition:width .5s}
.clear-btn{width:100%;background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);color:var(--critical);padding:8px;border-radius:6px;font-size:0.8rem;cursor:pointer;font-family:var(--sans);transition:all .15s}
.clear-btn:hover{background:rgba(239,68,68,.15)}

/* Main */
.main{overflow:hidden}
.topbar{background:var(--bg2);border-bottom:1px solid var(--border);padding:14px 22px;display:flex;align-items:center;justify-content:space-between;gap:12px;position:sticky;top:0;z-index:10}
.topbar-title{font-size:0.95rem;font-weight:600}
.topbar-sub{font-size:0.72rem;color:var(--muted)}
.topbar-right{display:flex;gap:8px;align-items:center}
.search{background:var(--bg2);border:1px solid var(--border2);border-radius:7px;padding:7px 12px;color:var(--text);font-family:var(--sans);font-size:0.82rem;width:220px;outline:none;transition:border-color .15s}
.search:focus{border-color:var(--accent)}
.btn{padding:6px 13px;border-radius:6px;font-size:0.78rem;cursor:pointer;font-family:var(--sans);border:1px solid var(--border2);background:var(--bg3);color:var(--text);transition:all .15s;text-decoration:none;display:inline-flex;align-items:center;gap:5px}
.btn:hover{border-color:var(--accent);color:var(--accent2)}

/* Content */
.content{padding:20px 22px}
.flash{background:rgba(16,185,129,.1);border:1px solid rgba(16,185,129,.25);color:var(--ok);padding:10px 14px;border-radius:8px;margin-bottom:16px;font-size:0.82rem}

/* Stat cards */
.stat-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(140px,1fr));gap:10px;margin-bottom:20px}
.stat-card{background:var(--bg2);border:1px solid var(--border);border-radius:9px;padding:14px;position:relative;overflow:hidden}
.stat-card::before{content:'';position:absolute;top:0;left:0;right:0;height:2px}
.stat-card.c-ok::before{background:var(--ok)}
.stat-card.c-notice::before{background:var(--notice)}
.stat-card.c-warn::before{background:var(--warn)}
.stat-card.c-critical::before{background:var(--critical)}
.stat-card.c-neutral::before{background:var(--accent)}
.stat-label{font-size:0.67rem;color:var(--muted);text-transform:uppercase;letter-spacing:.8px;font-weight:600}
.stat-value{font-size:1.7rem;font-weight:700;font-family:var(--mono);line-height:1;margin:3px 0 2px}
.stat-sub{font-size:0.67rem;color:var(--muted)}
.stat-card.c-ok .stat-value{color:var(--ok)}
.stat-card.c-notice .stat-value{color:var(--notice)}
.stat-card.c-warn .stat-value{color:var(--warn)}
.stat-card.c-critical .stat-value{color:var(--critical)}
.stat-card.c-neutral .stat-value{color:var(--accent2)}

/* Chart section */
.section{margin-bottom:20px}
.section-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px}
.section-title{font-size:0.72rem;font-weight:600;color:var(--muted);text-transform:uppercase;letter-spacing:.8px}
.chart-wrap{background:var(--bg2);border:1px solid var(--border);border-radius:9px;padding:14px}

/* Filter bar */
.filter-bar{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:14px}
.chip{padding:4px 12px;border-radius:20px;font-size:0.73rem;cursor:pointer;border:1px solid var(--border2);background:transparent;color:var(--muted);font-family:var(--sans);transition:all .15s}
.chip:hover{border-color:var(--accent);color:var(--accent2)}
.chip.active{background:rgba(99,102,241,.14);border-color:var(--accent);color:var(--accent2)}
.chip.f-critical.active{background:rgba(239,68,68,.1);border-color:var(--critical);color:var(--critical)}
.chip.f-warning.active{background:rgba(245,158,11,.1);border-color:var(--warn);color:var(--warn)}
.chip.f-notice.active{background:rgba(59,130,246,.1);border-color:var(--notice);color:var(--notice)}

/* Entry card */
.entry-card{background:var(--bg2);border:1px solid var(--border);border-radius:9px;margin-bottom:7px;overflow:hidden;transition:border-color .2s}
.entry-card:hover{border-color:var(--border2)}
.entry-header{padding:11px 14px;display:flex;align-items:center;gap:10px;cursor:pointer;user-select:none}
.sev-dot{width:7px;height:7px;border-radius:50%;flex-shrink:0}
.sev-dot.ok{background:var(--ok)}
.sev-dot.notice{background:var(--notice)}
.sev-dot.warning{background:var(--warn);box-shadow:0 0 5px rgba(245,158,11,.5)}
.sev-dot.critical{background:var(--critical);box-shadow:0 0 5px rgba(239,68,68,.6);animation:pulse 2s infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}
.method-badge{font-size:0.62rem;font-weight:700;padding:2px 6px;border-radius:4px;font-family:var(--mono);flex-shrink:0}
.GET{background:rgba(16,185,129,.1);color:var(--ok)}
.POST{background:rgba(99,102,241,.1);color:var(--accent2)}
.PUT,.PATCH{background:rgba(245,158,11,.1);color:var(--warn)}
.DELETE{background:rgba(239,68,68,.1);color:var(--critical)}
.entry-url{font-family:var(--mono);font-size:0.78rem;flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.entry-tags{display:flex;gap:4px;flex-shrink:0}
.tag{font-size:0.62rem;padding:1px 6px;border-radius:4px;font-weight:600}
.tag-n1{background:rgba(239,68,68,.1);color:var(--critical);border:1px solid rgba(239,68,68,.2)}
.tag-slow{background:rgba(245,158,11,.1);color:var(--warn);border:1px solid rgba(245,158,11,.2)}
.tag-dupe{background:rgba(59,130,246,.1);color:var(--notice);border:1px solid rgba(59,130,246,.2)}
.tag-many{background:rgba(168,85,247,.1);color:#c084fc;border:1px solid rgba(168,85,247,.2)}
.tag-mem{background:rgba(16,185,129,.1);color:var(--ok);border:1px solid rgba(16,185,129,.2)}
.tag-star{background:rgba(99,102,241,.1);color:var(--accent2);border:1px solid rgba(99,102,241,.2)}
.entry-meta{display:flex;gap:10px;flex-shrink:0;font-size:0.72rem;color:var(--muted);font-family:var(--mono)}
.meta-val{color:var(--text)}
.meta-val.s-warn{color:var(--warn)}
.meta-val.s-crit{color:var(--critical)}
.chevron{margin-left:6px;color:var(--muted);font-size:0.65rem;transition:transform .2s;flex-shrink:0}
.entry-card.open .chevron{transform:rotate(180deg)}

/* Detail */
.entry-detail{display:none;border-top:1px solid var(--border)}
.entry-card.open .entry-detail{display:block}
.tabs{display:flex;border-bottom:1px solid var(--border);padding:0 14px}
.tab-btn{padding:9px 12px;font-size:0.76rem;color:var(--muted);cursor:pointer;background:none;border:none;border-bottom:2px solid transparent;font-family:var(--sans);margin-bottom:-1px;transition:all .15s}
.tab-btn:hover{color:var(--text)}
.tab-btn.active{color:var(--accent2);border-bottom-color:var(--accent)}
.tab-panel{display:none;padding:14px}
.tab-panel.active{display:block}
.detail-cols{display:grid;grid-template-columns:1fr 1fr;gap:0}
.detail-col{padding:14px}
.detail-col:first-child{border-right:1px solid var(--border)}
.col-title{font-size:0.63rem;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--muted);margin-bottom:10px;display:flex;align-items:center;gap:6px}
.col-title::after{content:'';flex:1;height:1px;background:var(--border)}
.kv{display:flex;justify-content:space-between;align-items:baseline;margin-bottom:5px;font-size:0.78rem}
.kv-k{color:var(--muted)}
.kv-v{font-family:var(--mono)}
.kv-v.warn{color:var(--warn)}
.kv-v.crit{color:var(--critical)}
.kv-v.ok{color:var(--ok)}

/* Issue blocks */
.issue-list{margin-top:6px}
.issue-block{background:var(--bg3);border:1px solid var(--border);border-radius:6px;padding:10px 12px;margin-bottom:6px}
.issue-head{display:flex;align-items:center;gap:7px;font-size:0.74rem;font-weight:600;margin-bottom:5px}
.issue-body{font-size:0.73rem;color:var(--muted)}
.sql-wrap{position:relative;margin-top:5px}
.sql-block{background:var(--bg);border:1px solid var(--border);border-radius:5px;padding:8px 10px;font-family:var(--mono);font-size:0.72rem;color:#a5b4fc;overflow-x:auto;white-space:pre-wrap;word-break:break-all;padding-right:56px}
.copy-btn{position:absolute;top:5px;right:6px;background:var(--bg2);border:1px solid var(--border2);color:var(--muted);font-size:0.62rem;padding:2px 7px;border-radius:4px;cursor:pointer;font-family:var(--mono);transition:all .15s}
.copy-btn:hover{color:var(--text)}
.file-ref{font-family:var(--mono);font-size:0.68rem;color:var(--notice);background:rgba(59,130,246,.08);padding:2px 6px;border-radius:4px;display:inline-block;margin-top:5px}

/* Queries table */
.q-table{width:100%;border-collapse:collapse;font-size:0.76rem}
.q-table th{text-align:left;padding:7px 9px;font-size:0.63rem;font-weight:600;text-transform:uppercase;letter-spacing:.8px;color:var(--muted);border-bottom:1px solid var(--border)}
.q-table td{padding:7px 9px;border-bottom:1px solid var(--border);vertical-align:top}
.q-table tr:last-child td{border-bottom:none}
.q-table tr:hover td{background:rgba(255,255,255,.015)}
.time-badge{font-family:var(--mono);font-size:0.68rem;padding:2px 6px;border-radius:4px;font-weight:600;white-space:nowrap}
.t-ok{background:rgba(16,185,129,.1);color:var(--ok)}
.t-warn{background:rgba(245,158,11,.1);color:var(--warn)}
.t-crit{background:rgba(239,68,68,.1);color:var(--critical)}

/* Pagination */
#pagination{display:flex;gap:5px;align-items:center;justify-content:center;margin-top:14px;flex-wrap:wrap}
.page-btn{padding:4px 10px;border-radius:5px;font-size:0.72rem;cursor:pointer;background:var(--bg3);color:var(--text);border:1px solid var(--border2);font-family:var(--mono);transition:all .15s}
.page-btn:hover:not(:disabled){border-color:var(--accent);color:var(--accent2)}
.page-btn.active{background:var(--accent);border-color:var(--accent);color:#fff}
.page-btn:disabled{opacity:.4;cursor:default}
.page-dots{color:var(--muted);padding:0 3px;font-size:0.72rem}

/* Empty */
.empty{text-align:center;padding:56px 20px;color:var(--muted)}
.empty-icon{font-size:2.8rem;margin-bottom:10px}
.empty-title{font-size:1rem;font-weight:600;color:var(--text);margin-bottom:5px}
.empty-sub{font-size:0.82rem}

/* Auto-refresh label */
.refresh-label{display:flex;align-items:center;gap:6px;font-size:0.72rem;color:var(--muted);cursor:pointer}
.refresh-label input{accent-color:var(--accent)}

::-webkit-scrollbar{width:4px;height:4px}
::-webkit-scrollbar-thumb{background:var(--border2);border-radius:4px}

@media(max-width:700px){
  .layout{grid-template-columns:1fr}
  .sidebar{position:static;height:auto}
  .detail-cols{grid-template-columns:1fr}
  .entry-meta{display:none}
}
</style>
</head>
<body>
<div class="layout">

{{-- ── Sidebar ── --}}
<aside class="sidebar">
  <div class="logo">
    <div class="logo-icon">🔍</div>
    <div>
      <div class="logo-name">Query<span>Spy</span></div>
      <div class="logo-sub">Performance Monitor</div>
    </div>
  </div>

  <nav class="nav">
    <div class="nav-label">Overview</div>
    <button class="nav-item active" onclick="setFilter('all',this)">
      📋 All Requests <span class="nav-badge">{{ $stats['total'] }}</span>
    </button>
    <button class="nav-item" onclick="setFilter('critical',this)">
      🚨 Critical <span class="nav-badge" style="color:var(--critical)">{{ $stats['critical'] }}</span>
    </button>
    <button class="nav-item" onclick="setFilter('warning',this)">
      ⚠️ Warnings <span class="nav-badge" style="color:var(--warn)">{{ $stats['warning'] }}</span>
    </button>
    <button class="nav-item" onclick="setFilter('notice',this)">
      ℹ️ Notices <span class="nav-badge" style="color:var(--notice)">{{ $stats['notice'] }}</span>
    </button>
    <div class="nav-label" style="margin-top:10px">Issue Types</div>
    <button class="nav-item" onclick="setFilter('nplusone',this)">🔁 N+1 Queries</button>
    <button class="nav-item" onclick="setFilter('slow',this)">🐢 Slow Queries</button>
    <button class="nav-item" onclick="setFilter('duplicates',this)">📄 Duplicates</button>
    <button class="nav-item" onclick="setFilter('memory',this)">🧠 Memory</button>
  </nav>

  <div class="sidebar-footer">
    @if($stats['total'] > 0)
    <div style="margin-bottom:10px">
      <div class="qs-stat-row"><span>Avg queries</span><span>{{ $stats['avg_queries'] }}</span></div>
      <div class="qs-stat-row"><span>Avg duration</span><span>{{ $stats['avg_duration'] }}s</span></div>
      <div class="qs-stat-row"><span>Avg memory</span><span>{{ $stats['avg_memory_mb'] }} MB</span></div>
      <div class="qs-stat-row" style="margin-bottom:7px"><span>Avg score</span><span>{{ $stats['avg_score'] }}/100</span></div>
      <div class="qs-score-bar">
        <div class="qs-score-fill" style="width:{{ $stats['avg_score'] }}%;background:{{ $stats['avg_score'] >= 70 ? 'var(--critical)' : ($stats['avg_score'] >= 40 ? 'var(--warn)' : 'var(--ok)') }}"></div>
      </div>
    </div>
    @endif
    <form method="POST" action="{{ route('queryspy.clear') }}" onsubmit="return confirm('Clear all QuerySpy logs?')">
      @csrf
      @if(config('queryspy.password'))
        <input type="hidden" name="password" value="{{ request('password') }}">
      @endif
      <button type="submit" class="clear-btn">🗑 Clear All Logs</button>
    </form>
  </div>
</aside>

{{-- ── Main ── --}}
<main class="main">
  <div class="topbar">
    <div>
      <div class="topbar-title" id="view-title">All Requests</div>
      <div class="topbar-sub">Real-time query performance monitoring</div>
    </div>
    <div class="topbar-right">
      <input class="search" type="text" id="search" placeholder="🔍  Search URL, SQL…" oninput="applyFilters()">
      <a class="btn" href="{{ route('queryspy.export.csv') }}{{ config('queryspy.password') ? '?password='.request('password') : '' }}">⬇ CSV</a>
      <a class="btn" href="{{ route('queryspy.api') }}{{ config('queryspy.password') ? '?password='.request('password') : '' }}" target="_blank">⬇ JSON</a>
      <button class="btn" onclick="location.reload()">↻</button>
    </div>
  </div>

  <div class="content">

    @if(session('message'))
      <div class="flash">✓ {{ session('message') }}</div>
    @endif

    {{-- Stat cards --}}
    <div class="stat-grid">
      <div class="stat-card c-neutral">
        <div class="stat-label">Total</div>
        <div class="stat-value">{{ $stats['total'] }}</div>
        <div class="stat-sub">requests tracked</div>
      </div>
      <div class="stat-card {{ $stats['critical'] > 0 ? 'c-critical' : 'c-ok' }}">
        <div class="stat-label">Critical</div>
        <div class="stat-value">{{ $stats['critical'] }}</div>
        <div class="stat-sub">need attention</div>
      </div>
      <div class="stat-card {{ $stats['warning'] > 0 ? 'c-warn' : 'c-ok' }}">
        <div class="stat-label">Warnings</div>
        <div class="stat-value">{{ $stats['warning'] }}</div>
        <div class="stat-sub">performance issues</div>
      </div>
      <div class="stat-card {{ $stats['notice'] > 0 ? 'c-notice' : 'c-ok' }}">
        <div class="stat-label">Notices</div>
        <div class="stat-value">{{ $stats['notice'] }}</div>
        <div class="stat-sub">worth reviewing</div>
      </div>
      <div class="stat-card c-ok">
        <div class="stat-label">Clean</div>
        <div class="stat-value">{{ $stats['ok'] }}</div>
        <div class="stat-sub">no issues found</div>
      </div>
      <div class="stat-card {{ ($stats['avg_duration'] ?? 0) > config('queryspy.slow_request_seconds', 2) ? 'c-warn' : 'c-neutral' }}">
        <div class="stat-label">Avg Duration</div>
        <div class="stat-value" style="font-size:1.3rem">{{ $stats['avg_duration'] ?? 0 }}s</div>
        <div class="stat-sub">per request</div>
      </div>
      <div class="stat-card {{ ($stats['total_n_plus_one'] ?? 0) > 0 ? 'c-critical' : 'c-ok' }}">
        <div class="stat-label">Total N+1</div>
        <div class="stat-value">{{ $stats['total_n_plus_one'] ?? 0 }}</div>
        <div class="stat-sub">across all requests</div>
      </div>
    </div>

    @if(empty($entries))
      <div class="empty">
        <div class="empty-icon">🎉</div>
        <div class="empty-title">Nothing tracked yet</div>
        <div class="empty-sub">Browse your app — QuerySpy logs requests with issues here.</div>
      </div>
    @else

      {{-- Timeline chart --}}
      <div class="section">
        <div class="section-header">
          <span class="section-title">Request Timeline</span>
          <label class="refresh-label">
            <input type="checkbox" id="auto-refresh" onchange="toggleAutoRefresh(this.checked)">
            Auto-refresh (10s)
          </label>
        </div>
        <div class="chart-wrap">
          <canvas id="timeline-chart" height="75"></canvas>
        </div>
      </div>

      {{-- Filters --}}
      <div class="filter-bar">
        <button class="chip active"        data-f="all"        onclick="setFilter('all',this)">All</button>
        <button class="chip f-critical"    data-f="critical"   onclick="setFilter('critical',this)">🚨 Critical</button>
        <button class="chip f-warning"     data-f="warning"    onclick="setFilter('warning',this)">⚠️ Warning</button>
        <button class="chip f-notice"      data-f="notice"     onclick="setFilter('notice',this)">ℹ️ Notice</button>
        <button class="chip"               data-f="nplusone"   onclick="setFilter('nplusone',this)">🔁 N+1</button>
        <button class="chip"               data-f="slow"       onclick="setFilter('slow',this)">🐢 Slow</button>
        <button class="chip"               data-f="duplicates" onclick="setFilter('duplicates',this)">📄 Dupes</button>
        <button class="chip"               data-f="memory"     onclick="setFilter('memory',this)">🧠 Memory</button>
      </div>

      {{-- Entry list --}}
      <div class="section">
        <div class="section-header">
          <span class="section-title">Request Log</span>
          <span id="entry-count" style="font-size:0.72rem;color:var(--muted);font-family:var(--mono)">{{ count($entries) }} entries</span>
        </div>

        <div id="entry-list">
        @foreach($entries as $entry)
          @php
            $r        = $entry['report'];
            $sev      = $r['severity_level'] ?? 'ok';
            $dur      = $entry['duration_s'];
            $nC       = count($r['n_plus_one']        ?? []);
            $slC      = count($r['slow_queries']       ?? []);
            $duC      = count($r['duplicate_queries']  ?? []);
            $memA     = $r['memory_alert']      ?? false;
            $tooMany  = $r['too_many_queries']   ?? false;
            $starC    = count($r['select_star']  ?? []);
            $ts       = \Carbon\Carbon::parse($entry['timestamp'])->diffForHumans();
            $issues   = implode(' ', array_filter([
              $nC      ? 'nplusone'  : null,
              $slC     ? 'slow'      : null,
              $duC     ? 'duplicates': null,
              $memA    ? 'memory'    : null,
              $tooMany ? 'many'      : null,
            ]));
          @endphp
          <div class="entry-card"
               data-sev="{{ $sev }}"
               data-issues="{{ $issues }}"
               data-url="{{ strtolower($entry['url'] ?? '') }}"
               data-route="{{ strtolower($entry['route'] ?? '') }}">

            <div class="entry-header" onclick="this.parentElement.classList.toggle('open')">
              <div class="sev-dot {{ $sev }}"></div>
              <span class="method-badge {{ $entry['method'] }}">{{ $entry['method'] }}</span>
              <span class="entry-url" title="{{ $entry['url'] }}">
                {{ $entry['route'] ? '[' . $entry['route'] . '] ' : '' }}{{ parse_url($entry['url'], PHP_URL_PATH) }}
              </span>
              <div class="entry-tags">
                @if($nC)    <span class="tag tag-n1">N+1 ×{{ $nC }}</span> @endif
                @if($slC)   <span class="tag tag-slow">Slow ×{{ $slC }}</span> @endif
                @if($duC)   <span class="tag tag-dupe">Dupe ×{{ $duC }}</span> @endif
                @if($tooMany)<span class="tag tag-many">{{ $r['total_queries'] }}q</span> @endif
                @if($memA)  <span class="tag tag-mem">Mem</span> @endif
                @if($starC) <span class="tag tag-star">SELECT*</span> @endif
              </div>
              <div class="entry-meta">
                <span class="kv-v {{ $dur > 5 ? 's-crit' : ($dur > 2 ? 's-warn' : '') }}">{{ $dur }}s</span>
                <span class="kv-v {{ $tooMany ? 's-warn' : '' }}">{{ $r['total_queries'] }}q</span>
                <span style="color:var(--muted)">{{ $ts }}</span>
              </div>
              <span class="chevron">▼</span>
            </div>

            <div class="entry-detail">
              {{-- Tabs --}}
              <div class="tabs">
                <button class="tab-btn active" onclick="openTab(this,'ts-sum-{{ $loop->index }}')">Summary</button>
                <button class="tab-btn"        onclick="openTab(this,'ts-qry-{{ $loop->index }}')">Queries ({{ $r['total_queries'] }})</button>
                @if($nC)  <button class="tab-btn" onclick="openTab(this,'ts-n1-{{ $loop->index }}')">N+1 ({{ $nC }})</button> @endif
                @if($slC) <button class="tab-btn" onclick="openTab(this,'ts-sl-{{ $loop->index }}')">Slow ({{ $slC }})</button> @endif
                @if($duC) <button class="tab-btn" onclick="openTab(this,'ts-du-{{ $loop->index }}')">Dupes ({{ $duC }})</button> @endif
              </div>

              {{-- Summary tab --}}
              <div id="ts-sum-{{ $loop->index }}" class="tab-panel active">
                <div class="detail-cols">
                  <div class="detail-col">
                    <div class="col-title">Request</div>
                    <div class="kv"><span class="kv-k">Method</span>    <span class="kv-v">{{ $entry['method'] }}</span></div>
                    <div class="kv"><span class="kv-k">Route</span>      <span class="kv-v">{{ $entry['route'] ?? '—' }}</span></div>
                    <div class="kv"><span class="kv-k">Duration</span>   <span class="kv-v {{ $dur>5?'crit':($dur>2?'warn':'') }}">{{ $dur }}s</span></div>
                    <div class="kv"><span class="kv-k">Severity</span>   <span class="kv-v {{ $sev==='ok'?'ok':($sev==='critical'?'crit':'warn') }}">{{ strtoupper($sev) }} ({{ $r['severity_score'] }}/100)</span></div>
                    <div class="kv"><span class="kv-k">When</span>       <span class="kv-v">{{ $entry['timestamp'] }}</span></div>
                    <div class="col-title" style="margin-top:12px">Queries</div>
                    <div class="kv"><span class="kv-k">Total</span>      <span class="kv-v {{ $tooMany?'warn':'' }}">{{ $r['total_queries'] }}</span></div>
                    <div class="kv"><span class="kv-k">DB Time</span>    <span class="kv-v">{{ $r['total_time_ms'] }}ms</span></div>
                    <div class="kv"><span class="kv-k">N+1</span>        <span class="kv-v {{ $nC?'crit':'' }}">{{ $nC ? "Yes ({$nC})" : 'No' }}</span></div>
                    <div class="kv"><span class="kv-k">Slow</span>       <span class="kv-v {{ $slC?'warn':'' }}">{{ $slC }}</span></div>
                    <div class="kv"><span class="kv-k">Duplicates</span> <span class="kv-v {{ $duC?'warn':'' }}">{{ $duC }}</span></div>
                    <div class="kv"><span class="kv-k">SELECT *</span>   <span class="kv-v {{ $starC?'warn':'' }}">{{ $starC }}</span></div>
                  </div>
                  <div class="detail-col">
                    <div class="col-title">Memory</div>
                    <div class="kv"><span class="kv-k">Peak</span>       <span class="kv-v {{ $memA?'crit':'' }}">{{ $r['peak_memory_mb'] }} MB</span></div>
                    <div class="kv"><span class="kv-k">Growth</span>     <span class="kv-v">{{ $r['memory_growth_mb'] }} MB</span></div>
                    <div class="kv"><span class="kv-k">Alert</span>      <span class="kv-v {{ $memA?'crit':'ok' }}">{{ $memA ? 'Yes' : 'No' }}</span></div>

                    @if(!empty($r['missing_index_hints']))
                    <div class="col-title" style="margin-top:12px">Index Hints</div>
                    <div class="issue-list">
                      @foreach($r['missing_index_hints'] as $h)
                      <div class="issue-block">
                        <div class="issue-body">{{ $h['hint'] }}</div>
                        <div class="sql-wrap"><div class="sql-block">{{ $h['sql'] }}</div><button class="copy-btn" onclick="copySql(this)">Copy</button></div>
                        @if($h['file'])<div><span class="file-ref">{{ basename($h['file']) }}:{{ $h['line'] }}</span></div>@endif
                      </div>
                      @endforeach
                    </div>
                    @endif

                    @if(!empty($r['select_star']))
                    <div class="col-title" style="margin-top:12px">SELECT *</div>
                    <div class="issue-list">
                      @foreach(array_slice($r['select_star'],0,4) as $sq)
                      <div class="issue-block">
                        <div class="issue-body">{{ $sq['hint'] }}</div>
                        <div class="sql-wrap"><div class="sql-block">{{ Str::limit($sq['sql'],180) }}</div><button class="copy-btn" onclick="copySql(this)">Copy</button></div>
                        @if($sq['file'])<div><span class="file-ref">{{ basename($sq['file']) }}:{{ $sq['line'] }}</span></div>@endif
                      </div>
                      @endforeach
                    </div>
                    @endif
                  </div>
                </div>
              </div>

              {{-- All queries tab --}}
              <div id="ts-qry-{{ $loop->index }}" class="tab-panel">
                <table class="q-table">
                  <thead><tr><th>#</th><th>Time</th><th>SQL</th><th>Source</th></tr></thead>
                  <tbody>
                  @foreach(($r['queries'] ?? []) as $qi => $q)
                    @php $qt=$q['time']; $tc=$qt>config('queryspy.slow_query_ms',100)?'t-crit':($qt>50?'t-warn':'t-ok'); @endphp
                    <tr>
                      <td style="color:var(--muted);font-family:var(--mono)">{{ $qi+1 }}</td>
                      <td><span class="time-badge {{ $tc }}">{{ round($qt,2) }}ms</span></td>
                      <td>
                        <div class="sql-wrap">
                          <div class="sql-block" style="max-height:54px;overflow:hidden">{{ Str::limit($q['full_sql'] ?? $q['sql'],280) }}</div>
                          <button class="copy-btn" onclick="copySql(this)">Copy</button>
                        </div>
                      </td>
                      <td>
                        @if($q['file'])<span class="file-ref">{{ basename($q['file']) }}:{{ $q['line'] }}</span>@else<span style="color:var(--muted)">—</span>@endif
                      </td>
                    </tr>
                  @endforeach
                  </tbody>
                </table>
              </div>

              {{-- N+1 tab --}}
              @if($nC)
              <div id="ts-n1-{{ $loop->index }}" class="tab-panel">
                <div class="issue-list">
                  @foreach($r['n_plus_one'] as $np)
                  <div class="issue-block">
                    <div class="issue-head">
                      <span style="color:var(--critical)">🔁</span>
                      Ran <strong style="color:var(--critical)">{{ $np['count'] }}×</strong>
                      — total <strong style="color:var(--warn)">{{ $np['total_time'] }}ms</strong>
                    </div>
                    <div class="sql-wrap"><div class="sql-block">{{ $np['pattern'] }}</div><button class="copy-btn" onclick="copySql(this)">Copy</button></div>
                    <div class="issue-body" style="margin-top:6px;color:var(--warn)">💡 {{ $np['suggestion'] }}</div>
                    @if($np['file'])<div><span class="file-ref">{{ basename($np['file']) }}:{{ $np['line'] }}</span></div>@endif
                  </div>
                  @endforeach
                </div>
              </div>
              @endif

              {{-- Slow tab --}}
              @if($slC)
              <div id="ts-sl-{{ $loop->index }}" class="tab-panel">
                <div class="issue-list">
                  @foreach($r['slow_queries'] as $sq)
                  <div class="issue-block">
                    <div class="issue-head">
                      <span style="color:var(--warn)">🐢</span>
                      <span class="time-badge t-crit">{{ round($sq['time'],2) }}ms</span>
                      @if($sq['caller']??false)<span style="font-family:var(--mono);font-size:0.68rem;color:var(--muted)">{{ $sq['caller'] }}</span>@endif
                    </div>
                    <div class="sql-wrap"><div class="sql-block">{{ $sq['full_sql'] ?? $sq['sql'] }}</div><button class="copy-btn" onclick="copySql(this)">Copy</button></div>
                    @if($sq['file'])<div><span class="file-ref">{{ basename($sq['file']) }}:{{ $sq['line'] }}</span></div>@endif
                  </div>
                  @endforeach
                </div>
              </div>
              @endif

              {{-- Dupes tab --}}
              @if($duC)
              <div id="ts-du-{{ $loop->index }}" class="tab-panel">
                <div class="issue-list">
                  @foreach($r['duplicate_queries'] as $d)
                  <div class="issue-block">
                    <div class="issue-head">
                      <span style="color:var(--notice)">📄</span>
                      Run <strong style="color:var(--notice)">{{ $d['count'] }}×</strong> identically — consider caching.
                    </div>
                    <div class="sql-wrap"><div class="sql-block">{{ $d['sql'] }}</div><button class="copy-btn" onclick="copySql(this)">Copy</button></div>
                    @if($d['file'])<div><span class="file-ref">{{ basename($d['file']) }}:{{ $d['line'] }}</span></div>@endif
                  </div>
                  @endforeach
                </div>
              </div>
              @endif

            </div>{{-- /entry-detail --}}
          </div>{{-- /entry-card --}}
        @endforeach
        </div>{{-- /entry-list --}}

        <div id="no-results" style="display:none" class="empty">
          <div class="empty-icon">🔍</div>
          <div class="empty-title">No matching entries</div>
          <div class="empty-sub">Try a different filter or search term.</div>
        </div>

        <div id="pagination"></div>

      </div>{{-- /section --}}
    @endif

  </div>{{-- /content --}}
</main>
</div>{{-- /layout --}}

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
// ── Entries data for chart ──────────────────────────────────────────────────
const QS_ENTRIES = {!! json_encode(array_map(fn($e) => [
  'timestamp'  => $e['timestamp'],
  'duration_s' => $e['duration_s'],
  'sev'        => $e['report']['severity_level'] ?? 'ok',
  'queries'    => $e['report']['total_queries'],
  'time_ms'    => $e['report']['total_time_ms'],
], $entries), JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP) !!};

// ── State ───────────────────────────────────────────────────────────────────
let currentFilter = 'all';
let currentPage   = 1;
const PAGE_SIZE   = 25;
let refreshTimer  = null;

// ── Tabs ────────────────────────────────────────────────────────────────────
function openTab(btn, id) {
  const detail = btn.closest('.entry-detail');
  detail.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
  detail.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
  btn.classList.add('active');
  const panel = document.getElementById(id);
  if (panel) panel.classList.add('active');
}

// ── Filters ─────────────────────────────────────────────────────────────────
function setFilter(filter, btn) {
  currentFilter = filter;
  currentPage   = 1;
  document.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
  document.querySelectorAll('.nav-item').forEach(n => n.classList.remove('active'));
  if (btn) btn.classList.add('active');
  const titles = {
    all:'All Requests', critical:'Critical Issues', warning:'Warnings',
    notice:'Notices', ok:'Clean', nplusone:'N+1 Issues',
    slow:'Slow Queries', duplicates:'Duplicates', memory:'Memory Alerts',
  };
  const t = document.getElementById('view-title');
  if (t) t.textContent = titles[filter] || 'All Requests';
  applyFilters();
}

function applyFilters() {
  const search  = (document.getElementById('search')?.value || '').toLowerCase().trim();
  const cards   = Array.from(document.querySelectorAll('.entry-card'));

  const matched = cards.filter(card => {
    const sev    = card.dataset.sev    || '';
    const issues = card.dataset.issues || '';
    const url    = card.dataset.url    || '';
    const route  = card.dataset.route  || '';
    let show = true;
    if      (currentFilter === 'critical')   show = sev === 'critical';
    else if (currentFilter === 'warning')    show = sev === 'warning';
    else if (currentFilter === 'notice')     show = sev === 'notice';
    else if (currentFilter === 'ok')         show = sev === 'ok';
    else if (currentFilter === 'nplusone')   show = issues.includes('nplusone');
    else if (currentFilter === 'slow')       show = issues.includes('slow');
    else if (currentFilter === 'duplicates') show = issues.includes('duplicates');
    else if (currentFilter === 'memory')     show = issues.includes('memory');
    if (show && search) {
      show = (url + ' ' + route + ' ' + card.textContent).toLowerCase().includes(search);
    }
    return show;
  });

  const total   = matched.length;
  const pages   = Math.max(1, Math.ceil(total / PAGE_SIZE));
  currentPage   = Math.min(currentPage, pages);
  const start   = (currentPage - 1) * PAGE_SIZE;
  const visible = new Set(matched.slice(start, start + PAGE_SIZE));

  cards.forEach(c => c.style.display = visible.has(c) ? '' : 'none');

  const countEl = document.getElementById('entry-count');
  if (countEl) countEl.textContent = total
    ? `${start+1}–${Math.min(start+PAGE_SIZE, total)} of ${total}`
    : '0 entries';

  const noRes = document.getElementById('no-results');
  if (noRes) noRes.style.display = total === 0 ? '' : 'none';

  renderPagination(total, pages);
}

// ── Pagination ───────────────────────────────────────────────────────────────
function renderPagination(total, pages) {
  const el = document.getElementById('pagination');
  if (!el) return;
  if (pages <= 1) { el.innerHTML = ''; return; }

  const range = [];
  if (pages <= 7) { for(let i=1;i<=pages;i++) range.push(i); }
  else {
    range.push(1);
    if (currentPage > 3) range.push('…');
    for(let i=Math.max(2,currentPage-1); i<=Math.min(pages-1,currentPage+1); i++) range.push(i);
    if (currentPage < pages-2) range.push('…');
    range.push(pages);
  }

  el.innerHTML = [
    `<button class="page-btn" onclick="goPage(${currentPage-1})" ${currentPage===1?'disabled':''}>‹</button>`,
    ...range.map(p => p==='…'
      ? `<span class="page-dots">…</span>`
      : `<button class="page-btn ${p===currentPage?'active':''}" onclick="goPage(${p})">${p}</button>`
    ),
    `<button class="page-btn" onclick="goPage(${currentPage+1})" ${currentPage===pages?'disabled':''}>›</button>`,
  ].join('');
}

function goPage(p) {
  currentPage = p;
  applyFilters();
  window.scrollTo({top:0, behavior:'smooth'});
}

// ── Copy SQL ─────────────────────────────────────────────────────────────────
function copySql(btn) {
  const sql = btn.previousElementSibling?.textContent?.trim() || '';
  navigator.clipboard.writeText(sql).then(() => {
    const orig = btn.textContent;
    btn.textContent = '✓';
    setTimeout(() => btn.textContent = orig, 1400);
  }).catch(() => {});
}

// ── Auto-refresh ─────────────────────────────────────────────────────────────
function toggleAutoRefresh(on) {
  clearInterval(refreshTimer);
  if (on) refreshTimer = setInterval(() => location.reload(), 10000);
}

// ── Timeline Chart ────────────────────────────────────────────────────────────
(function() {
  const canvas = document.getElementById('timeline-chart');
  if (!canvas || !QS_ENTRIES.length) {
    if (canvas) canvas.closest('.chart-wrap').innerHTML =
      '<p style="color:var(--muted);text-align:center;padding:20px;font-size:.8rem">No data yet — browse your app.</p>';
    return;
  }
  const slice   = QS_ENTRIES.slice(0, 40).reverse();
  const sevColor = { ok:'#10b981', notice:'#3b82f6', warning:'#f59e0b', critical:'#ef4444' };
  const labels   = slice.map(e => e.timestamp.slice(11,19));
  const colors   = slice.map(e => sevColor[e.sev] || '#6366f1');

  new Chart(canvas, {
    type: 'bar',
    data: {
      labels,
      datasets: [
        {
          label: 'Duration (s)',
          data: slice.map(e => e.duration_s),
          backgroundColor: colors.map(c => c + '44'),
          borderColor: colors,
          borderWidth: 1.5,
          borderRadius: 3,
          yAxisID: 'y',
        },
        {
          label: 'Query count',
          data: slice.map(e => e.queries),
          type: 'line',
          borderColor: '#818cf8',
          backgroundColor: 'rgba(99,102,241,.07)',
          borderWidth: 2,
          pointRadius: 3,
          pointBackgroundColor: '#818cf8',
          tension: 0.4,
          fill: true,
          yAxisID: 'y2',
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      interaction: { mode: 'index', intersect: false },
      plugins: {
        legend: { labels: { color:'#6b7280', font:{ size:11, family:'JetBrains Mono' }, boxWidth:12 } },
        tooltip: {
          backgroundColor:'#111318', borderColor:'#2a2f42', borderWidth:1,
          titleColor:'#e2e6f0', bodyColor:'#9ca3af',
          titleFont:{ family:'JetBrains Mono', size:11 },
          bodyFont:{ family:'JetBrains Mono', size:11 },
        },
      },
      scales: {
        x: { ticks:{ color:'#6b7280', font:{ size:10 }, maxRotation:45 }, grid:{ color:'#1f2330' } },
        y: {
          position: 'left',
          title:{ display:true, text:'Time (s)', color:'#6b7280', font:{ size:10 } },
          ticks:{ color:'#6b7280', font:{ size:10 } }, grid:{ color:'#1f2330' },
        },
        y2: {
          position: 'right',
          title:{ display:true, text:'Queries', color:'#818cf8', font:{ size:10 } },
          ticks:{ color:'#818cf8', font:{ size:10 } }, grid:{ drawOnChartArea:false },
        },
      },
    },
  });
})();

// ── Boot ──────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => applyFilters());
</script>
</body>
</html>
