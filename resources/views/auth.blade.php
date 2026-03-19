<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>QuerySpy — Authenticate</title>
<link href="https://fonts.googleapis.com/css2?family=JetBrains+Mono:wght@400;700&family=Geist:wght@400;600&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
body{background:#0a0b0d;color:#e2e6f0;font-family:'Geist',system-ui,sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center}
.card{background:#111318;border:1px solid #1f2330;border-radius:12px;padding:40px;width:100%;max-width:360px;text-align:center}
.icon{font-size:2.5rem;margin-bottom:16px}
h1{font-size:1.2rem;font-weight:600;margin-bottom:6px}
p{font-size:0.8rem;color:#6b7280;margin-bottom:28px}
input{width:100%;background:#0a0b0d;border:1px solid #2a2f42;border-radius:8px;padding:10px 14px;color:#e2e6f0;font-size:0.9rem;font-family:'JetBrains Mono',monospace;outline:none;margin-bottom:12px;transition:border-color .15s}
input:focus{border-color:#6366f1}
button{width:100%;background:#6366f1;border:none;border-radius:8px;padding:10px;color:#fff;font-size:0.9rem;font-weight:600;cursor:pointer;font-family:'Geist',sans-serif;transition:background .15s}
button:hover{background:#818cf8}
</style>
</head>
<body>
<div class="card">
    <div class="icon">🔍</div>
    <h1>QuerySpy Dashboard</h1>
    <p>Enter your password to continue</p>
    <form method="GET">
        <input type="password" name="password" placeholder="Password" autofocus>
        <button type="submit">Unlock</button>
    </form>
</div>
</body>
</html>
