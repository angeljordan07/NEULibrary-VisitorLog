<?php
// Force HTTPS
if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
    if (!headers_sent()) {
        header("Location: https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        exit();
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>NEU Library Visitor Log</title>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@500;700&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.31/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script src="https://accounts.google.com/gsi/client" async></script>
<!-- QR Code scanner library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jsQR/1.4.0/jsQR.min.js"></script>
<style>
:root {
  --neu-maroon: #1a6b3a;
  --neu-gold: #D4A017;
  --neu-cream: #f4faf6;
  --neu-light: #e6f4ec;
  --text-main: #0a1f10;
  --text-muted: #4a7a5a;
  --border: #c8e6d0;
  --success: #1a6b3a;
  --danger: #c0392b;
  --radius: 14px;
  --shadow: 0 4px 32px rgba(26,107,58,0.10);
}
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: 'DM Sans', sans-serif; background: var(--neu-cream); color: var(--text-main); min-height: 100vh; }

/* ── LOGIN ── */
#loginScreen { min-height: 100vh; display: flex; align-items: center; justify-content: center; position: relative; overflow: hidden; }
#loginScreen::before { content: ''; position: absolute; inset: 0; background: rgba(0,0,0,0.45); z-index: 1; }
#ytBgWrap { position: absolute; inset: 0; z-index: 0; pointer-events: none; overflow: hidden; }
#ytBgWrap video { position: absolute; top: 50%; left: 50%; width: 100vw; height: 56.25vw; min-height: 100vh; min-width: 177.78vh; transform: translate(-50%, -50%); object-fit: cover; }
.login-card { background: rgba(255,255,255,0.92); border-radius: 24px; padding: 44px 40px; width: 100%; max-width: 460px; box-shadow: 0 24px 80px rgba(0,0,0,0.35); position: relative; z-index: 2; animation: slideUp .5s ease; max-height: 95vh; overflow-y: auto; }
@keyframes slideUp { from { opacity:0; transform: translateY(30px); } to { opacity:1; transform: translateY(0); } }
.login-logo { text-align: center; margin-bottom: 24px; }
.login-logo h1 { font-family: 'Playfair Display', serif; font-size: 22px; color: var(--neu-maroon); line-height: 1.2; }
.login-logo p { font-size: 13px; color: var(--text-muted); margin-top: 4px; }
.form-group { margin-bottom: 16px; }
.form-group label { display: block; font-size: 12px; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; letter-spacing:.4px; text-transform: uppercase; }
.form-group input, .form-group select { width: 100%; padding: 12px 16px; border: 1.5px solid var(--border); border-radius: 10px; font-size: 15px; font-family: 'DM Sans', sans-serif; transition: border-color .2s; background: var(--neu-cream); }
.form-group input:focus, .form-group select:focus { outline: none; border-color: var(--neu-maroon); background: #fff; }
.btn { width: 100%; padding: 13px; border: none; border-radius: 10px; font-size: 15px; font-weight: 600; cursor: pointer; transition: all .2s; font-family: 'DM Sans', sans-serif; }
.btn-primary { background: var(--neu-maroon); color: #fff; }
.btn-primary:hover { background: #145530; transform: translateY(-1px); box-shadow: 0 6px 20px rgba(26,107,58,0.35); }
.btn-secondary { background: var(--neu-light); color: var(--text-main); }
.btn-secondary:hover { background: var(--border); }
.btn-sm { width: auto; padding: 7px 14px; font-size: 13px; border-radius: 8px; }
.btn-danger { background: var(--danger); color: #fff; }
.btn-danger:hover { background: #a93226; }
.btn-success { background: var(--success); color: #fff; }
.login-switch { text-align: center; margin-top: 16px; font-size: 13px; color: var(--text-muted); }
.login-switch a { color: var(--neu-maroon); font-weight: 600; cursor: pointer; text-decoration: none; }
.error-msg { color: var(--danger); font-size: 13px; margin-top: 8px; display: none; }

/* ── LOGIN METHOD TABS ── */
.method-tabs { display: flex; background: var(--neu-light); border-radius: 12px; padding: 4px; margin-bottom: 20px; gap: 4px; }
.method-tab { flex: 1; padding: 10px 6px; border-radius: 9px; border: none; background: transparent; font-family: 'DM Sans', sans-serif; font-size: 13px; font-weight: 500; cursor: pointer; color: var(--text-muted); transition: all .2s; text-align: center; }
.method-tab.active { background: #fff; color: var(--neu-maroon); box-shadow: 0 2px 8px rgba(0,0,0,.08); font-weight: 600; }

/* ── QR SCANNER ── */
#qrSection { display: none; }
.qr-scanner-wrap {
  position: relative; border-radius: 16px; overflow: hidden;
  background: #000; margin-bottom: 14px;
  aspect-ratio: 1; width: 100%;
}
#qrVideo { width: 100%; height: 100%; object-fit: cover; display: block; }
#qrCanvas { display: none; }
.qr-overlay {
  position: absolute; inset: 0;
  display: flex; align-items: center; justify-content: center;
  pointer-events: none;
}
.qr-frame {
  width: 65%; aspect-ratio: 1;
  border: 3px solid var(--neu-gold);
  border-radius: 12px;
  box-shadow: 0 0 0 9999px rgba(0,0,0,0.45);
  position: relative;
}
.qr-frame::before, .qr-frame::after,
.qr-frame-tl::before, .qr-frame-tl::after {
  content: ''; position: absolute; width: 22px; height: 22px;
  border-color: #fff; border-style: solid;
}
.qr-frame::before  { top: -3px;  left: -3px;  border-width: 4px 0 0 4px; border-radius: 6px 0 0 0; }
.qr-frame::after   { top: -3px;  right: -3px; border-width: 4px 4px 0 0; border-radius: 0 6px 0 0; }
.qr-frame-tl::before { bottom: -3px; left: -3px;  border-width: 0 0 4px 4px; border-radius: 0 0 0 6px; }
.qr-frame-tl::after  { bottom: -3px; right: -3px; border-width: 0 4px 4px 0; border-radius: 0 0 6px 0; }
.qr-scan-line {
  position: absolute; left: 8%; right: 8%; height: 2px;
  background: linear-gradient(90deg, transparent, var(--neu-gold), transparent);
  animation: scanLine 2s linear infinite;
  top: 10%;
}
@keyframes scanLine { 0% { top: 10%; } 100% { top: 88%; } }
.qr-status { text-align: center; font-size: 13px; color: var(--text-muted); margin-bottom: 10px; min-height: 20px; }
.qr-status.success { color: var(--success); font-weight: 600; }
.qr-status.error   { color: var(--danger);  font-weight: 600; }
.qr-status.scanning { color: var(--neu-maroon); }
#startScanBtn { margin-bottom: 8px; }
#stopScanBtn  { display: none; background: var(--danger); color: #fff; margin-bottom: 8px; }
#stopScanBtn:hover { background: #a93226; }

/* ── GOOGLE / RFID ── */
.google-btn { display: flex; align-items: center; justify-content: center; gap: 10px; width: 100%; padding: 12px 16px; border: 1.5px solid #dadce0; border-radius: 10px; background: #fff; font-size: 14px; font-weight: 600; cursor: pointer; transition: all .2s; font-family: 'DM Sans', sans-serif; color: #3c4043; box-shadow: 0 1px 4px rgba(0,0,0,0.1); margin-bottom: 12px; }
.google-btn:hover { box-shadow: 0 2px 10px rgba(0,0,0,0.18); background: #f8f9fa; }
.divider { display: flex; align-items: center; gap: 10px; margin: 4px 0 14px; color: var(--text-muted); font-size: 13px; }
.divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: var(--border); }
.google-user-bar { display: flex; align-items: center; gap: 10px; background: var(--neu-light); border: 1.5px solid var(--border); border-radius: 10px; padding: 10px 14px; margin-bottom: 14px; }
.google-user-bar img { width: 36px; height: 36px; border-radius: 50%; border: 2px solid var(--neu-maroon); }
.google-user-bar .g-name  { font-weight: 600; font-size: 14px; }
.google-user-bar .g-email { font-size: 12px; color: var(--text-muted); }
.google-user-bar .g-signout { margin-left: auto; font-size: 12px; color: var(--danger); cursor: pointer; font-weight: 600; }
.google-signed-note { font-size: 12px; color: var(--text-muted); background: var(--neu-light); border-radius: 8px; padding: 8px 12px; margin-bottom: 12px; border-left: 3px solid var(--neu-maroon); }
#visitForm { display: none; }

/* ── WELCOME ── */
#welcomeScreen { display: none; min-height: 100vh; align-items: center; justify-content: center; background: linear-gradient(135deg, #1a6b3a 0%, #0f4424 100%); }
.welcome-card { background: #fff; border-radius: 24px; padding: 56px 48px; text-align: center; max-width: 480px; box-shadow: 0 24px 80px rgba(0,0,0,.3); animation: slideUp .4s ease; }
.welcome-card .checkmark { font-size: 64px; margin-bottom: 16px; }
.welcome-card h2 { font-family: 'Playfair Display', serif; font-size: 30px; color: var(--neu-maroon); }
.welcome-card .program { color: var(--text-muted); font-size: 15px; margin-top: 6px; }
.welcome-card .reason-tag { display: inline-block; background: var(--neu-light); border: 1.5px solid var(--border); border-radius: 20px; padding: 6px 18px; font-size: 13px; margin-top: 16px; color: var(--neu-maroon); font-weight: 500; }
.welcome-tagline { font-size: 20px; color: var(--neu-gold); font-weight: 600; margin: 20px 0 8px; }
.countdown { color: var(--text-muted); font-size: 13px; }

/* ── APP SHELL ── */
#appScreen { display: none; flex-direction: column; min-height: 100vh; }
.topbar { background: var(--neu-maroon); color: #fff; display: flex; align-items: center; padding: 0 28px; height: 64px; gap: 16px; box-shadow: 0 2px 16px rgba(0,0,0,.2); position: sticky; top: 0; z-index: 100; }
.topbar-logo { font-family: 'Playfair Display', serif; font-size: 17px; flex: 1; display: flex; align-items: center; gap: 10px; }
.topbar-logo span { color: var(--neu-gold); }
.topbar-user { font-size: 13px; color: rgba(255,255,255,.8); }
.topbar-btn { background: rgba(255,255,255,.15); border: none; color: #fff; padding: 7px 16px; border-radius: 8px; cursor: pointer; font-size: 13px; font-family: 'DM Sans', sans-serif; transition: background .2s; }
.topbar-btn:hover { background: rgba(255,255,255,.25); }
.topbar-btn.excel { background: rgba(21,128,61,0.5); }
.topbar-btn.excel:hover { background: rgba(21,128,61,0.75); }
.content { flex: 1; padding: 28px; max-width: 1100px; margin: 0 auto; width: 100%; }
.tabs { display: flex; gap: 6px; margin-bottom: 26px; background: var(--neu-light); border-radius: 12px; padding: 5px; width: fit-content; }
.tab { padding: 9px 22px; border-radius: 9px; border: none; background: transparent; font-family: 'DM Sans', sans-serif; font-size: 14px; font-weight: 500; cursor: pointer; color: var(--text-muted); transition: all .2s; }
.tab.active { background: #fff; color: var(--neu-maroon); box-shadow: 0 2px 8px rgba(0,0,0,.08); font-weight: 600; }
.stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px; }
.stat-card { background: #fff; border-radius: var(--radius); padding: 24px 20px; box-shadow: var(--shadow); border-left: 4px solid var(--neu-maroon); position: relative; overflow: hidden; }
.stat-card::after { content: attr(data-icon); position: absolute; right: 16px; top: 16px; font-size: 32px; opacity: .15; }
.stat-card .label { font-size: 12px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: .6px; }
.stat-card .value { font-size: 38px; font-weight: 700; color: var(--neu-maroon); margin: 4px 0; font-family: 'Playfair Display', serif; }
.stat-card .sub { font-size: 12px; color: var(--text-muted); }
.stat-card.gold { border-left-color: var(--neu-gold); } .stat-card.gold .value { color: var(--neu-gold); }
.stat-card.green { border-left-color: var(--success); } .stat-card.green .value { color: var(--success); }
.panel { background: #fff; border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden; }
.panel-header { padding: 20px 24px; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
.panel-header h3 { font-family: 'Playfair Display', serif; font-size: 18px; flex: 1; }
.search-box { display: flex; align-items: center; gap: 8px; background: var(--neu-light); border-radius: 9px; padding: 8px 14px; border: 1.5px solid transparent; transition: border-color .2s; flex: 1; min-width: 200px; max-width: 340px; }
.search-box:focus-within { border-color: var(--neu-maroon); background: #fff; }
.search-box input { border: none; background: transparent; font-family: 'DM Sans', sans-serif; font-size: 14px; flex: 1; outline: none; }
.date-filter { display: flex; gap: 8px; flex-wrap: wrap; align-items: center; }
.date-filter select, .date-filter input { padding: 8px 12px; border: 1.5px solid var(--border); border-radius: 9px; font-size: 13px; font-family: 'DM Sans', sans-serif; background: var(--neu-light); }
table { width: 100%; border-collapse: collapse; }
thead { background: var(--neu-light); }
thead th { padding: 12px 16px; text-align: left; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: .5px; color: var(--text-muted); }
tbody tr { border-bottom: 1px solid var(--border); transition: background .15s; }
tbody tr:hover { background: #fdf8f4; }
tbody tr:last-child { border-bottom: none; }
tbody td { padding: 13px 16px; font-size: 14px; }
.badge-reason { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 500; background: #e6f4ec; color: #1a6b3a; }
.badge-type { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 500; }
.badge-student { background: #e8f0fe; color: #1a56db; }
.badge-faculty  { background: #fdf2f8; color: #9d174d; }
.badge-employee { background: #f0fdf4; color: #166534; }
.empty-state { text-align: center; padding: 60px 20px; color: var(--text-muted); }
.empty-state .icon { font-size: 48px; margin-bottom: 12px; }
.pagination { display: flex; justify-content: flex-end; align-items: center; gap: 8px; padding: 16px 24px; border-top: 1px solid var(--border); font-size: 13px; }
.page-btn { padding: 6px 12px; border-radius: 7px; border: 1.5px solid var(--border); background: #fff; cursor: pointer; font-family: 'DM Sans', sans-serif; transition: all .2s; }
.page-btn:hover { border-color: var(--neu-maroon); color: var(--neu-maroon); }
.page-btn.active { background: var(--neu-maroon); color: #fff; border-color: var(--neu-maroon); }
.modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,.5); display: none; align-items: center; justify-content: center; z-index: 200; backdrop-filter: blur(3px); }
.modal-overlay.open { display: flex; }
.modal { background: #fff; border-radius: 20px; padding: 36px; width: 100%; max-width: 420px; animation: slideUp .3s ease; }
.modal h3 { font-family: 'Playfair Display', serif; margin-bottom: 20px; font-size: 20px; }
.modal-actions { display: flex; gap: 10px; justify-content: flex-end; margin-top: 24px; }
.chart-bar-wrap { padding: 20px 24px; }
.chart-row { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; }
.chart-label { font-size: 12px; width: 90px; text-align: right; color: var(--text-muted); flex-shrink: 0; }
.chart-bar-bg { flex: 1; height: 22px; background: var(--neu-light); border-radius: 6px; overflow: hidden; }
.chart-bar { height: 100%; background: linear-gradient(90deg, #1a6b3a, #2d9e5a); border-radius: 6px; transition: width .5s ease; min-width: 2px; }
.chart-count { font-size: 13px; font-weight: 600; color: var(--neu-maroon); width: 30px; }
.live-badge { display: inline-flex; align-items: center; gap: 6px; background: #dcfce7; color: #166534; border: 1.5px solid #bbf7d0; border-radius: 20px; padding: 4px 12px; font-size: 12px; font-weight: 600; }
.live-dot { width: 8px; height: 8px; border-radius: 50%; background: #16a34a; animation: pulse-dot 1.5s infinite; }
@keyframes pulse-dot { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.4;transform:scale(.7)} }
.new-row-flash { animation: flashRow 1.5s ease; }
@keyframes flashRow { 0%{background:#dcfce7} 100%{background:transparent} }
.section-title { font-family: 'Playfair Display', serif; font-size: 20px; margin-bottom: 16px; }

/* QR scan reason modal */
.reason-modal { position: fixed; inset: 0; background: rgba(0,0,0,.6); display: none; align-items: center; justify-content: center; z-index: 300; backdrop-filter: blur(4px); }
.reason-modal.open { display: flex; }
.reason-card { background: #fff; border-radius: 20px; padding: 32px; width: 100%; max-width: 380px; animation: slideUp .3s ease; text-align: center; }
.reason-card h3 { font-family: 'Playfair Display', serif; font-size: 20px; color: var(--neu-maroon); margin-bottom: 6px; }
.reason-card .scanned-name { font-size: 15px; color: var(--text-muted); margin-bottom: 20px; }
.reason-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 16px; }
.reason-chip { padding: 12px 8px; border-radius: 10px; border: 1.5px solid var(--border); background: var(--neu-light); font-family: 'DM Sans', sans-serif; font-size: 13px; font-weight: 500; cursor: pointer; transition: all .2s; color: var(--text-main); }
.reason-chip:hover { background: var(--neu-maroon); color: #fff; border-color: var(--neu-maroon); transform: translateY(-1px); }
</style>
</head>
<body>

<!-- LOGIN SCREEN -->
<div id="loginScreen">
  <div id="ytBgWrap">
    <video autoplay muted loop playsinline>
      <source src="assets/video.mp4" type="video/mp4">
    </video>
  </div>
  <div class="login-card">
    <div class="login-logo">
      <img src="assets/neu_logo.png" alt="NEU Logo" style="width:80px;height:80px;border-radius:50%;object-fit:cover;margin-bottom:10px;border:3px solid #1a6b3a;">
      <h1>NEU Library<br>Visitor Log</h1>
      <p>New Era University — Library Management</p>
    </div>

    <div id="visitorLoginForm">
      <!-- Method tabs -->
      <div class="method-tabs">
        <button class="method-tab active" onclick="switchMethod('qr', this)">📷 QR Scan</button>
        <button class="method-tab" onclick="switchMethod('google', this)">🔑 Google</button>
        <button class="method-tab" onclick="switchMethod('rfid', this)">🪪 RFID</button>
      </div>

      <!-- ── QR SCANNER SECTION ── -->
      <div id="qrSection">
        <p style="font-size:13px;color:var(--text-muted);text-align:center;margin-bottom:12px;">
          Show your <strong>QR code</strong> to the camera to log in instantly
        </p>
        <div class="qr-scanner-wrap">
          <video id="qrVideo" autoplay playsinline muted></video>
          <canvas id="qrCanvas"></canvas>
          <div class="qr-overlay">
            <div class="qr-frame">
              <div class="qr-frame-tl"></div>
              <div class="qr-scan-line"></div>
            </div>
          </div>
        </div>
        <div class="qr-status" id="qrStatus">Press Start to activate camera</div>
        <button class="btn btn-primary" id="startScanBtn" onclick="startScanner()">▶ Start Camera</button>
        <button class="btn" id="stopScanBtn" onclick="stopScanner()">■ Stop Camera</button>
        <p style="font-size:11px;color:var(--text-muted);text-align:center;margin-top:8px;">
          💡 Your QR code should contain your RFID number or NEU email
        </p>
      </div>

      <!-- ── GOOGLE SIGN-IN SECTION ── -->
      <div id="googleSection" style="display:none;">
        <p style="font-size:13px;color:var(--text-muted);margin-bottom:14px;text-align:center;">
          Sign in with your NEU Google account
        </p>
        <button class="google-btn" onclick="triggerGoogleSignIn()">
          <svg width="20" height="20" viewBox="0 0 48 48">
            <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.08 17.74 9.5 24 9.5z"/>
            <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
            <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
            <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.18 1.48-4.97 2.31-8.16 2.31-6.26 0-11.57-3.59-13.46-8.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
          </svg>
          Sign in with Google (@neu.edu.ph)
        </button>
        <p class="error-msg" id="googleError" style="display:none;text-align:center;"></p>
      </div>

      <!-- ── RFID SECTION ── -->
      <div id="rfidSection" style="display:none;">
        <div class="form-group">
          <label>RFID or Institutional Email</label>
          <input type="text" id="loginId" placeholder="e.g. 24-12345-345 or name@neu.edu.ph">
        </div>
        <button class="btn btn-secondary" onclick="proceedWithRFID()">Continue</button>
        <p class="error-msg" id="rfidError" style="display:none;"></p>
      </div>

      <!-- ── VISIT FORM (shown after any login method) ── -->
      <div id="visitForm">
        <div class="google-user-bar" id="googleUserBar" style="display:none;">
          <img id="gUserPhoto" src="" alt="">
          <div><div class="g-name" id="gUserName"></div><div class="g-email" id="gUserEmail"></div></div>
          <span class="g-signout" onclick="cancelVisitForm()">Sign out</span>
        </div>
        <div class="google-signed-note" id="googleSignedNote" style="display:none;"></div>
        <div class="form-group">
          <label>Visitor Type</label>
          <select id="loginType">
            <option value="Student">Student</option>
            <option value="Faculty">Faculty Member</option>
            <option value="Employee">Employee</option>
          </select>
        </div>
        <div class="form-group">
          <label>Program / Department</label>
          <input type="text" id="loginProgram" placeholder="e.g. BSIT, College of Nursing">
        </div>
        <div class="form-group">
          <label>Reason for Visit</label>
          <select id="loginReason">
            <option>Reading</option><option>Researching</option><option>Use of Computer</option>
            <option>Meeting</option><option>Borrowing Books</option><option>Studying</option><option>Other</option>
          </select>
        </div>
        <p class="error-msg" id="loginError"></p>
        <button class="btn btn-primary" onclick="visitorLogin()">Log In to Library</button>
        <div style="text-align:center;margin-top:10px;">
          <a style="font-size:13px;color:var(--text-muted);cursor:pointer;" onclick="cancelVisitForm()">← Back</a>
        </div>
      </div>

      <div class="login-switch" id="adminSwitchLink">Admin? <a onclick="showAdminLogin()">Sign in as Admin</a></div>
    </div>

    <!-- ADMIN FORM -->
    <div id="adminLoginForm" style="display:none;">
      <div class="form-group">
        <label>Admin Email</label>
        <div style="display:flex; align-items:stretch;">
          <input type="text" id="adminUser" placeholder="e.g. angelgrace" style="border-radius:10px 0 0 10px; border-right:none; flex:1;">
          <span style="padding:13px 14px; background:var(--neu-light); border:1.5px solid var(--border); border-left:none; border-radius:0 10px 10px 0; font-size:13px; color:var(--text-muted); white-space:nowrap; display:flex; align-items:center;">@neu.admin.lib</span>
        </div>
      </div>
      <div class="form-group">
        <label>Password</label>
        <input type="password" id="adminPass" placeholder="••••••">
      </div>
      <p class="error-msg" id="adminError"></p>
      <button class="btn btn-primary" onclick="adminLogin()">Admin Login</button>
      <div class="login-switch"><a onclick="showVisitorLogin()">← Back to Visitor Login</a></div>
    </div>
  </div>
</div>

<!-- QR REASON PICKER MODAL (pops up after successful QR scan) -->
<div class="reason-modal" id="reasonModal">
  <div class="reason-card">
    <div style="font-size:48px;margin-bottom:8px;">👋</div>
    <h3 id="reasonModalName">Welcome!</h3>
    <div class="scanned-name" id="reasonModalSub"></div>
    <p style="font-size:13px;color:var(--text-muted);margin-bottom:16px;">What is your reason for visiting today?</p>
    <div class="reason-grid">
      <button class="reason-chip" onclick="qrAutoLogin('Reading')">📖 Reading</button>
      <button class="reason-chip" onclick="qrAutoLogin('Researching')">🔍 Researching</button>
      <button class="reason-chip" onclick="qrAutoLogin('Use of Computer')">💻 Computer</button>
      <button class="reason-chip" onclick="qrAutoLogin('Meeting')">🤝 Meeting</button>
      <button class="reason-chip" onclick="qrAutoLogin('Borrowing Books')">📚 Borrowing</button>
      <button class="reason-chip" onclick="qrAutoLogin('Studying')">✏️ Studying</button>
    </div>
    <button class="btn btn-secondary btn-sm" style="width:100%;" onclick="closeReasonModal()">Cancel</button>
  </div>
</div>

<!-- WELCOME SCREEN -->
<div id="welcomeScreen" style="display:none; min-height:100vh; align-items:center; justify-content:center; background: linear-gradient(135deg, #1a6b3a 0%, #0f4424 100%);">
  <div class="welcome-card">
    <div class="checkmark">✅</div>
    <h2 id="welcomeName">Welcome!</h2>
    <div class="program" id="welcomeProgram"></div>
    <div class="reason-tag" id="welcomeReason"></div>
    <div class="welcome-tagline">Welcome to NEU Library!</div>
    <div class="countdown" id="welcomeCountdown">Redirecting in 5 seconds...</div>
  </div>
</div>

<!-- MAIN APP -->
<div id="appScreen" style="display:none; flex-direction:column; min-height:100vh;">
  <div class="topbar">
    <div class="topbar-logo">
      <img src="assets/library.jpg" alt="NEU" style="width:38px;height:38px;border-radius:50%;object-fit:cover;border:2px solid rgba(255,255,255,0.4);">
      NEU Library <span>Visitor Log</span> — Admin Dashboard
    </div>
    <span class="topbar-user" id="topbarUser"></span>
    <button class="topbar-btn excel" onclick="exportExcel()">📊 Export Excel</button>
    <button class="topbar-btn" onclick="exportPDF()">⬇ Export PDF</button>
    <button class="topbar-btn" onclick="logout()">Logout</button>
  </div>
  <div class="content">
    <div class="tabs">
      <button class="tab active" onclick="showTab('dashboard',this)">📊 Dashboard</button>
      <button class="tab" onclick="showTab('visitors',this)">📋 Visitor Log</button>
      <button class="tab" onclick="showTab('blocked',this)">🚫 Blocked Users</button>
      <button class="tab" onclick="showTab('records',this)">👥 Student Records</button>
    </div>
    <div id="tab-dashboard">
      <div class="stats-grid">
        <div class="stat-card" data-icon="👥"><div class="label">Today's Visitors</div><div class="value" id="statToday">0</div><div class="sub">Total logged in today</div></div>
        <div class="stat-card gold" data-icon="📅"><div class="label">This Week</div><div class="value" id="statWeek">0</div><div class="sub">Last 7 days</div></div>
        <div class="stat-card green" data-icon="📆"><div class="label">This Month</div><div class="value" id="statMonth">0</div><div class="sub" id="statMonthLabel"></div></div>
        <div class="stat-card" data-icon="📊" style="border-left-color:#6b7280;"><div class="label">Total All Time</div><div class="value" id="statTotal">0</div><div class="sub">Cumulative visitors</div></div>
      </div>
      <div class="panel" style="margin-bottom:24px;"><div class="panel-header"><h3>Visits by Reason</h3></div><div class="chart-bar-wrap" id="reasonChart"></div></div>
      <div class="panel"><div class="panel-header"><h3>Visits by Type</h3></div><div class="chart-bar-wrap" id="typeChart"></div></div>
    </div>
    <div id="tab-visitors" style="display:none;">
      <div class="panel">
        <div class="panel-header">
          <h3>Visitor Log</h3>
          <span class="live-badge"><span class="live-dot"></span> LIVE</span>
          <span id="lastUpdated" style="font-size:12px;color:var(--text-muted);"></span>
          <div class="search-box">🔍 <input type="text" id="searchBox" placeholder="Search name, program, reason..." oninput="renderTable()"></div>
          <div class="date-filter">
            <select id="filterPeriod" onchange="handleFilterPeriod()">
              <option value="all">All Time</option><option value="today">Today</option>
              <option value="week">This Week</option><option value="month">This Month</option><option value="range">Date Range</option>
            </select>
            <div id="rangeInputs" style="display:none;gap:6px;">
              <input type="date" id="dateFrom"><input type="date" id="dateTo">
              <button class="btn btn-sm btn-secondary" onclick="renderTable()">Apply</button>
            </div>
          </div>
        </div>
        <table><thead><tr><th>#</th><th>Name / ID</th><th>Type</th><th>Program</th><th>Reason</th><th>Date & Time</th><th>Action</th></tr></thead><tbody id="visitorTbody"></tbody></table>
        <div class="empty-state" id="emptyState" style="display:none;"><div class="icon">📭</div><div>No visitors found</div></div>
        <div class="pagination" id="paginationWrap"></div>
      </div>
    </div>
    <div id="tab-blocked" style="display:none;">
      <div class="panel">
        <div class="panel-header"><h3>Blocked Visitors</h3><button class="btn btn-sm btn-primary" onclick="openBlockModal()">+ Block Visitor</button></div>
        <table><thead><tr><th>ID / Email</th><th>Reason Blocked</th><th>Blocked On</th><th>Action</th></tr></thead><tbody id="blockedTbody"></tbody></table>
        <div class="empty-state" id="blockedEmpty" style="display:none;"><div class="icon">✅</div><div>No blocked users</div></div>
      </div>
    </div>
    <div id="tab-records" style="display:none;">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;">
        <h3 class="section-title" style="margin:0;">👥 Student / Staff Records</h3>
        <div style="display:flex;gap:8px;">
          <button class="btn btn-sm btn-primary" onclick="openAddRecordModal()">+ Add Record</button>
          <button class="btn btn-sm" style="background:var(--neu-gold);color:#fff;" onclick="openQrGeneratorModal()">🔲 Generate QR</button>
        </div>
      </div>
      <input type="text" id="recordSearch" placeholder="Search by name, RFID or email…" style="width:100%;margin-bottom:14px;padding:10px 14px;border:1.5px solid var(--border);border-radius:10px;font-family:'DM Sans',sans-serif;font-size:14px;" oninput="renderRecords()">
      <table><thead><tr><th>#</th><th>Name</th><th>Type</th><th>Program</th><th>RFID / Email</th><th>Action</th></tr></thead><tbody id="recordsTbody"></tbody></table>
      <div class="empty-state" id="recordsEmpty" style="display:none;"><div class="icon">👤</div><div>No records yet.</div></div>
    </div>
  </div>
</div>

<!-- ADD RECORD MODAL -->
<div class="modal-overlay" id="addRecordModal">
  <div class="modal">
    <h3>👤 Add Student / Staff Record</h3>
    <div class="form-group"><label>Full Name</label><input type="text" id="arName" placeholder="e.g. Maria Santos"></div>
    <div class="form-group"><label>RFID / Email</label><input type="text" id="arRfid" placeholder="e.g. 2024-10011 or name@neu.edu.ph"></div>
    <div class="form-group"><label>Type</label><select id="arType"><option value="Student">Student</option><option value="Faculty">Faculty</option><option value="Employee">Employee</option></select></div>
    <div class="form-group"><label>Program / Department</label><input type="text" id="arProgram" placeholder="e.g. BSIT, College of Nursing"></div>
    <div class="form-group"><label>Year Level / Title (optional)</label><input type="text" id="arYear" placeholder="e.g. 2nd Year or Professor"></div>
    <p class="error-msg" id="arError" style="display:none;">Please fill in all required fields.</p>
    <div class="modal-actions">
      <button class="btn btn-sm btn-secondary" onclick="closeAddRecordModal()">Cancel</button>
      <button class="btn btn-sm btn-primary" onclick="saveRecord()">Save Record</button>
    </div>
  </div>
</div>

<!-- BLOCK MODAL -->
<div class="modal-overlay" id="blockModal">
  <div class="modal">
    <h3>🚫 Block a Visitor</h3>
    <div class="form-group"><label>Visitor ID or Email</label><input type="text" id="blockId" placeholder="e.g. 2021-12345"></div>
    <div class="form-group"><label>Reason for Blocking</label><input type="text" id="blockReason" placeholder="e.g. Policy violation"></div>
    <div class="modal-actions">
      <button class="btn btn-sm btn-secondary" onclick="closeBlockModal()">Cancel</button>
      <button class="btn btn-sm btn-danger" onclick="blockVisitor()">Block Visitor</button>
    </div>
  </div>
</div>

<!-- QR GENERATOR MODAL -->
<div class="modal-overlay" id="qrGeneratorModal">
  <div class="modal" style="max-width:340px;text-align:center;">
    <h3>🔲 Student QR Code</h3>
    <p style="font-size:13px;color:var(--text-muted);margin-bottom:16px;">Select a student to generate their QR code for scanning</p>
    <div class="form-group">
      <label>Select Student / Staff</label>
      <select id="qrSelectRecord" onchange="renderQrCode()">
        <option value="">-- Select --</option>
      </select>
    </div>
    <div id="qrCodeDisplay" style="margin:16px auto;display:none;">
      <canvas id="qrOutputCanvas" style="border-radius:12px;border:3px solid var(--border);"></canvas>
      <p id="qrCodeLabel" style="font-size:12px;color:var(--text-muted);margin-top:8px;"></p>
      <button class="btn btn-sm btn-primary" style="margin-top:8px;" onclick="downloadQr()">⬇ Download QR</button>
    </div>
    <div class="modal-actions" style="justify-content:center;">
      <button class="btn btn-sm btn-secondary" onclick="closeQrGeneratorModal()">Close</button>
    </div>
  </div>
</div>

<!-- Load QR generator library -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script>
// ══════════════════════════════════════════════
// CONFIG
// ══════════════════════════════════════════════
const GOOGLE_CLIENT_ID = '709056082841-jm3vqkhk9oe05q3bvjbchm1rooe4qmg4.apps.googleusercontent.com';
const ALLOWED_DOMAIN   = 'neu.edu.ph';

let googleUser    = null;
let scannerActive = false;
let scannerStream = null;
let animFrame     = null;
let scanCooldown  = false;
let pendingQrData = null; // stores scanned RFID/email before reason is picked

// ══════════════════════════════════════════════
// METHOD TABS
// ══════════════════════════════════════════════
function switchMethod(method, el) {
  stopScanner();
  document.querySelectorAll('.method-tab').forEach(t => t.classList.remove('active'));
  el.classList.add('active');
  document.getElementById('qrSection').style.display     = method === 'qr'     ? 'block' : 'none';
  document.getElementById('googleSection').style.display = method === 'google' ? 'block' : 'none';
  document.getElementById('rfidSection').style.display   = method === 'rfid'   ? 'block' : 'none';
  document.getElementById('visitForm').style.display     = 'none';
  document.getElementById('adminSwitchLink').style.display = 'block';
}

// ══════════════════════════════════════════════
// QR SCANNER
// ══════════════════════════════════════════════
async function startScanner() {
  const video    = document.getElementById('qrVideo');
  const startBtn = document.getElementById('startScanBtn');
  const stopBtn  = document.getElementById('stopScanBtn');
  setQrStatus('Requesting camera access...', 'scanning');
  try {
    scannerStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' } });
    video.srcObject = scannerStream;
    await video.play();
    scannerActive = true;
    startBtn.style.display = 'none';
    stopBtn.style.display  = 'block';
    setQrStatus('📷 Scanning... Show your QR code', 'scanning');
    scanFrame();
  } catch (err) {
    setQrStatus('❌ Camera access denied. Please allow camera permission.', 'error');
  }
}

function stopScanner() {
  scannerActive = false;
  if (scannerStream) { scannerStream.getTracks().forEach(t => t.stop()); scannerStream = null; }
  if (animFrame)     { cancelAnimationFrame(animFrame); animFrame = null; }
  const video    = document.getElementById('qrVideo');
  const startBtn = document.getElementById('startScanBtn');
  const stopBtn  = document.getElementById('stopScanBtn');
  video.srcObject = null;
  startBtn.style.display = 'block';
  stopBtn.style.display  = 'none';
  setQrStatus('Press Start to activate camera', '');
}

function scanFrame() {
  if (!scannerActive) return;
  const video  = document.getElementById('qrVideo');
  const canvas = document.getElementById('qrCanvas');
  if (video.readyState === video.HAVE_ENOUGH_DATA) {
    canvas.width  = video.videoWidth;
    canvas.height = video.videoHeight;
    const ctx = canvas.getContext('2d');
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
    const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
    const code = jsQR(imageData.data, imageData.width, imageData.height, { inversionAttempts: 'dontInvert' });
    if (code && !scanCooldown) {
      handleQrResult(code.data);
    }
  }
  animFrame = requestAnimationFrame(scanFrame);
}

function handleQrResult(data) {
  scanCooldown = true;
  setTimeout(() => scanCooldown = false, 3000);

  const scanned = data.trim();

  // Check if blocked
  const isBlocked = blocked.find(b => b.id.toLowerCase() === scanned.toLowerCase());
  if (isBlocked) {
    setQrStatus('🚫 This visitor is blocked from using the library.', 'error');
    return;
  }

  // Look up in records
  const rec = customRecords[scanned.toLowerCase()];
  if (rec) {
    setQrStatus('✅ Recognized: ' + rec.name, 'success');
    pendingQrData = { rfid: scanned, name: rec.name, type: rec.type, program: rec.program };
    showReasonModal(rec.name, rec.type + ' — ' + rec.program);
  } else {
    // Not in records — still allow, just use scanned value as ID
    setQrStatus('✅ QR scanned: ' + scanned, 'success');
    pendingQrData = { rfid: scanned, name: scanned, type: 'Student', program: '' };
    showReasonModal(scanned, 'Please confirm your details after selecting reason');
  }
  stopScanner();
}

function setQrStatus(msg, type) {
  const el = document.getElementById('qrStatus');
  el.textContent  = msg;
  el.className    = 'qr-status ' + (type || '');
}

// ══════════════════════════════════════════════
// REASON MODAL (after QR scan)
// ══════════════════════════════════════════════
function showReasonModal(name, sub) {
  document.getElementById('reasonModalName').textContent = 'Hello, ' + name + '!';
  document.getElementById('reasonModalSub').textContent  = sub;
  document.getElementById('reasonModal').classList.add('open');
}
function closeReasonModal() {
  document.getElementById('reasonModal').classList.remove('open');
  pendingQrData = null;
  setQrStatus('Press Start to activate camera', '');
}

function qrAutoLogin(reason) {
  document.getElementById('reasonModal').classList.remove('open');
  if (!pendingQrData) return;

  const { rfid, name, type, program } = pendingQrData;

  // If program is unknown, show visit form to complete
  if (!program) {
    document.getElementById('loginId').value = rfid;
    document.getElementById('loginReason').value = reason;
    document.getElementById('googleSignedNote').textContent = '✅ QR scanned: ' + rfid + ' — please fill in your details';
    document.getElementById('googleSignedNote').style.display = 'block';
    showVisitFormDirectly();
    pendingQrData = null;
    return;
  }

  // Full auto-login
  const isBlocked = blocked.find(b => b.id.toLowerCase() === rfid.toLowerCase());
  if (isBlocked) { alert('You are not allowed to use the library.'); return; }

  const entry = { id: 'V' + Date.now(), name, rfid, type, program, reason, timestamp: new Date().toISOString() };
  visitors.unshift(entry);
  save();
  pendingQrData = null;
  showWelcome(name, program, reason, type);
}

// ══════════════════════════════════════════════
// QR CODE GENERATOR (admin side)
// ══════════════════════════════════════════════
function openQrGeneratorModal() {
  const sel = document.getElementById('qrSelectRecord');
  sel.innerHTML = '<option value="">-- Select --</option>';
  Object.entries(customRecords).forEach(([key, rec]) => {
    const opt = document.createElement('option');
    opt.value       = key;
    opt.textContent = rec.name + ' (' + key + ')';
    sel.appendChild(opt);
  });
  document.getElementById('qrCodeDisplay').style.display = 'none';
  document.getElementById('qrGeneratorModal').classList.add('open');
}
function closeQrGeneratorModal() { document.getElementById('qrGeneratorModal').classList.remove('open'); }

function renderQrCode() {
  const key = document.getElementById('qrSelectRecord').value;
  const display = document.getElementById('qrCodeDisplay');
  if (!key) { display.style.display = 'none'; return; }

  const rec    = customRecords[key];
  const canvas = document.getElementById('qrOutputCanvas');
  display.style.display = 'block';
  document.getElementById('qrCodeLabel').textContent = rec.name + ' — ' + key;

  // Clear and draw QR
  const ctx = canvas.getContext('2d');
  ctx.clearRect(0, 0, canvas.width, canvas.height);

  // Use QRCode library to generate into a temp div then copy to canvas
  const tempDiv = document.createElement('div');
  tempDiv.style.display = 'none';
  document.body.appendChild(tempDiv);

  new QRCode(tempDiv, {
    text: key,
    width: 200, height: 200,
    colorDark: '#1a6b3a', colorLight: '#ffffff',
    correctLevel: QRCode.CorrectLevel.H
  });

  setTimeout(() => {
    const img = tempDiv.querySelector('img') || tempDiv.querySelector('canvas');
    canvas.width = 200; canvas.height = 200;
    if (img.tagName === 'IMG') {
      const i = new Image();
      i.onload = () => ctx.drawImage(i, 0, 0, 200, 200);
      i.src = img.src;
    } else {
      ctx.drawImage(img, 0, 0, 200, 200);
    }
    document.body.removeChild(tempDiv);
  }, 100);
}

function downloadQr() {
  const key    = document.getElementById('qrSelectRecord').value;
  const rec    = customRecords[key];
  const canvas = document.getElementById('qrOutputCanvas');
  const link   = document.createElement('a');
  link.download = 'QR_' + rec.name.replace(/\s+/g, '_') + '.png';
  link.href     = canvas.toDataURL('image/png');
  link.click();
}

// ══════════════════════════════════════════════
// GOOGLE SIGN-IN
// ══════════════════════════════════════════════
function triggerGoogleSignIn() {
  if (typeof google === 'undefined' || !google.accounts) {
    showGoogleError('Google Sign-In is still loading. Please wait and try again.');
    return;
  }
  const tokenClient = google.accounts.oauth2.initTokenClient({
    client_id: GOOGLE_CLIENT_ID,
    scope: 'email profile',
    callback: function(tokenResponse) {
      if (tokenResponse.error) { showGoogleError('Sign-In failed: ' + tokenResponse.error); return; }
      fetch('https://www.googleapis.com/oauth2/v3/userinfo', {
        headers: { Authorization: 'Bearer ' + tokenResponse.access_token }
      })
      .then(r => r.json())
      .then(profile => {
        const email  = profile.email || '';
        const domain = email.split('@')[1] || '';
        if (domain.toLowerCase() !== ALLOWED_DOMAIN) {
          showGoogleError('⚠️ Only @' + ALLOWED_DOMAIN + ' accounts are allowed.'); return;
        }
        const isBlocked = blocked.find(b => b.id.toLowerCase() === email.toLowerCase());
        if (isBlocked) { showGoogleError('⚠️ You are not allowed to use the library.'); return; }
        googleUser = { name: profile.name || email, email, photo: profile.picture || '' };
        showVisitFormWithGoogle();
      })
      .catch(() => showGoogleError('Could not get account info. Please try again.'));
    }
  });
  tokenClient.requestAccessToken({ prompt: 'select_account' });
}

function showGoogleError(msg) {
  const el = document.getElementById('googleError');
  el.textContent = msg; el.style.display = 'block';
  setTimeout(() => el.style.display = 'none', 6000);
}

function showVisitFormWithGoogle() {
  document.getElementById('gUserPhoto').src         = googleUser.photo;
  document.getElementById('gUserName').textContent   = googleUser.name;
  document.getElementById('gUserEmail').textContent  = googleUser.email;
  document.getElementById('googleUserBar').style.display    = 'flex';
  document.getElementById('googleSignedNote').textContent   = '✅ Verified NEU account — complete your visit details';
  document.getElementById('googleSignedNote').style.display = 'block';
  showVisitFormDirectly();
}

function proceedWithRFID() {
  const val = document.getElementById('loginId').value.trim();
  const err = document.getElementById('rfidError');
  if (!val) { err.style.display='block'; err.textContent='Please enter your RFID or email.'; return; }
  if (val.includes('@')) {
    const domain = val.split('@')[1] || '';
    if (domain.toLowerCase() !== ALLOWED_DOMAIN) {
      err.style.display='block'; err.textContent='⚠️ Only @' + ALLOWED_DOMAIN + ' emails allowed.'; return;
    }
  }
  const isBlocked = blocked.find(b => b.id.toLowerCase() === val.toLowerCase());
  if (isBlocked) { err.style.display='block'; err.textContent='⚠️ You are not allowed to use the library.'; return; }
  err.style.display = 'none';
  showVisitFormDirectly();
}

function showVisitFormDirectly() {
  document.getElementById('qrSection').style.display     = 'none';
  document.getElementById('googleSection').style.display = 'none';
  document.getElementById('rfidSection').style.display   = 'none';
  document.querySelectorAll('.method-tab').forEach(t => t.classList.remove('active'));
  document.getElementById('visitForm').style.display     = 'block';
  document.getElementById('adminSwitchLink').style.display = 'none';
}

function cancelVisitForm() {
  googleUser = null; pendingQrData = null;
  document.getElementById('visitForm').style.display         = 'none';
  document.getElementById('googleUserBar').style.display     = 'none';
  document.getElementById('googleSignedNote').style.display  = 'none';
  document.getElementById('adminSwitchLink').style.display   = 'block';
  document.getElementById('loginId').value = '';
  document.getElementById('loginProgram').value = '';
  document.getElementById('loginError').style.display = 'none';
  // Go back to QR tab by default
  const tabs = document.querySelectorAll('.method-tab');
  tabs[0].classList.add('active');
  document.getElementById('qrSection').style.display     = 'block';
  document.getElementById('googleSection').style.display = 'none';
  document.getElementById('rfidSection').style.display   = 'none';
  setQrStatus('Press Start to activate camera', '');
}

// ══════════════════════════════════════════════
// SEED SAMPLE DATA
// ══════════════════════════════════════════════
(function seedSamples() {
  if (localStorage.getItem('neu_samples_seeded')) return;
  const existing = JSON.parse(localStorage.getItem('neu_visitors') || '[]');
  const samples = [
    { id:'SAMPLE_002', name:'Al Christian R. Limos',  rfid:'24-10845-559', type:'Student', program:'BSIT', reason:'Studying', timestamp: new Date('2026-03-02T13:00:00').toISOString() },
    { id:'SAMPLE_001', name:'Ishi Kent D.C Martinez', rfid:'24-10441-713', type:'Student', program:'BSIT', reason:'Studying', timestamp: new Date('2026-03-02T12:00:00').toISOString() }
  ];
  localStorage.setItem('neu_visitors', JSON.stringify([...samples, ...existing]));
  localStorage.setItem('neu_samples_seeded', '1');
})();

// ══════════════════════════════════════════════
// DATA
// ══════════════════════════════════════════════
let visitors      = JSON.parse(localStorage.getItem('neu_visitors')        || '[]');
let blocked       = JSON.parse(localStorage.getItem('neu_blocked')         || '[]');
let customRecords = JSON.parse(localStorage.getItem('neu_custom_records')  || '{}');
let currentPage   = 1;
const PAGE_SIZE   = 12;

if (visitors.length === 0) {
  visitors = [
    { id:'V1', name:'Al Christian R. Limos',  rfid:'24-10845-559', type:'Student', program:'BSIT', reason:'Studying', timestamp: new Date('2026-03-02T13:00:00').toISOString() },
    { id:'V2', name:'Ishi Kent D.C Martinez', rfid:'24-10441-713', type:'Student', program:'BSIT', reason:'Studying', timestamp: new Date('2026-03-02T12:00:00').toISOString() }
  ];
  localStorage.setItem('neu_visitors', JSON.stringify(visitors));
}

function save() {
  localStorage.setItem('neu_visitors', JSON.stringify(visitors));
  localStorage.setItem('neu_blocked',  JSON.stringify(blocked));
  syncFromStorage();
}
function saveCustomRecords() { localStorage.setItem('neu_custom_records', JSON.stringify(customRecords)); }
function syncFromStorage() {
  const sv = localStorage.getItem('neu_visitors');
  const sb = localStorage.getItem('neu_blocked');
  if (sv) visitors = JSON.parse(sv);
  if (sb) blocked  = JSON.parse(sb);
  const app = document.getElementById('appScreen');
  if (app && app.style.display === 'flex') {
    updateStats(); renderTable(); renderBlocked(); renderCharts();
    const el = document.getElementById('lastUpdated');
    if (el) el.textContent = 'Last updated: ' + new Date().toLocaleTimeString('en-PH',{hour:'2-digit',minute:'2-digit',second:'2-digit'});
  }
}

// ══════════════════════════════════════════════
// AUTH
// ══════════════════════════════════════════════
function showAdminLogin() {
  stopScanner();
  document.getElementById('visitorLoginForm').style.display = 'none';
  document.getElementById('adminLoginForm').style.display   = 'block';
}
function showVisitorLogin() {
  document.getElementById('adminLoginForm').style.display   = 'none';
  document.getElementById('visitorLoginForm').style.display = 'block';
  cancelVisitForm();
}

function visitorLogin() {
  const rfid        = googleUser ? googleUser.email : document.getElementById('loginId').value.trim();
  const displayName = googleUser ? googleUser.name  : rfid;
  const type        = document.getElementById('loginType').value;
  const program     = document.getElementById('loginProgram').value.trim();
  const reason      = document.getElementById('loginReason').value;
  const err         = document.getElementById('loginError');
  if (!rfid || !program) { err.style.display='block'; err.textContent='Please fill in all fields.'; return; }
  const isBlocked = blocked.find(b => b.id.toLowerCase() === rfid.toLowerCase());
  if (isBlocked) { err.style.display='block'; err.textContent='⚠️ You are not allowed to use the library.'; return; }
  err.style.display = 'none';
  const entry = { id:'V'+Date.now(), name:displayName, rfid, type, program, reason, timestamp: new Date().toISOString() };
  visitors.unshift(entry);
  save();
  googleUser = null;
  showWelcome(displayName, program, reason, type);
}

function showWelcome(name, program, reason, type) {
  stopScanner();
  document.getElementById('loginScreen').style.display = 'none';
  const ws = document.getElementById('welcomeScreen');
  ws.style.display = 'flex';
  document.getElementById('welcomeName').textContent    = 'Welcome, ' + name + '!';
  document.getElementById('welcomeProgram').textContent = type + ' — ' + program;
  document.getElementById('welcomeReason').textContent  = '📖 ' + reason;
  let secs = 5;
  const cd = document.getElementById('welcomeCountdown');
  const timer = setInterval(() => {
    secs--;
    cd.textContent = 'Redirecting in ' + secs + ' second' + (secs!==1?'s':'') + '...';
    if (secs <= 0) {
      clearInterval(timer);
      ws.style.display = 'none';
      document.getElementById('loginScreen').style.display = 'flex';
      cancelVisitForm();
    }
  }, 1000);
}

function adminLogin() {
  const raw = document.getElementById('adminUser').value.trim();
  const p   = document.getElementById('adminPass').value;
  const e   = document.getElementById('adminError');
  if (!raw || !p) { e.style.display='block'; e.textContent='Please fill in your email and password.'; setTimeout(()=>e.style.display='none',3000); return; }
  stopScanner();
  document.getElementById('loginScreen').style.display = 'none';
  document.getElementById('appScreen').style.display   = 'flex';
  document.getElementById('topbarUser').textContent    = raw.replace('@neu.admin.lib','') + '@neu.admin.lib';
  updateStats(); renderTable(); renderBlocked(); renderCharts();
}

function logout() {
  document.getElementById('appScreen').style.display   = 'none';
  document.getElementById('loginScreen').style.display = 'flex';
  showVisitorLogin();
}

// ══════════════════════════════════════════════
// RECORDS
// ══════════════════════════════════════════════
function renderRecords() {
  const q = (document.getElementById('recordSearch')?.value||'').toLowerCase();
  const entries = Object.entries(customRecords).filter(([key,rec]) =>
    !q || rec.name.toLowerCase().includes(q) || key.toLowerCase().includes(q) || rec.program.toLowerCase().includes(q)
  );
  const tbody = document.getElementById('recordsTbody');
  const empty = document.getElementById('recordsEmpty');
  if (entries.length===0){tbody.innerHTML='';empty.style.display='block';return;}
  empty.style.display='none';
  const typeColors={Student:'#e8f0fe|#1a56db',Faculty:'#fdf2f8|#9d174d',Employee:'#f0fdf4|#166534'};
  tbody.innerHTML=entries.map(([key,rec],i)=>{
    const [bg,col]=(typeColors[rec.type]||'#e6f4ec|#1a6b3a').split('|');
    const initials=rec.name.replace(/^(Dr\.|Prof\.)\s*/i,'').split(' ').map(n=>n[0]).join('').toUpperCase().slice(0,2);
    const photo=rec.photo?`<img src="${rec.photo}" style="width:32px;height:32px;border-radius:50%;object-fit:cover;border:2px solid #c8e6d0;vertical-align:middle;margin-right:8px;">`:`<div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#1a6b3a,#2d9e58);display:inline-flex;align-items:center;justify-content:center;color:#fff;font-size:11px;font-weight:700;vertical-align:middle;margin-right:8px;">${initials}</div>`;
    return `<tr><td>${i+1}</td><td style="display:flex;align-items:center;">${photo}<strong>${rec.name}</strong></td><td><span style="display:inline-block;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:600;background:${bg};color:${col};">${rec.type}</span></td><td>${rec.program}</td><td><code style="font-size:12px;background:#f0fdf4;padding:2px 6px;border-radius:6px;">${key}</code></td><td style="display:flex;gap:6px;"><button class="btn btn-sm btn-danger" onclick="deleteRecord('${key}')">Delete</button></td></tr>`;
  }).join('');
}
function openAddRecordModal(){['arName','arRfid','arProgram','arYear'].forEach(id=>document.getElementById(id).value='');document.getElementById('arType').value='Student';document.getElementById('arError').style.display='none';document.getElementById('addRecordModal').classList.add('open');}
function closeAddRecordModal(){document.getElementById('addRecordModal').classList.remove('open');}
function saveRecord(){
  const name=document.getElementById('arName').value.trim();
  const rfid=document.getElementById('arRfid').value.trim();
  const type=document.getElementById('arType').value;
  const prog=document.getElementById('arProgram').value.trim();
  const year=document.getElementById('arYear').value.trim();
  const err=document.getElementById('arError');
  if(!name||!rfid||!prog){err.style.display='block';return;}
  err.style.display='none';
  customRecords[rfid.toLowerCase()]={name,type,program:prog,photo:'',...(year?{year}:{})};
  saveCustomRecords();closeAddRecordModal();renderRecords();
}
function deleteRecord(key){delete customRecords[key];saveCustomRecords();renderRecords();}

// ══════════════════════════════════════════════
// TABS
// ══════════════════════════════════════════════
function showTab(name,el){
  ['dashboard','visitors','blocked','records'].forEach(t=>document.getElementById('tab-'+t).style.display=t===name?'block':'none');
  document.querySelectorAll('.tab').forEach(t=>t.classList.remove('active'));
  el.classList.add('active');
  if(name==='dashboard'){updateStats();renderCharts();}
  if(name==='visitors') renderTable();
  if(name==='blocked')  renderBlocked();
  if(name==='records')  renderRecords();
}

// ══════════════════════════════════════════════
// FILTERS / STATS / CHARTS / TABLE / BLOCKED
// ══════════════════════════════════════════════
function getFiltered(){
  const q=(document.getElementById('searchBox')?.value||'').toLowerCase();
  const period=document.getElementById('filterPeriod')?.value||'all';
  const now=new Date();
  return visitors.filter(v=>{
    const ts=new Date(v.timestamp);
    let inPeriod=true;
    if(period==='today') inPeriod=ts.toDateString()===now.toDateString();
    else if(period==='week') inPeriod=(now-ts)<=7*86400000;
    else if(period==='month') inPeriod=ts.getMonth()===now.getMonth()&&ts.getFullYear()===now.getFullYear();
    else if(period==='range'){
      const from=document.getElementById('dateFrom').value;
      const to=document.getElementById('dateTo').value;
      if(from) inPeriod=inPeriod&&ts>=new Date(from);
      if(to)   inPeriod=inPeriod&&ts<=new Date(to+'T23:59:59');
    }
    const matchQ=!q||v.name.toLowerCase().includes(q)||v.program.toLowerCase().includes(q)||v.reason.toLowerCase().includes(q)||v.rfid.toLowerCase().includes(q);
    return inPeriod&&matchQ;
  });
}
function handleFilterPeriod(){document.getElementById('rangeInputs').style.display=document.getElementById('filterPeriod').value==='range'?'flex':'none';renderTable();}
function updateStats(){
  const now=new Date();
  document.getElementById('statToday').textContent=visitors.filter(v=>new Date(v.timestamp).toDateString()===now.toDateString()).length;
  document.getElementById('statWeek').textContent=visitors.filter(v=>(now-new Date(v.timestamp))<=7*86400000).length;
  document.getElementById('statMonth').textContent=visitors.filter(v=>{const d=new Date(v.timestamp);return d.getMonth()===now.getMonth()&&d.getFullYear()===now.getFullYear();}).length;
  document.getElementById('statTotal').textContent=visitors.length;
  document.getElementById('statMonthLabel').textContent=now.toLocaleString('default',{month:'long',year:'numeric'});
}
function renderCharts(){
  const reasons={},types={};
  visitors.forEach(v=>{reasons[v.reason]=(reasons[v.reason]||0)+1;types[v.type]=(types[v.type]||0)+1;});
  const maxR=Math.max(...Object.values(reasons),1),maxT=Math.max(...Object.values(types),1);
  document.getElementById('reasonChart').innerHTML=Object.entries(reasons).sort((a,b)=>b[1]-a[1]).map(([k,v])=>`<div class="chart-row"><div class="chart-label">${k}</div><div class="chart-bar-bg"><div class="chart-bar" style="width:${v/maxR*100}%"></div></div><div class="chart-count">${v}</div></div>`).join('');
  document.getElementById('typeChart').innerHTML=Object.entries(types).sort((a,b)=>b[1]-a[1]).map(([k,v])=>`<div class="chart-row"><div class="chart-label">${k}</div><div class="chart-bar-bg"><div class="chart-bar" style="width:${v/maxT*100}%;background:linear-gradient(90deg,#c49010,#e8b820)"></div></div><div class="chart-count">${v}</div></div>`).join('');
}
function renderTable(){
  const filtered=getFiltered(),tbody=document.getElementById('visitorTbody'),empty=document.getElementById('emptyState');
  const total=filtered.length,pages=Math.ceil(total/PAGE_SIZE);
  if(currentPage>pages) currentPage=1;
  const slice=filtered.slice((currentPage-1)*PAGE_SIZE,currentPage*PAGE_SIZE);
  tbody.innerHTML=slice.map((v,i)=>{
    const dt=new Date(v.timestamp);
    const typeClass=v.type==='Student'?'badge-student':v.type==='Faculty'?'badge-faculty':'badge-employee';
    const prof=customRecords[(v.rfid||'').toLowerCase()]||{};
    const initials=(v.name||'?').split(' ').map(n=>n[0]).join('').toUpperCase().slice(0,2);
    const photo=prof.photo?`<img src="${prof.photo}" style="width:36px;height:36px;border-radius:50%;object-fit:cover;border:2px solid #c8e6d0;vertical-align:middle;margin-right:8px;">`:`<div style="width:36px;height:36px;border-radius:50%;background:linear-gradient(135deg,#1a6b3a,#2d9e58);display:inline-flex;align-items:center;justify-content:center;color:#fff;font-size:12px;font-weight:700;vertical-align:middle;margin-right:8px;">${initials}</div>`;
    return `<tr><td>${(currentPage-1)*PAGE_SIZE+i+1}</td><td style="display:flex;align-items:center;">${photo}<div><strong>${v.name}</strong><br><small style="color:var(--text-muted)">${v.rfid}</small></div></td><td><span class="badge-type ${typeClass}">${v.type}</span></td><td>${v.program}</td><td><span class="badge-reason">${v.reason}</span></td><td>${dt.toLocaleDateString('en-PH',{month:'short',day:'numeric',year:'numeric'})} ${dt.toLocaleTimeString('en-PH',{hour:'2-digit',minute:'2-digit'})}</td><td><button class="btn btn-sm btn-danger" onclick="quickBlock('${v.rfid}')">Block</button></td></tr>`;
  }).join('');
  empty.style.display=total===0?'block':'none';
  renderPagination(pages);
}
function renderPagination(pages){
  const wrap=document.getElementById('paginationWrap');
  if(pages<=1){wrap.innerHTML='';return;}
  let html=`<span style="color:var(--text-muted);margin-right:6px">Page ${currentPage} of ${pages}</span>`;
  for(let i=1;i<=pages;i++) html+=`<button class="page-btn ${i===currentPage?'active':''}" onclick="goPage(${i})">${i}</button>`;
  wrap.innerHTML=html;
}
function goPage(n){currentPage=n;renderTable();}
function openBlockModal(){document.getElementById('blockModal').classList.add('open');}
function closeBlockModal(){document.getElementById('blockModal').classList.remove('open');document.getElementById('blockId').value='';document.getElementById('blockReason').value='';}
function blockVisitor(){
  const id=document.getElementById('blockId').value.trim();
  const reason=document.getElementById('blockReason').value.trim();
  if(!id) return;
  blocked.push({id,reason:reason||'Unspecified',date:new Date().toISOString()});
  save();closeBlockModal();renderBlocked();
}
function quickBlock(rfid){
  if(!confirm('Block visitor '+rfid+'?')) return;
  blocked.push({id:rfid,reason:'Blocked by admin',date:new Date().toISOString()});
  save();renderBlocked();renderTable();
}
function unblock(id){blocked=blocked.filter(b=>b.id!==id);save();renderBlocked();}
function renderBlocked(){
  const tbody=document.getElementById('blockedTbody'),empty=document.getElementById('blockedEmpty');
  tbody.innerHTML=blocked.map(b=>{const dt=new Date(b.date);return `<tr><td><strong>${b.id}</strong></td><td>${b.reason}</td><td>${dt.toLocaleDateString('en-PH',{month:'short',day:'numeric',year:'numeric'})}</td><td><button class="btn btn-sm btn-success" onclick="unblock('${b.id}')">Unblock</button></td></tr>`;}).join('');
  empty.style.display=blocked.length===0?'block':'none';
}
window.addEventListener('storage',e=>{if(e.key==='neu_visitors'||e.key==='neu_blocked') syncFromStorage();});
setInterval(()=>{
  const stored=localStorage.getItem('neu_visitors');
  if(stored){
    const parsed=JSON.parse(stored);
    if(parsed.length!==visitors.length||(parsed[0]&&visitors[0]&&parsed[0].id!==visitors[0].id)){
      visitors=parsed;
      const bs=localStorage.getItem('neu_blocked');if(bs) blocked=JSON.parse(bs);
      const app=document.getElementById('appScreen');
      if(app&&app.style.display==='flex'){
        updateStats();renderTable();renderBlocked();renderCharts();
        const el=document.getElementById('lastUpdated');
        if(el) el.textContent='Last updated: '+new Date().toLocaleTimeString('en-PH',{hour:'2-digit',minute:'2-digit',second:'2-digit'});
        const firstRow=document.querySelector('#visitorTbody tr:first-child');
        if(firstRow) firstRow.classList.add('new-row-flash');
      }
    }
  }
},3000);

// ══════════════════════════════════════════════
// EXCEL EXPORT
// ══════════════════════════════════════════════
function exportExcel(){
  const filtered=getFiltered(),now=new Date();
  const logData=[['#','Name','RFID / Email','Type','Program','Reason','Date','Time']];
  filtered.forEach((v,i)=>{const dt=new Date(v.timestamp);logData.push([i+1,v.name,v.rfid,v.type,v.program,v.reason,dt.toLocaleDateString('en-PH',{month:'short',day:'numeric',year:'numeric'}),dt.toLocaleTimeString('en-PH',{hour:'2-digit',minute:'2-digit'})]);});
  const todayCount=visitors.filter(v=>new Date(v.timestamp).toDateString()===now.toDateString()).length;
  const weekCount=visitors.filter(v=>(now-new Date(v.timestamp))<=7*86400000).length;
  const monthCount=visitors.filter(v=>{const d=new Date(v.timestamp);return d.getMonth()===now.getMonth()&&d.getFullYear()===now.getFullYear();}).length;
  const summaryData=[['NEU Library Visitor Log — Summary'],['Generated',new Date().toLocaleString('en-PH')],[],['Period','Count'],['Today',todayCount],['This Week',weekCount],['This Month',monthCount],['All Time',visitors.length]];
  const blockedData=[['ID / Email','Reason Blocked','Blocked On']];
  blocked.forEach(b=>{const dt=new Date(b.date);blockedData.push([b.id,b.reason,dt.toLocaleDateString('en-PH',{month:'short',day:'numeric',year:'numeric'})]);});
  const wb=XLSX.utils.book_new();
  const wsLog=XLSX.utils.aoa_to_sheet(logData);wsLog['!cols']=[6,28,28,12,20,18,16,10].map(w=>({wch:w}));XLSX.utils.book_append_sheet(wb,wsLog,'Visitor Log');
  const wsSummary=XLSX.utils.aoa_to_sheet(summaryData);wsSummary['!cols']=[{wch:20},{wch:20}];XLSX.utils.book_append_sheet(wb,wsSummary,'Summary');
  const wsBlocked=XLSX.utils.aoa_to_sheet(blockedData);wsBlocked['!cols']=[{wch:28},{wch:30},{wch:16}];XLSX.utils.book_append_sheet(wb,wsBlocked,'Blocked Users');
  XLSX.writeFile(wb,'NEU_Library_VisitorLog_'+now.toISOString().slice(0,10)+'.xlsx');
}

// ══════════════════════════════════════════════
// PDF EXPORT
// ══════════════════════════════════════════════
function exportPDF(){
  const {jsPDF}=window.jspdf,doc=new jsPDF(),filtered=getFiltered();
  doc.setFontSize(16);doc.setTextColor(26,107,58);doc.text('NEU Library Visitor Log',14,18);
  doc.setFontSize(10);doc.setTextColor(100);doc.text('Generated: '+new Date().toLocaleString('en-PH')+' | Total: '+filtered.length+' visitors',14,26);
  doc.autoTable({startY:32,head:[['#','ID / RFID','Type','Program','Reason','Date & Time']],body:filtered.map((v,i)=>{const dt=new Date(v.timestamp);return[i+1,v.rfid,v.type,v.program,v.reason,dt.toLocaleString('en-PH')];}),styles:{fontSize:9},headStyles:{fillColor:[26,107,58]},alternateRowStyles:{fillColor:[230,244,236]}});
  doc.save('NEU_Library_VisitorLog.pdf');
}
</script>
</body>
</html>
