<?php
session_start();
if (empty($_SESSION['admin'])) {
    header('Location: index.php');
    exit;
}
$adminEmail = $_SESSION['admin'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NEU Library — Admin Dashboard</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
<style>
  :root{--neu-maroon:#1a6b3a;--neu-gold:#D4A017;--neu-dark:#0a1f10;--neu-cream:#f4faf6;--neu-light:#e6f4ec;--text-main:#0a1f10;--text-muted:#4a7a5a;--border:#c8e6d0;--success:#1a6b3a;--danger:#c0392b;--radius:14px;--shadow:0 4px 32px rgba(26,107,58,.10);}
  *{box-sizing:border-box;margin:0;padding:0;}
  body{font-family:'DM Sans',sans-serif;background:var(--neu-cream);color:var(--text-main);min-height:100vh;}
  .topbar{background:var(--neu-maroon);color:#fff;display:flex;align-items:center;padding:0 28px;height:64px;gap:16px;box-shadow:0 2px 16px rgba(0,0,0,.2);position:sticky;top:0;z-index:100;}
  .topbar-logo{font-family:'Playfair Display',serif;font-size:17px;flex:1;}
  .topbar-logo span{color:var(--neu-gold);}
  .topbar-user{font-size:13px;color:rgba(255,255,255,.8);}
  .topbar-btn{background:rgba(255,255,255,.15);border:none;color:#fff;padding:7px 16px;border-radius:8px;cursor:pointer;font-size:13px;font-family:'DM Sans',sans-serif;transition:background .2s;}
  .topbar-btn:hover{background:rgba(255,255,255,.25);}
  .content{flex:1;padding:28px;max-width:1100px;margin:0 auto;width:100%;}
  .tabs{display:flex;gap:6px;margin-bottom:26px;background:var(--neu-light);border-radius:12px;padding:5px;width:fit-content;}
  .tab{padding:9px 22px;border-radius:9px;border:none;background:transparent;font-family:'DM Sans',sans-serif;font-size:14px;font-weight:500;cursor:pointer;color:var(--text-muted);transition:all .2s;}
  .tab.active{background:#fff;color:var(--neu-maroon);box-shadow:0 2px 8px rgba(0,0,0,.08);font-weight:600;}
  .stats-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:16px;margin-bottom:24px;}
  .stat-card{background:#fff;border-radius:var(--radius);padding:24px 20px;box-shadow:var(--shadow);border-left:4px solid var(--neu-maroon);position:relative;overflow:hidden;}
  .stat-card::after{content:attr(data-icon);position:absolute;right:16px;top:16px;font-size:32px;opacity:.15;}
  .stat-card .label{font-size:12px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.6px;}
  .stat-card .value{font-size:38px;font-weight:700;color:var(--neu-maroon);margin:4px 0;font-family:'Playfair Display',serif;}
  .stat-card .sub{font-size:12px;color:var(--text-muted);}
  .stat-card.gold{border-left-color:var(--neu-gold);} .stat-card.gold .value{color:var(--neu-gold);}
  .stat-card.green{border-left-color:var(--success);} .stat-card.green .value{color:var(--success);}
  .panel{background:#fff;border-radius:var(--radius);box-shadow:var(--shadow);overflow:hidden;}
  .panel-header{padding:20px 24px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:12px;flex-wrap:wrap;}
  .panel-header h3{font-family:'Playfair Display',serif;font-size:18px;flex:1;}
  .search-box{display:flex;align-items:center;gap:8px;background:var(--neu-light);border-radius:9px;padding:8px 14px;border:1.5px solid transparent;transition:border-color .2s;flex:1;min-width:200px;max-width:340px;}
  .search-box:focus-within{border-color:var(--neu-maroon);background:#fff;}
  .search-box input{border:none;background:transparent;font-family:'DM Sans',sans-serif;font-size:14px;flex:1;outline:none;}
  .date-filter select,.date-filter input{padding:8px 12px;border:1.5px solid var(--border);border-radius:9px;font-size:13px;font-family:'DM Sans',sans-serif;background:var(--neu-light);}
  table{width:100%;border-collapse:collapse;}
  thead{background:var(--neu-light);}
  thead th{padding:12px 16px;text-align:left;font-size:12px;font-weight:600;text-transform:uppercase;letter-spacing:.5px;color:var(--text-muted);}
  tbody tr{border-bottom:1px solid var(--border);transition:background .15s;}
  tbody tr:hover{background:#fdf8f4;}
  tbody td{padding:13px 16px;font-size:14px;}
  .badge-reason{display:inline-block;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:500;background:#e6f4ec;color:#1a6b3a;}
  .badge-type{display:inline-block;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:500;}
  .badge-student{background:#e8f0fe;color:#1a56db;}
  .badge-faculty{background:#fdf2f8;color:#9d174d;}
  .badge-employee{background:#f0fdf4;color:#166534;}
  .empty-state{text-align:center;padding:60px 20px;color:var(--text-muted);}
  .empty-state .icon{font-size:48px;margin-bottom:12px;}
  .pagination{display:flex;justify-content:flex-end;align-items:center;gap:8px;padding:16px 24px;border-top:1px solid var(--border);font-size:13px;}
  .page-btn{padding:6px 12px;border-radius:7px;border:1.5px solid var(--border);background:#fff;cursor:pointer;font-family:'DM Sans',sans-serif;transition:all .2s;}
  .page-btn.active{background:var(--neu-maroon);color:#fff;border-color:var(--neu-maroon);}
  .btn{width:100%;padding:14px;border:none;border-radius:10px;font-size:15px;font-weight:600;cursor:pointer;transition:all .2s;font-family:'DM Sans',sans-serif;}
  .btn-primary{background:var(--neu-maroon);color:#fff;}
  .btn-secondary{background:var(--neu-light);color:var(--text-main);}
  .btn-danger{background:var(--danger);color:#fff;}
  .btn-success{background:var(--success);color:#fff;}
  .btn-sm{width:auto;padding:7px 14px;font-size:13px;border-radius:8px;}
  .modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.5);display:none;align-items:center;justify-content:center;z-index:200;backdrop-filter:blur(3px);}
  .modal-overlay.open{display:flex;}
  .modal{background:#fff;border-radius:20px;padding:36px;width:100%;max-width:420px;animation:slideUp .3s ease;}
  .modal h3{font-family:'Playfair Display',serif;margin-bottom:20px;font-size:20px;}
  .modal-actions{display:flex;gap:10px;justify-content:flex-end;margin-top:24px;}
  .form-group{margin-bottom:18px;}
  .form-group label{display:block;font-size:13px;font-weight:500;color:var(--text-muted);margin-bottom:6px;text-transform:uppercase;letter-spacing:.4px;}
  .form-group input,.form-group select{width:100%;padding:13px 16px;border:1.5px solid var(--border);border-radius:10px;font-size:15px;font-family:'DM Sans',sans-serif;background:var(--neu-cream);}
  .chart-bar-wrap{padding:20px 24px;}
  .chart-row{display:flex;align-items:center;gap:12px;margin-bottom:12px;}
  .chart-label{font-size:12px;width:120px;text-align:right;color:var(--text-muted);flex-shrink:0;}
  .chart-bar-bg{flex:1;height:22px;background:var(--neu-light);border-radius:6px;overflow:hidden;}
  .chart-bar{height:100%;background:linear-gradient(90deg,#1a6b3a,#2d9e5a);border-radius:6px;transition:width .5s ease;}
  .chart-count{font-size:13px;font-weight:600;color:var(--neu-maroon);width:30px;}
  .live-badge{display:inline-flex;align-items:center;gap:6px;background:#dcfce7;color:#166534;border:1.5px solid #bbf7d0;border-radius:20px;padding:4px 12px;font-size:12px;font-weight:600;}
  .live-dot{width:8px;height:8px;border-radius:50%;background:#16a34a;animation:pulse-dot 1.5s infinite;}
  @keyframes pulse-dot{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.4;transform:scale(.7)}}
  @keyframes slideUp{from{opacity:0;transform:translateY(30px)}to{opacity:1;transform:translateY(0)}}
  #rangeInputs{display:none;gap:6px;align-items:center;}
  .section-title{font-family:'Playfair Display',serif;font-size:20px;margin-bottom:16px;}
</style>
</head>
<body style="display:flex;flex-direction:column;">

<div class="topbar">
  <div class="topbar-logo">
    <img src="assets/library.jpg" alt="NEU" style="width:32px;height:32px;border-radius:50%;object-fit:cover;margin-right:8px;vertical-align:middle;">
    NEU Library <span>Visitor Log</span> — Admin Dashboard
  </div>
  <span class="topbar-user"><?= htmlspecialchars($adminEmail) ?></span>
  <button class="topbar-btn" onclick="exportPDF()">⬇ Export PDF</button>
  <button class="topbar-btn" onclick="adminLogout()">Logout</button>
</div>

<div class="content">
  <div class="tabs">
    <button class="tab active" onclick="showTab('dashboard',this)">📊 Dashboard</button>
    <button class="tab" onclick="showTab('visitors',this)">📋 Visitor Log</button>
    <button class="tab" onclick="showTab('blocked',this)">🚫 Blocked Users</button>
    <button class="tab" onclick="showTab('records',this)">👥 Student Records</button>
  </div>

  <!-- DASHBOARD -->
  <div id="tab-dashboard">
    <div class="stats-grid">
      <div class="stat-card" data-icon="👥"><div class="label">Today's Visitors</div><div class="value" id="statToday">0</div><div class="sub">Total logged in today</div></div>
      <div class="stat-card gold" data-icon="📅"><div class="label">This Week</div><div class="value" id="statWeek">0</div><div class="sub">Last 7 days</div></div>
      <div class="stat-card green" data-icon="📆"><div class="label">This Month</div><div class="value" id="statMonth">0</div><div class="sub" id="statMonthLabel"></div></div>
      <div class="stat-card" data-icon="📊" style="border-left-color:#6b7280;"><div class="label">Total All Time</div><div class="value" id="statTotal">0</div><div class="sub">Cumulative visitors</div></div>
    </div>
    <div class="panel" style="margin-bottom:24px;">
      <div class="panel-header"><h3>Visits by Reason</h3></div>
      <div class="chart-bar-wrap" id="reasonChart"></div>
    </div>
    <div class="panel">
      <div class="panel-header"><h3>Visits by Type</h3></div>
      <div class="chart-bar-wrap" id="typeChart"></div>
    </div>
  </div>

  <!-- VISITOR LOG -->
  <div id="tab-visitors" style="display:none;">
    <div class="panel">
      <div class="panel-header">
        <h3>Visitor Log</h3>
        <span class="live-badge"><span class="live-dot"></span> LIVE</span>
        <span id="lastUpdated" style="font-size:12px;color:var(--text-muted);"></span>
        <div class="search-box">🔍 <input type="text" id="searchBox" placeholder="Search name, program, reason..." oninput="renderTable()"></div>
        <div class="date-filter" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
          <select id="filterPeriod" onchange="handleFilterPeriod()">
            <option value="all">All Time</option>
            <option value="today">Today</option>
            <option value="week">This Week</option>
            <option value="month">This Month</option>
            <option value="range">Date Range</option>
          </select>
          <div id="rangeInputs">
            <input type="date" id="dateFrom">
            <input type="date" id="dateTo">
            <button class="btn btn-sm btn-secondary" onclick="renderTable()">Apply</button>
          </div>
        </div>
      </div>
      <table><thead><tr><th>#</th><th>Name / ID</th><th>Type</th><th>Program</th><th>Reason</th><th>Date & Time</th><th>Action</th></tr></thead>
      <tbody id="visitorTbody"></tbody></table>
      <div class="empty-state" id="emptyState" style="display:none;"><div class="icon">📭</div><div>No visitors found</div></div>
      <div class="pagination" id="paginationWrap"></div>
    </div>
  </div>

  <!-- BLOCKED -->
  <div id="tab-blocked" style="display:none;">
    <div class="panel">
      <div class="panel-header"><h3>Blocked Visitors</h3><button class="btn btn-sm btn-primary" onclick="openBlockModal()">+ Block Visitor</button></div>
      <table><thead><tr><th>RFID / Email</th><th>Reason Blocked</th><th>Blocked On</th><th>Action</th></tr></thead>
      <tbody id="blockedTbody"></tbody></table>
      <div class="empty-state" id="blockedEmpty" style="display:none;"><div class="icon">✅</div><div>No blocked users</div></div>
    </div>
  </div>

  <!-- RECORDS -->
  <div id="tab-records" style="display:none;">
    <div class="panel">
      <div class="panel-header"><h3>👥 Student / Staff Records</h3><button class="btn btn-sm btn-primary" onclick="openAddRecordModal()">+ Add Record</button></div>
      <div style="padding:16px 24px;">
        <input type="text" id="recordSearch" placeholder="Search by name, RFID or email…" style="width:100%;padding:10px 14px;border:1.5px solid var(--border);border-radius:9px;font-size:14px;font-family:'DM Sans',sans-serif;" oninput="renderRecords()">
      </div>
      <table><thead><tr><th>#</th><th>Name</th><th>Type</th><th>Program</th><th>RFID / Email</th><th>Action</th></tr></thead>
      <tbody id="recordsTbody"></tbody></table>
      <div class="empty-state" id="recordsEmpty" style="display:none;"><div class="icon">👤</div><div>No records yet.</div></div>
    </div>
  </div>
</div>

<!-- BLOCK MODAL -->
<div class="modal-overlay" id="blockModal">
  <div class="modal">
    <h3>🚫 Block a Visitor</h3>
    <div class="form-group"><label>Visitor RFID or Email</label><input type="text" id="blockId" placeholder="e.g. 2021-12345"></div>
    <div class="form-group"><label>Reason for Blocking</label><input type="text" id="blockReason" placeholder="e.g. Policy violation"></div>
    <div class="modal-actions">
      <button class="btn btn-sm btn-secondary" onclick="closeBlockModal()">Cancel</button>
      <button class="btn btn-sm btn-danger" onclick="doBlock()">Block Visitor</button>
    </div>
  </div>
</div>

<!-- ADD RECORD MODAL -->
<div class="modal-overlay" id="addRecordModal">
  <div class="modal">
    <h3>👤 Add Student / Staff Record</h3>
    <div class="form-group"><label>Full Name</label><input type="text" id="arName" placeholder="e.g. Maria Santos"></div>
    <div class="form-group"><label>RFID / Email</label><input type="text" id="arRfid" placeholder="e.g. 24-10011-123"></div>
    <div class="form-group"><label>Type</label><select id="arType"><option>Student</option><option>Faculty</option><option>Employee</option></select></div>
    <div class="form-group"><label>Program / Department</label><input type="text" id="arProgram" placeholder="e.g. BSIT"></div>
    <div class="form-group"><label>Year / Title (optional)</label><input type="text" id="arYear" placeholder="e.g. 2nd Year"></div>
    <p style="color:#c0392b;font-size:13px;display:none;" id="arError">Please fill in all required fields.</p>
    <div class="modal-actions">
      <button class="btn btn-sm btn-secondary" onclick="closeAddRecordModal()">Cancel</button>
      <button class="btn btn-sm btn-primary" onclick="saveRecord()">Save Record</button>
    </div>
  </div>
</div>

<script>
let allVisitors = [];
let allBlocked  = [];
let allRecords  = [];
let currentPage = 1;
const PAGE_SIZE = 12;

// ── LOAD DATA ──
async function loadVisitors() {
  const res = await fetch('api.php?action=get_visitors');
  allVisitors = await res.json();
}
async function loadBlocked() {
  const res = await fetch('api.php?action=get_blocked');
  allBlocked = await res.json();
}
async function loadRecords() {
  const res = await fetch('api.php?action=get_records');
  allRecords = await res.json();
}

// ── TABS ──
async function showTab(name, el) {
  ['dashboard','visitors','blocked','records'].forEach(t => {
    document.getElementById('tab-'+t).style.display = t===name ? 'block' : 'none';
  });
  document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
  el.classList.add('active');
  if (name === 'dashboard') { await loadVisitors(); updateStats(); renderCharts(); }
  if (name === 'visitors')  { await loadVisitors(); renderTable(); }
  if (name === 'blocked')   { await loadBlocked();  renderBlocked(); }
  if (name === 'records')   { await loadRecords();  renderRecords(); }
}

// ── STATS ──
function updateStats() {
  const now = new Date();
  const today = allVisitors.filter(v => new Date(v.timestamp).toDateString() === now.toDateString()).length;
  const week  = allVisitors.filter(v => (now - new Date(v.timestamp)) <= 7*86400000).length;
  const month = allVisitors.filter(v => { const d=new Date(v.timestamp); return d.getMonth()===now.getMonth()&&d.getFullYear()===now.getFullYear(); }).length;
  document.getElementById('statToday').textContent = today;
  document.getElementById('statWeek').textContent  = week;
  document.getElementById('statMonth').textContent = month;
  document.getElementById('statTotal').textContent = allVisitors.length;
  document.getElementById('statMonthLabel').textContent = now.toLocaleString('default',{month:'long',year:'numeric'});
}

// ── CHARTS ──
function renderCharts() {
  const reasons={}, types={};
  allVisitors.forEach(v => { reasons[v.reason]=(reasons[v.reason]||0)+1; types[v.type]=(types[v.type]||0)+1; });
  const maxR=Math.max(...Object.values(reasons),1), maxT=Math.max(...Object.values(types),1);
  document.getElementById('reasonChart').innerHTML = Object.entries(reasons).sort((a,b)=>b[1]-a[1]).map(([k,v])=>
    `<div class="chart-row"><div class="chart-label">${k}</div><div class="chart-bar-bg"><div class="chart-bar" style="width:${v/maxR*100}%"></div></div><div class="chart-count">${v}</div></div>`).join('');
  document.getElementById('typeChart').innerHTML = Object.entries(types).sort((a,b)=>b[1]-a[1]).map(([k,v])=>
    `<div class="chart-row"><div class="chart-label">${k}</div><div class="chart-bar-bg"><div class="chart-bar" style="width:${v/maxT*100}%;background:linear-gradient(90deg,#c49010,#e8b820)"></div></div><div class="chart-count">${v}</div></div>`).join('');
}

// ── FILTER ──
function getFiltered() {
  const q = (document.getElementById('searchBox')?.value||'').toLowerCase();
  const period = document.getElementById('filterPeriod')?.value||'all';
  const now = new Date();
  return allVisitors.filter(v => {
    const ts = new Date(v.timestamp);
    let inPeriod = true;
    if (period==='today') inPeriod = ts.toDateString()===now.toDateString();
    else if (period==='week') inPeriod = (now-ts)<=7*86400000;
    else if (period==='month') inPeriod = ts.getMonth()===now.getMonth()&&ts.getFullYear()===now.getFullYear();
    else if (period==='range') {
      const from=document.getElementById('dateFrom').value, to=document.getElementById('dateTo').value;
      if (from) inPeriod = inPeriod && ts >= new Date(from);
      if (to)   inPeriod = inPeriod && ts <= new Date(to+'T23:59:59');
    }
    const matchQ = !q || v.name.toLowerCase().includes(q)||v.program.toLowerCase().includes(q)||v.reason.toLowerCase().includes(q)||v.rfid.toLowerCase().includes(q);
    return inPeriod && matchQ;
  });
}

function handleFilterPeriod() {
  const p = document.getElementById('filterPeriod').value;
  document.getElementById('rangeInputs').style.display = p==='range' ? 'flex' : 'none';
  renderTable();
}

// ── TABLE ──
function renderTable() {
  const filtered = getFiltered();
  const tbody = document.getElementById('visitorTbody');
  const total = filtered.length;
  const pages = Math.ceil(total/PAGE_SIZE);
  if (currentPage > pages) currentPage = 1;
  const slice = filtered.slice((currentPage-1)*PAGE_SIZE, currentPage*PAGE_SIZE);
  tbody.innerHTML = slice.map((v,i) => {
    const dt = new Date(v.timestamp);
    const typeClass = v.type==='Student'?'badge-student':v.type==='Faculty'?'badge-faculty':'badge-employee';
    const initials = (v.name||'?').split(' ').map(n=>n[0]).join('').toUpperCase().slice(0,2);
    return `<tr>
      <td>${(currentPage-1)*PAGE_SIZE+i+1}</td>
      <td style="display:flex;align-items:center;">
        <div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#1a6b3a,#2d9e58);display:inline-flex;align-items:center;justify-content:center;color:#fff;font-size:12px;font-weight:700;margin-right:8px;flex-shrink:0;">${initials}</div>
        <div><strong>${v.name}</strong><br><small style="color:var(--text-muted)">${v.rfid}</small></div>
      </td>
      <td><span class="badge-type ${typeClass}">${v.type}</span></td>
      <td>${v.program}</td>
      <td><span class="badge-reason">${v.reason}</span></td>
      <td>${dt.toLocaleDateString('en-PH',{month:'short',day:'numeric',year:'numeric'})} ${dt.toLocaleTimeString('en-PH',{hour:'2-digit',minute:'2-digit'})}</td>
      <td><button class="btn btn-sm btn-danger" onclick="quickBlock('${v.rfid}')">Block</button></td>
    </tr>`;
  }).join('');
  document.getElementById('emptyState').style.display = total===0 ? 'block' : 'none';
  renderPagination(pages);
  document.getElementById('lastUpdated').textContent = 'Last updated: ' + new Date().toLocaleTimeString('en-PH',{hour:'2-digit',minute:'2-digit',second:'2-digit'});
}

function renderPagination(pages) {
  const wrap = document.getElementById('paginationWrap');
  if (pages<=1) { wrap.innerHTML=''; return; }
  let html = `<span style="color:var(--text-muted);margin-right:6px">Page ${currentPage} of ${pages}</span>`;
  for (let i=1;i<=pages;i++) html+=`<button class="page-btn ${i===currentPage?'active':''}" onclick="goPage(${i})">${i}</button>`;
  wrap.innerHTML = html;
}
function goPage(n) { currentPage=n; renderTable(); }

// ── BLOCKED ──
function renderBlocked() {
  const tbody = document.getElementById('blockedTbody');
  document.getElementById('blockedEmpty').style.display = allBlocked.length===0 ? 'block' : 'none';
  tbody.innerHTML = allBlocked.map(b => {
    const dt = new Date(b.blocked_at);
    return `<tr><td><strong>${b.rfid}</strong></td><td>${b.reason}</td><td>${dt.toLocaleDateString('en-PH',{month:'short',day:'numeric',year:'numeric'})}</td><td><button class="btn btn-sm btn-success" onclick="unblock('${b.rfid}')">Unblock</button></td></tr>`;
  }).join('');
}
function openBlockModal() { document.getElementById('blockModal').classList.add('open'); }
function closeBlockModal() { document.getElementById('blockModal').classList.remove('open'); }
async function doBlock() {
  const rfid=document.getElementById('blockId').value.trim(), reason=document.getElementById('blockReason').value.trim();
  if (!rfid) return;
  const fd=new FormData(); fd.append('action','block'); fd.append('rfid',rfid); fd.append('reason',reason||'Unspecified');
  await fetch('api.php',{method:'POST',body:fd});
  closeBlockModal(); await loadBlocked(); renderBlocked();
}
async function quickBlock(rfid) {
  if (!confirm(`Block visitor ${rfid}?`)) return;
  const fd=new FormData(); fd.append('action','block'); fd.append('rfid',rfid); fd.append('reason','Blocked by admin');
  await fetch('api.php',{method:'POST',body:fd});
  await loadVisitors(); await loadBlocked(); renderTable(); renderBlocked();
}
async function unblock(rfid) {
  const fd=new FormData(); fd.append('action','unblock'); fd.append('rfid',rfid);
  await fetch('api.php',{method:'POST',body:fd});
  await loadBlocked(); renderBlocked();
}

// ── RECORDS ──
function renderRecords() {
  const q=(document.getElementById('recordSearch')?.value||'').toLowerCase();
  const filtered = allRecords.filter(r => !q||r.name.toLowerCase().includes(q)||r.rfid.toLowerCase().includes(q)||r.program.toLowerCase().includes(q));
  const tbody=document.getElementById('recordsTbody');
  document.getElementById('recordsEmpty').style.display=filtered.length===0?'block':'none';
  const colors={Student:'#e8f0fe|#1a56db',Faculty:'#fdf2f8|#9d174d',Employee:'#f0fdf4|#166534'};
  tbody.innerHTML=filtered.map((r,i)=>{
    const [bg,col]=(colors[r.type]||'#e6f4ec|#1a6b3a').split('|');
    const initials=r.name.replace(/^(Dr\.|Prof\.)\s*/i,'').split(' ').map(n=>n[0]).join('').toUpperCase().slice(0,2);
    return `<tr>
      <td>${i+1}</td>
      <td style="display:flex;align-items:center;"><div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#1a6b3a,#2d9e58);display:inline-flex;align-items:center;justify-content:center;color:#fff;font-size:11px;font-weight:700;margin-right:8px;">${initials}</div><strong>${r.name}</strong>${r.year_level?`<br><small style="color:var(--text-muted)">${r.year_level}</small>`:''}</td>
      <td><span style="display:inline-block;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;background:${bg};color:${col};">${r.type}</span></td>
      <td>${r.program}</td>
      <td><code style="font-size:12px;background:#f0fdf4;padding:2px 6px;border-radius:6px;">${r.rfid}</code></td>
      <td><button class="btn btn-sm btn-danger" onclick="deleteRecord(${r.id})">Delete</button></td>
    </tr>`;
  }).join('');
}
function openAddRecordModal() {
  ['arName','arRfid','arProgram','arYear'].forEach(id=>document.getElementById(id).value='');
  document.getElementById('arType').value='Student';
  document.getElementById('arError').style.display='none';
  document.getElementById('addRecordModal').classList.add('open');
}
function closeAddRecordModal() { document.getElementById('addRecordModal').classList.remove('open'); }
async function saveRecord() {
  const name=document.getElementById('arName').value.trim(), rfid=document.getElementById('arRfid').value.trim();
  const type=document.getElementById('arType').value, program=document.getElementById('arProgram').value.trim();
  const year=document.getElementById('arYear').value.trim();
  if (!name||!rfid||!program) { document.getElementById('arError').style.display='block'; return; }
  const fd=new FormData(); fd.append('action','add_record'); fd.append('name',name); fd.append('rfid',rfid); fd.append('type',type); fd.append('program',program); fd.append('year',year);
  await fetch('api.php',{method:'POST',body:fd});
  closeAddRecordModal(); await loadRecords(); renderRecords();
}
async function deleteRecord(id) {
  if (!confirm('Delete this record?')) return;
  const fd=new FormData(); fd.append('action','delete_record'); fd.append('id',id);
  await fetch('api.php',{method:'POST',body:fd});
  await loadRecords(); renderRecords();
}

// ── LOGOUT ──
async function adminLogout() {
  const fd=new FormData(); fd.append('action','logout');
  await fetch('api.php',{method:'POST',body:fd});
  window.location.href='index.php';
}

// ── PDF EXPORT ──
function exportPDF() {
  const {jsPDF}=window.jspdf;
  const doc=new jsPDF();
  const filtered=getFiltered();
  doc.setFontSize(16); doc.setTextColor(26,107,58);
  doc.text('NEU Library Visitor Log',14,18);
  doc.setFontSize(10); doc.setTextColor(100);
  doc.text(`Generated: ${new Date().toLocaleString('en-PH')}  |  Total: ${filtered.length} visitors`,14,26);
  doc.autoTable({
    startY:32,
    head:[['#','RFID','Name','Type','Program','Reason','Date & Time']],
    body:filtered.map((v,i)=>[i+1,v.rfid,v.name,v.type,v.program,v.reason,new Date(v.timestamp).toLocaleString('en-PH')]),
    styles:{fontSize:9},
    headStyles:{fillColor:[26,107,58]},
    alternateRowStyles:{fillColor:[230,244,236]}
  });
  doc.save('NEU_Library_VisitorLog.pdf');
}

// ── AUTO REFRESH every 10 seconds ──
setInterval(async () => {
  const activeTab = document.querySelector('.tab.active')?.textContent;
  if (activeTab?.includes('Visitor')) { await loadVisitors(); renderTable(); }
  if (activeTab?.includes('Dashboard')) { await loadVisitors(); updateStats(); renderCharts(); }
}, 10000);

// ── INIT ──
(async () => {
  await loadVisitors();
  updateStats();
  renderCharts();
})();
</script>
</body>
</html>
```

---

## Step 6 — How to Run in XAMPP

1. **Copy your project folder** to:
```
   C:/xampp/htdocs/NEULibproject/