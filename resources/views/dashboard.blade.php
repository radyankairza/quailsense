<!doctype html>
<html lang="id" data-theme="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Kandang Unggas Monitoring</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;600&display=swap');

        /* ===== DARK THEME (default) ===== */
        :root,
        [data-theme="dark"] {
            --bg:        #0f0b08;
            --surface:   #1a1208;
            --surface-2: #221808;
            --surface-3: #2e2210;
            --text:      #d0edae;
            --muted:     #6b9648;
            --border:    rgba(75,130,40,0.16);
            --border-2:  rgba(75,130,40,0.32);
            --primary:   #62c030;
            --secondary: #a07038;
            --ok:        #4dba28;
            --warn:      #d4820a;
            --bad:       #c93a18;
            --grid-line: rgba(75,130,40,0.05);
            --header-bg: rgba(26,18,8,0.93);
        }

        /* ===== LIGHT THEME ===== */
        [data-theme="light"] {
            --bg:        #e8ede4;
            --surface:   #f0f4ec;
            --surface-2: #e2e9dc;
            --surface-3: #d4dece;
            --text:      #1e2c18;
            --muted:     #5a7a4a;
            --border:    rgba(60,100,45,0.16);
            --border-2:  rgba(60,100,45,0.30);
            --primary:   #3d8c22;
            --secondary: #7a5530;
            --ok:        #2e8012;
            --warn:      #b86e00;
            --bad:       #b02c10;
            --grid-line: rgba(60,100,45,0.07);
            --header-bg: rgba(232,237,228,0.95);
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            color: var(--text);
            min-height: 100vh;
            overflow-x: hidden;
            transition: background .3s, color .3s;
        }
        body::before {
            content: '';
            position: fixed; inset: 0;
            background-image:
                linear-gradient(var(--grid-line) 1px, transparent 1px),
                linear-gradient(90deg, var(--grid-line) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none;
            z-index: 0;
        }
        .wrap { position: relative; z-index: 1; max-width: 1280px; margin: 0 auto; padding: 18px 20px; }

        /* ── HEADER ── */
        .header {
            display: flex; justify-content: space-between; align-items: center;
            padding: 14px 20px;
            background: var(--header-bg);
            border: 1px solid var(--border-2);
            border-radius: 16px;
            backdrop-filter: blur(12px);
            margin-bottom: 18px;
        }
        .header-left { display: flex; align-items: center; gap: 14px; }
        .logo {
            width: 36px; height: 36px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px;
        }
        .header h1 { font-size: 17px; font-weight: 600; letter-spacing: -0.3px; }
        .header h1 span { color: var(--primary); }
        .header-right { display: flex; align-items: center; gap: 10px; font-size: 13px; }

        .live-pill {
            display: flex; align-items: center; gap: 7px;
            padding: 6px 14px; border-radius: 999px;
            background: rgba(77,168,50,0.12);
            border: 1px solid rgba(77,168,50,0.30);
            color: var(--ok); font-weight: 500;
        }
        .pulse-dot {
            width: 8px; height: 8px; border-radius: 50%;
            background: var(--ok);
            animation: ping 1.6s ease-in-out infinite;
        }
        @keyframes ping {
            0%   { box-shadow: 0 0 0 0   rgba(77,168,50,0.7); }
            70%  { box-shadow: 0 0 0 10px rgba(77,168,50,0); }
            100% { box-shadow: 0 0 0 0   rgba(77,168,50,0); }
        }
        .clock { font-family: 'JetBrains Mono', monospace; font-size: 14px; color: var(--muted); }

        /* ── THEME TOGGLE ── */
        .theme-toggle {
            width: 38px; height: 38px;
            border-radius: 10px;
            border: 1px solid var(--border-2);
            background: var(--surface-2);
            color: var(--text);
            font-size: 17px;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: background .15s, border-color .15s, transform .1s;
            flex-shrink: 0;
        }
        .theme-toggle:hover  { background: var(--surface-3); border-color: var(--primary); }
        .theme-toggle:active { transform: scale(0.93); }

        /* ── STATS ROW ── */
        .stats-row {
            display: grid; grid-template-columns: repeat(6, 1fr);
            gap: 10px; margin-bottom: 14px;
        }
        .stat-card {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: 12px; padding: 12px 14px;
            transition: border-color .2s, transform .15s;
        }
        .stat-card:hover { border-color: var(--border-2); transform: translateY(-2px); }
        .stat-label { font-size: 11px; color: var(--muted); margin-bottom: 4px; text-transform: uppercase; letter-spacing: .5px; }
        .stat-value { font-size: 22px; font-weight: 700; font-family: 'JetBrains Mono', monospace; }
        .stat-sub   { font-size: 11px; color: var(--muted); margin-top: 3px; }
        .color-temp { color: #e8920a; }
        .color-hum  { color: #5a9e2f; }
        .color-ok   { color: var(--ok); }
        .color-bad  { color: var(--bad); }

        /* ── MAIN GRID ── */
        .main-grid {
            display: grid; grid-template-columns: 1fr 1fr 360px;
            gap: 14px; margin-bottom: 14px;
        }
        .card {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: 16px; padding: 18px;
            transition: border-color .2s;
        }
        .card:hover { border-color: var(--border-2); }
        .card-title {
            font-size: 12px; font-weight: 600;
            text-transform: uppercase; letter-spacing: .7px;
            color: var(--muted); margin-bottom: 16px;
            display: flex; align-items: center; gap: 8px;
        }
        .card-title::before {
            content: ''; width: 3px; height: 14px;
            background: var(--primary); border-radius: 2px;
        }

        /* ── GAUGE ── */
        .gauge-canvas { display: block; width: 100%; max-width: 220px; margin: 0 auto; }
        .gauge-meta { text-align: center; margin-top: 6px; }
        .gauge-meta .g-val { font-size: 28px; font-weight: 700; font-family: 'JetBrains Mono', monospace; }
        .gauge-meta .g-label { font-size: 12px; color: var(--muted); margin-top: 2px; }
        .gauge-zones { display: flex; justify-content: center; gap: 8px; margin-top: 10px; font-size: 11px; flex-wrap: wrap; }
        .zone-dot { display: flex; align-items: center; gap: 4px; color: var(--muted); }
        .zone-dot span { width: 8px; height: 8px; border-radius: 50%; display: inline-block; }
        .separator { height: 1px; background: var(--border); margin: 12px 0; }

        /* ── CAMERA ── */
        .cam-frame {
            position: relative; background: #0e0a02;
            border-radius: 12px; overflow: hidden;
            aspect-ratio: 4/3; border: 1px solid var(--border);
        }
        .cam-frame video { width: 100%; height: 100%; object-fit: cover; display: block; }
        .cam-placeholder {
            position: absolute; inset: 0;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            gap: 10px; color: var(--muted); font-size: 13px;
        }
        .cam-placeholder svg { opacity: .4; }
        .scanline {
            position: absolute; inset: 0;
            background: repeating-linear-gradient(0deg, transparent, transparent 2px, rgba(0,0,0,0.08) 2px, rgba(0,0,0,0.08) 4px);
            pointer-events: none;
        }
        .scan-bar {
            position: absolute; left: 0; right: 0; height: 2px;
            background: linear-gradient(90deg, transparent, rgba(212,144,10,0.7), transparent);
            animation: scan 3s linear infinite; pointer-events: none;
        }
        @keyframes scan { 0% { top: 0%; } 100% { top: 100%; } }
        .corner { position: absolute; width: 16px; height: 16px; border-color: var(--primary); border-style: solid; }
        .corner.tl { top: 8px; left: 8px;     border-width: 2px 0 0 2px; }
        .corner.tr { top: 8px; right: 8px;    border-width: 2px 2px 0 0; }
        .corner.bl { bottom: 8px; left: 8px;  border-width: 0 0 2px 2px; }
        .corner.br { bottom: 8px; right: 8px; border-width: 0 2px 2px 0; }
        .cam-hud-top {
            position: absolute; top: 10px; left: 12px; right: 12px;
            display: flex; justify-content: space-between; align-items: center;
            font-size: 11px; font-family: 'JetBrains Mono', monospace; pointer-events: none;
        }
        .cam-hud-bottom {
            position: absolute; bottom: 10px; left: 12px; right: 12px;
            display: flex; justify-content: space-between;
            font-size: 11px; font-family: 'JetBrains Mono', monospace; pointer-events: none;
        }
        .cam-dot { width: 7px; height: 7px; border-radius: 50%; display: inline-block; margin-right: 5px; }
        .cam-dot.off { background: #475569; }
        .cam-dot.on  { background: #22c55e; box-shadow: 0 0 8px #22c55e; }
        .cam-btn-row { display: flex; gap: 8px; margin-top: 10px; }

        /* ── BUTTONS ── */
        .btn {
            flex: 1; padding: 9px 14px; border-radius: 10px;
            border: 1px solid var(--border-2);
            background: var(--surface-2); color: var(--text);
            font-size: 13px; font-family: 'Inter', sans-serif;
            cursor: pointer;
            transition: background .15s, border-color .15s, transform .1s;
            display: flex; align-items: center; justify-content: center; gap: 6px;
        }
        .btn:hover  { background: var(--surface-3); border-color: var(--primary); }
        .btn:active { transform: scale(0.97); }
        .btn-primary { background: rgba(212,144,10,0.15); border-color: rgba(212,144,10,0.40); color: var(--primary); }
        .btn-danger  { background: rgba(201,58,24,0.12); border-color: rgba(201,58,24,0.35); color: var(--bad); }

        /* ── CHART ── */
        .chart-section {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: 16px; padding: 18px; margin-bottom: 14px;
        }
        .chart-header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 14px; flex-wrap: wrap; gap: 8px;
        }
        .range-btns { display: flex; gap: 6px; }
        .range-btns button {
            padding: 6px 12px; border-radius: 8px;
            border: 1px solid var(--border); background: transparent;
            color: var(--muted); font-size: 12px; cursor: pointer;
            font-family: 'Inter', sans-serif; transition: all .15s;
        }
        .range-btns button:hover  { border-color: var(--border-2); color: var(--text); }
        .range-btns button.active {
            background: rgba(212,144,10,0.15);
            border-color: rgba(212,144,10,0.50);
            color: var(--primary);
        }

        /* ── ALERT LOG ── */
        .bottom-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .alert-list { list-style: none; display: flex; flex-direction: column; gap: 6px; max-height: 200px; overflow-y: auto; }
        .alert-list::-webkit-scrollbar { width: 4px; }
        .alert-list::-webkit-scrollbar-thumb { background: var(--border-2); border-radius: 4px; }
        .alert-item {
            display: flex; align-items: flex-start; gap: 10px;
            padding: 10px 12px; background: var(--surface-2);
            border-radius: 10px; border-left: 3px solid transparent;
            font-size: 13px; animation: fadeIn .3s ease;
        }
        @keyframes fadeIn { from { opacity:0; transform:translateY(6px); } to { opacity:1; transform:none; } }
        .alert-item.normal { border-left-color: var(--ok); }
        .alert-item.active { border-left-color: var(--bad); }
        .alert-item .ai-time { font-size: 11px; color: var(--muted); margin-top: 2px; font-family: 'JetBrains Mono', monospace; }
        .alert-item .ai-icon { font-size: 16px; flex-shrink: 0; margin-top: 1px; }
        .class-badge {
            display: inline-flex; align-items: center; gap: 6px;
            padding: 5px 12px; border-radius: 999px; font-size: 12px; font-weight: 600;
            margin-top: 4px;
        }
        .class-badge.normal { background: rgba(77,168,50,.15); border: 1px solid rgba(77,168,50,.35); color: var(--ok); }
        .class-badge.active { background: rgba(201,58,24,.15); border: 1px solid rgba(201,58,24,.35); color: var(--bad); }

        @media (max-width: 1024px) {
            .main-grid  { grid-template-columns: 1fr 1fr; }
            .stats-row  { grid-template-columns: repeat(3, 1fr); }
        }
        @media (max-width: 640px) {
            .main-grid   { grid-template-columns: 1fr; }
            .stats-row   { grid-template-columns: repeat(2, 1fr); }
            .bottom-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="wrap">

    <!-- HEADER -->
    <header class="header">
        <div class="header-left">
            <div class="logo">🐔</div>
            <div>
                <h1>Kandang <span>Unggas</span> Monitoring</h1>
                <div style="font-size:11px; color:var(--muted); margin-top:2px">Sistem Monitoring Peternakan IoT</div>
            </div>
        </div>
        <div class="header-right">
            <div class="live-pill"><span class="pulse-dot"></span> LIVE</div>
            <div class="clock" id="clock">--:--:--</div>
            <button class="theme-toggle" id="themeToggle" title="Toggle tema">🌙</button>
        </div>
    </header>

    <!-- STATS ROW -->
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-label">Suhu Sekarang</div>
            <div class="stat-value color-temp" id="statTempNow">-</div>
            <div class="stat-sub">°C</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Suhu Min / Max</div>
            <div class="stat-value" style="font-size:16px" id="statTempMinMax">- / -</div>
            <div class="stat-sub">12 jam terakhir</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Kelembapan</div>
            <div class="stat-value color-hum" id="statHumNow">-</div>
            <div class="stat-sub">%</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Hum Min / Max</div>
            <div class="stat-value" style="font-size:16px" id="statHumMinMax">- / -</div>
            <div class="stat-sub">12 jam terakhir</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Klasifikasi</div>
            <div class="stat-value" id="statClass" style="font-size:15px">-</div>
            <div class="stat-sub" id="statConf">confidence -</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Update Terakhir</div>
            <div class="stat-value" id="statLastUpdate" style="font-size:14px; color: var(--muted);">-</div>
            <div class="stat-sub">auto 10 detik</div>
        </div>
    </div>

    <!-- MAIN GRID -->
    <div class="main-grid">

        <!-- GAUGE SUHU -->
        <div class="card">
            <div class="card-title">Gauge Suhu</div>
            <canvas id="tempGaugeCanvas" class="gauge-canvas" height="130"></canvas>
            <div class="gauge-meta">
                <div class="g-val color-temp" id="tempGaugeVal">-</div>
                <div class="g-label">Temperature (°C)</div>
            </div>
            <div class="gauge-zones">
                <span class="zone-dot"><span style="background:#34d399"></span>Normal (&lt;29°C)</span>
                <span class="zone-dot"><span style="background:#fbbf24"></span>Hangat (29–32°C)</span>
                <span class="zone-dot"><span style="background:#f87171"></span>Panas (&gt;32°C)</span>
            </div>
            <div class="separator"></div>
            <div style="font-size:12px; color:var(--muted)">Rata-rata 12 jam: <span id="tempAvg" style="color:var(--text)">-</span> °C</div>
        </div>

        <!-- GAUGE KELEMBAPAN -->
        <div class="card">
            <div class="card-title">Gauge Kelembapan</div>
            <canvas id="humGaugeCanvas" class="gauge-canvas" height="130"></canvas>
            <div class="gauge-meta">
                <div class="g-val color-hum" id="humGaugeVal">-</div>
                <div class="g-label">Humidity (%)</div>
            </div>
            <div class="gauge-zones">
                <span class="zone-dot"><span style="background:#34d399"></span>Normal (40–70%)</span>
                <span class="zone-dot"><span style="background:#fbbf24"></span>Tinggi (70–80%)</span>
                <span class="zone-dot"><span style="background:#f87171"></span>Ekstrem (&gt;80%)</span>
            </div>
            <div class="separator"></div>
            <div style="font-size:12px; color:var(--muted)">Rata-rata 12 jam: <span id="humAvg" style="color:var(--text)">-</span> %</div>
        </div>

        <!-- CAMERA -->
        <div class="card">
            <div class="card-title">Camera Feed</div>
            <div class="cam-frame">
                <div class="cam-placeholder" id="camPlaceholder">
                    <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path d="M15 10l4.553-2.277A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M3 8a2 2 0 012-2h10a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2V8z"/>
                    </svg>
                    Kamera belum aktif
                </div>
                <video id="camVideo" autoplay muted playsinline style="display:none"></video>
                <div class="scanline"></div>
                <div class="scan-bar"></div>
                <span class="corner tl"></span><span class="corner tr"></span>
                <span class="corner bl"></span><span class="corner br"></span>
                <div class="cam-hud-top">
                    <span><span class="cam-dot off" id="camDot"></span><span id="camStatusText">OFFLINE</span></span>
                    <span id="camTimecode">00:00:00</span>
                </div>
                <div class="cam-hud-bottom">
                    <span>RPI-CAM-01</span>
                    <span id="camResolution">-</span>
                </div>
            </div>
            <div class="cam-btn-row">
                <button class="btn btn-primary" id="startCamBtn">&#9654; Start</button>
                <button class="btn btn-danger"  id="stopCamBtn">&#9632; Stop</button>
            </div>
            <div class="separator"></div>
            <div style="font-size:12px; color:var(--muted); margin-bottom:8px">Hasil Klasifikasi Terkini</div>
            <span class="class-badge normal" id="classBadge">&#9679; Normal</span>
        </div>
    </div>

    <!-- CHART -->
    <div class="chart-section">
        <div class="chart-header">
            <div class="card-title" style="margin-bottom:0">Grafik Suhu &amp; Kelembapan</div>
            <div class="range-btns">
                <button data-limit="20">20 Data</button>
                <button data-limit="50" class="active">50 Data</button>
                <button data-limit="100">100 Data</button>
                <button data-limit="144">All</button>
            </div>
        </div>
        <canvas id="sensorChart" height="80"></canvas>
    </div>

    <!-- BOTTOM -->
    <div class="bottom-grid">
        <div class="card">
            <div class="card-title">Alert Log</div>
            <ul class="alert-list" id="alertList">
                <li style="color:var(--muted); font-size:13px; padding:6px 0">Belum ada data log.</li>
            </ul>
        </div>
        <div class="card">
            <div class="card-title">Device Info</div>
            <div style="display:flex; flex-direction:column; gap:10px; font-size:13px">
                <div style="display:flex; justify-content:space-between">
                    <span style="color:var(--muted)">ESP32 Device ID</span>
                    <span style="font-family:'JetBrains Mono',monospace; color:var(--primary)">ESP32-001</span>
                </div>
                <div style="display:flex; justify-content:space-between">
                    <span style="color:var(--muted)">Raspberry Pi</span>
                    <span style="font-family:'JetBrains Mono',monospace; color:var(--secondary)">RPI-001</span>
                </div>
                <div style="display:flex; justify-content:space-between">
                    <span style="color:var(--muted)">Model TFLite</span>
                    <span>activity_classifier_v1</span>
                </div>
                <div style="display:flex; justify-content:space-between">
                    <span style="color:var(--muted)">Input Gambar</span>
                    <span>224 x 224 px</span>
                </div>
                <div style="display:flex; justify-content:space-between">
                    <span style="color:var(--muted)">Sensor</span>
                    <span>SHT31</span>
                </div>
                <div style="display:flex; justify-content:space-between">
                    <span style="color:var(--muted)">Protokol</span>
                    <span>HTTP / MQTT</span>
                </div>
                <div class="separator"></div>
                <div style="display:flex; justify-content:space-between; align-items:center">
                    <span style="color:var(--muted)">Status Koneksi</span>
                    <span class="class-badge normal" style="padding:3px 10px; font-size:11px">&#9679; Online</span>
                </div>
            </div>
        </div>
    </div>

</div><!-- /wrap -->

<script>
'use strict';

let currentLimit = 50;
let cameraStream = null;
let alertHistory = [];

/* ── CLOCK ── */
setInterval(() => {
    document.getElementById('clock').textContent = new Date().toLocaleTimeString('id-ID');
    document.getElementById('camTimecode').textContent = new Date().toLocaleTimeString('id-ID');
}, 1000);

/* ── THEME TOGGLE ── */
const htmlEl    = document.documentElement;
const themeBtn  = document.getElementById('themeToggle');
const THEME_KEY = 'iot-theme';

function applyTheme(theme) {
    htmlEl.setAttribute('data-theme', theme);
    themeBtn.textContent = theme === 'dark' ? '🌙' : '☀️';
    localStorage.setItem(THEME_KEY, theme);

    const isDark      = theme === 'dark';
    const tickColor   = isDark ? '#6b9648' : '#5a7a4a';
    const gridColor   = isDark ? 'rgba(75,130,40,0.09)' : 'rgba(60,100,45,0.10)';
    const legendColor = isDark ? '#8ecc5a' : '#3d8c22';
    const ttBg        = isDark ? '#1a1208' : '#f0f4ec';
    const ttTitle     = isDark ? '#d0edae' : '#1e2c18';
    const ttBody      = isDark ? '#6b9648' : '#5a7a4a';

    if (typeof sensorChart !== 'undefined') {
        sensorChart.options.plugins.legend.labels.color       = legendColor;
        sensorChart.options.plugins.tooltip.backgroundColor   = ttBg;
        sensorChart.options.plugins.tooltip.titleColor        = ttTitle;
        sensorChart.options.plugins.tooltip.bodyColor         = ttBody;
        sensorChart.options.scales.x.ticks.color              = tickColor;
        sensorChart.options.scales.x.grid.color               = gridColor;
        sensorChart.options.scales.y.ticks.color              = tickColor;
        sensorChart.options.scales.y.grid.color               = gridColor;
        sensorChart.update('none');
    }
}

// Load saved theme on start
(function () {
    const saved = localStorage.getItem(THEME_KEY) || 'dark';
    htmlEl.setAttribute('data-theme', saved);
    themeBtn.textContent = saved === 'dark' ? '🌙' : '☀️';
})();

themeBtn.addEventListener('click', () => {
    const current = htmlEl.getAttribute('data-theme');
    applyTheme(current === 'dark' ? 'light' : 'dark');
});

/* ── GAUGE ── */

// Per-gauge animation state
const gaugeState = {};

// Easing: smooth deceleration
function easeOutCubic(t) { return 1 - Math.pow(1 - t, 3); }

// Raw single-frame draw (called each animation frame)
function drawGaugeFrame(canvasId, value, min, max, zones) {
    const el  = document.getElementById(canvasId);
    if (!el) return;
    const dpr = window.devicePixelRatio || 1;
    const W   = el.offsetWidth;
    const H   = el.offsetHeight || 130;
    el.width  = W * dpr;
    el.height = H * dpr;
    const c   = el.getContext('2d');
    c.scale(dpr, dpr);

    const cx     = W / 2;
    const cy     = H - 10;
    const r      = Math.min(W * 0.42, H - 18);
    const start  = Math.PI;
    const pct    = Math.max(0, Math.min(1, (value - min) / (max - min)));
    const valEnd = start + Math.PI * pct;

    let color = zones[zones.length - 1].color;
    for (const z of zones) { if (pct <= z.max) { color = z.color; break; } }

    c.clearRect(0, 0, W, H);

    c.lineWidth = 14; c.strokeStyle = 'rgba(255,255,255,0.06)';
    c.beginPath(); c.arc(cx, cy, r, Math.PI, 2 * Math.PI); c.stroke();

    c.lineWidth = 14; c.lineCap = 'round';
    c.strokeStyle = color; c.shadowColor = color; c.shadowBlur = 18;
    c.beginPath(); c.arc(cx, cy, r, start, valEnd); c.stroke();
    c.shadowBlur = 0;

    const nx = cx + r * Math.cos(valEnd);
    const ny = cy + r * Math.sin(valEnd);
    c.beginPath(); c.arc(nx, ny, 5, 0, 2 * Math.PI);
    c.fillStyle = '#fff'; c.fill();

    const isDark     = htmlEl.getAttribute('data-theme') === 'dark';
    const labelColor = isDark ? 'rgba(107,150,72,0.85)' : 'rgba(60,100,45,0.75)';
    c.fillStyle  = labelColor; c.font = '500 11px Inter, Arial';
    c.textAlign  = 'left';  c.fillText(min, cx - r - 4, cy + 16);
    c.textAlign  = 'right'; c.fillText(max, cx + r + 4, cy + 16);
}

// Animate gauge smoothly from current displayed value to new target
function animateGaugeTo(canvasId, targetValue, min, max, zones, labelElId, unit, duration) {
    duration = duration || 700; // ms

    if (!gaugeState[canvasId]) {
        gaugeState[canvasId] = { current: min, raf: null };
    }
    const state = gaugeState[canvasId];

    // Cancel any running animation
    if (state.raf) { cancelAnimationFrame(state.raf); state.raf = null; }

    if (!Number.isFinite(targetValue)) {
        drawGaugeFrame(canvasId, state.current, min, max, zones);
        return;
    }

    const fromValue  = state.current;
    const startTime  = performance.now();
    const labelEl    = labelElId ? document.getElementById(labelElId) : null;

    function tick(now) {
        const elapsed  = now - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const eased    = easeOutCubic(progress);
        const current  = fromValue + (targetValue - fromValue) * eased;

        drawGaugeFrame(canvasId, current, min, max, zones);

        if (labelEl) {
            labelEl.textContent = `${current.toFixed(1)} ${unit}`;
        }

        if (progress < 1) {
            state.raf = requestAnimationFrame(tick);
        } else {
            state.current = targetValue;
            state.raf = null;
        }
    }

    state.raf = requestAnimationFrame(tick);
}

/* ── CHART ── */
const chartCtx    = document.getElementById('sensorChart').getContext('2d');
const sensorChart = new Chart(chartCtx, {
    type: 'line',
    data: {
        labels: [],
        datasets: [
            {
                label: 'Temperature (°C)', data: [],
                borderWidth: 2, tension: 0.38,
                borderColor: '#e8920a',
                backgroundColor: ctx => {
                    const g = ctx.chart.ctx.createLinearGradient(0, 0, 0, 300);
                    g.addColorStop(0, 'rgba(232,146,10,0.35)');
                    g.addColorStop(1, 'rgba(232,146,10,0)');
                    return g;
                },
                fill: true, pointRadius: 0, pointHoverRadius: 5,
            },
            {
                label: 'Humidity (%)', data: [],
                borderWidth: 2, tension: 0.38,
                borderColor: '#5a9e2f',
                backgroundColor: ctx => {
                    const g = ctx.chart.ctx.createLinearGradient(0, 0, 0, 300);
                    g.addColorStop(0, 'rgba(90,158,47,0.28)');
                    g.addColorStop(1, 'rgba(90,158,47,0)');
                    return g;
                },
                fill: true, pointRadius: 0, pointHoverRadius: 5,
            }
        ]
    },
    options: {
        responsive: true,
        interaction: { mode: 'index', intersect: false },
        plugins: {
            legend: { labels: { color: '#8ecc5a', font: { family: 'Inter' } } },
            tooltip: {
                backgroundColor: '#1a1208', borderColor: 'rgba(75,130,40,0.30)', borderWidth: 1,
                titleColor: '#d0edae', bodyColor: '#6b9648', padding: 10,
                callbacks: { label: ctx => `  ${ctx.dataset.label}: ${ctx.parsed.y.toFixed(1)}` }
            }
        },
        scales: {
            x: {
                ticks: { color: '#6b9648', maxTicksLimit: 10, font: { family: 'JetBrains Mono', size: 11 } },
                grid:  { color: 'rgba(75,130,40,0.09)' }
            },
            y: {
                ticks: { color: '#6b9648', font: { family: 'JetBrains Mono', size: 11 } },
                grid:  { color: 'rgba(75,130,40,0.09)' }
            }
        }
    }
});

/* ── ALERTS ── */
function pushAlert(result, confidence, time) {
    alertHistory.unshift({ result, confidence, time });
    if (alertHistory.length > 20) alertHistory.pop();
    const ul = document.getElementById('alertList');
    ul.innerHTML = alertHistory.map(a => `
        <li class="alert-item ${a.result === 'increased_activity' ? 'active' : 'normal'}">
            <span class="ai-icon">${a.result === 'increased_activity' ? '⚠️' : '✅'}</span>
            <div>
                <div>${a.result === 'increased_activity' ? 'Increased Activity Detected' : 'Status Normal'}
                    &nbsp;<span style="font-size:11px;opacity:.7">${(a.confidence * 100).toFixed(0)}%</span>
                </div>
                <div class="ai-time">${a.time}</div>
            </div>
        </li>`).join('');
}

/* ── CLASSIFICATION BADGE ── */
function updateClassBadge(result, confidence) {
    const badge  = document.getElementById('classBadge');
    const statEl = document.getElementById('statClass');
    const confEl = document.getElementById('statConf');
    if (result === 'increased_activity') {
        badge.className   = 'class-badge active';
        badge.textContent = '⚠ Increased Activity';
        statEl.textContent = 'Increased Activity';
        statEl.style.color = 'var(--bad)';
    } else {
        badge.className   = 'class-badge normal';
        badge.textContent = '● Normal';
        statEl.textContent = 'Normal';
        statEl.style.color = 'var(--ok)';
    }
    confEl.textContent = `confidence ${confidence ? (confidence * 100).toFixed(0) + '%' : '-'}`;
}

/* ── MAIN FETCH ── */
async function loadDashboard() {
    try {
        const [sensorList, sensorLatest, classLatest] = await Promise.all([
            fetch('/api/sensors').then(r => r.json()),
            fetch('/api/sensors/latest').then(r => r.json()),
            fetch('/api/classifications/latest').then(r => r.json()),
        ]);

        const allReadings = sensorList.data   || [];
        const latest      = sensorLatest?.data || {};
        const latestCl    = classLatest?.data  || {};
        const temp = Number(latest.temperature);
        const hum  = Number(latest.humidity);

        document.getElementById('statTempNow').textContent    = Number.isFinite(temp) ? temp.toFixed(1) : '-';
        document.getElementById('statHumNow').textContent     = Number.isFinite(hum)  ? hum.toFixed(1)  : '-';
        document.getElementById('statLastUpdate').textContent = new Date().toLocaleTimeString('id-ID');

        if (allReadings.length) {
            const temps = allReadings.map(r => r.temperature);
            const hums  = allReadings.map(r => r.humidity);
            document.getElementById('statTempMinMax').textContent = `${Math.min(...temps).toFixed(1)} / ${Math.max(...temps).toFixed(1)}`;
            document.getElementById('statHumMinMax').textContent  = `${Math.min(...hums).toFixed(1)} / ${Math.max(...hums).toFixed(1)}`;
            document.getElementById('tempAvg').textContent = (temps.reduce((a, b) => a + b, 0) / temps.length).toFixed(1);
            document.getElementById('humAvg').textContent  = (hums.reduce((a, b) => a + b, 0) / hums.length).toFixed(1);
        }

        animateGaugeTo('tempGaugeCanvas', temp, 20, 40, [
            { max: 0.45, color: '#34d399' },
            { max: 0.75, color: '#fbbf24' },
            { max: 1.00, color: '#f87171' },
        ], 'tempGaugeVal', '°C');
        animateGaugeTo('humGaugeCanvas', hum, 0, 100, [
            { max: 0.70, color: '#34d399' },
            { max: 0.85, color: '#fbbf24' },
            { max: 1.00, color: '#f87171' },
        ], 'humGaugeVal', '%');

        const sliced = allReadings.slice(0, currentLimit).reverse();
        sensorChart.data.labels           = sliced.map(x => new Date(x.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }));
        sensorChart.data.datasets[0].data = sliced.map(x => x.temperature);
        sensorChart.data.datasets[1].data = sliced.map(x => x.humidity);
        sensorChart.update('none');

        updateClassBadge(latestCl.result, latestCl.confidence);
        if (latestCl.result && latestCl.created_at) {
            const t = new Date(latestCl.created_at).toLocaleTimeString('id-ID');
            if (!alertHistory[0] || alertHistory[0].time !== t) {
                pushAlert(latestCl.result, latestCl.confidence, t);
            }
        }
    } catch (e) { console.warn('Fetch error', e); }
}

/* ── CAMERA ── */
async function startCamera() {
    try {
        cameraStream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'environment' }, audio: false });
        const video = document.getElementById('camVideo');
        video.srcObject = cameraStream;
        video.style.display = 'block';
        document.getElementById('camPlaceholder').style.display = 'none';
        document.getElementById('camDot').className = 'cam-dot on';
        document.getElementById('camStatusText').textContent = 'LIVE';
        video.addEventListener('loadedmetadata', () => {
            document.getElementById('camResolution').textContent = `${video.videoWidth}x${video.videoHeight}`;
        });
    } catch { document.getElementById('camStatusText').textContent = 'ACCESS DENIED'; }
}

function stopCamera() {
    cameraStream?.getTracks().forEach(t => t.stop());
    cameraStream = null;
    const video = document.getElementById('camVideo');
    video.srcObject = null; video.style.display = 'none';
    document.getElementById('camPlaceholder').style.display = 'flex';
    document.getElementById('camDot').className = 'cam-dot off';
    document.getElementById('camStatusText').textContent = 'OFFLINE';
    document.getElementById('camResolution').textContent = '-';
}

/* ── RANGE BUTTONS ── */
document.querySelectorAll('.range-btns button').forEach(btn => {
    btn.addEventListener('click', () => {
        document.querySelectorAll('.range-btns button').forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentLimit = Number(btn.dataset.limit);
        loadDashboard();
    });
});

document.getElementById('startCamBtn').addEventListener('click', startCamera);
document.getElementById('stopCamBtn').addEventListener('click', stopCamera);

// Apply saved theme colors to chart after chart is created
applyTheme(localStorage.getItem(THEME_KEY) || 'dark');

loadDashboard();
setInterval(loadDashboard, 10000);
</script>
</body>
</html>
