<?php require __DIR__ . '/_auth.php'; ?><!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#020617">
<meta name="robots" content="noindex,nofollow">
<title>TKAWEN Intel · القيادة</title>
<style>
  :root {
    --bg: #020617;
    --card: rgba(15, 23, 42, .7);
    --card-2: rgba(15, 23, 42, .45);
    --border: rgba(148, 163, 184, .12);
    --border-hi: rgba(6, 182, 212, .35);
    --text: #f8fafc;
    --muted: #94a3b8;
    --dim: #475569;
    --cyan: #06b6d4;
    --green: #10b981;
    --amber: #f59e0b;
    --red: #ef4444;
    --purple: #a855f7;
  }
  * { box-sizing: border-box }
  body {
    margin: 0;
    background:
      radial-gradient(ellipse 80% 60% at 20% 0%, rgba(6, 182, 212, .08), transparent 60%),
      radial-gradient(ellipse 60% 40% at 80% 100%, rgba(168, 85, 247, .06), transparent 60%),
      var(--bg);
    color: var(--text);
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Tahoma, system-ui, sans-serif;
    min-height: 100vh; padding-bottom: 60px;
    font-size: 14px; line-height: 1.55;
    -webkit-font-smoothing: antialiased;
  }
  .lat { font-family: 'JetBrains Mono', 'SF Mono', 'Consolas', monospace; direction: ltr; unicode-bidi: embed }

  /* Top bar */
  .topbar {
    position: sticky; top: 0; z-index: 50;
    background: rgba(2, 6, 23, .9);
    backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
    border-bottom: 1px solid var(--border);
    padding: 12px 20px;
    display: flex; align-items: center; gap: 16px; flex-wrap: wrap;
  }
  .brand { display: flex; align-items: center; gap: 10px; font-weight: 800; font-size: 15px; letter-spacing: -.01em }
  .brand .dot { width: 8px; height: 8px; border-radius: 50%; background: var(--green); box-shadow: 0 0 10px var(--green); animation: pulse 2s infinite }
  @keyframes pulse { 0%, 100% { opacity: 1 } 50% { opacity: .4 } }
  .brand-tag { font-size: 10px; padding: 2px 8px; border-radius: 4px; background: rgba(6, 182, 212, .12); color: var(--cyan); letter-spacing: .06em; font-weight: 700 }
  .topbar-right { margin-inline-start: auto; display: flex; align-items: center; gap: 12px; font-size: 12px; color: var(--muted) }
  #last-refresh { color: var(--dim); font-family: 'JetBrains Mono', monospace }
  .logout { color: var(--muted); text-decoration: none; padding: 4px 10px; border: 1px solid var(--border); border-radius: 6px; font-size: 11px }
  .logout:hover { color: var(--text); border-color: var(--border-hi) }

  /* Campaign status banner */
  .status-banner {
    margin: 16px 20px 0;
    background: linear-gradient(135deg, rgba(6, 182, 212, .12), rgba(168, 85, 247, .08));
    border: 1px solid var(--border-hi);
    border-radius: 14px;
    padding: 16px 20px;
    display: flex; align-items: center; gap: 16px; flex-wrap: wrap;
  }
  .status-banner .pulse {
    width: 10px; height: 10px; border-radius: 50%; background: var(--green);
    box-shadow: 0 0 0 0 rgba(16, 185, 129, .7); animation: ringPulse 2s infinite;
  }
  @keyframes ringPulse {
    0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, .7) }
    70% { box-shadow: 0 0 0 12px rgba(16, 185, 129, 0) }
    100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0) }
  }
  .sb-title { font-weight: 700; font-size: 14px; flex: 1; min-width: 200px }
  .sb-sub { color: var(--muted); font-size: 12px; margin-top: 2px; font-weight: 400 }
  .sb-progress { flex: 1; min-width: 200px; max-width: 360px }
  .sb-bar { height: 6px; border-radius: 3px; background: rgba(255,255,255,.06); overflow: hidden }
  .sb-fill { height: 100%; background: linear-gradient(90deg, var(--cyan), var(--green)); border-radius: 3px; transition: width .6s }
  .sb-stats { font-size: 11px; color: var(--muted); margin-top: 6px; display: flex; justify-content: space-between }
  .sb-stats .lat { color: var(--text) }

  /* Layout */
  .container { max-width: 1280px; margin: 0 auto; padding: 16px 20px }

  .row {
    display: grid; gap: 12px;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    margin-bottom: 16px;
  }
  .card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 16px 18px;
    backdrop-filter: blur(8px);
    transition: border-color .2s;
    display: flex; flex-direction: column;
  }
  .card:hover { border-color: var(--border-hi) }
  .card-title {
    font-size: 11px; font-weight: 700; letter-spacing: .1em;
    text-transform: uppercase; color: var(--muted);
    margin-bottom: 12px; display: flex; align-items: center; gap: 6px;
  }
  .card-title .badge {
    margin-inline-start: auto; padding: 2px 8px; border-radius: 4px;
    font-size: 10px; background: rgba(255,255,255,.04); color: var(--dim); font-weight: 500;
  }

  /* Counters */
  .counter .label { font-size: 10px; font-weight: 600; letter-spacing: .08em; text-transform: uppercase; color: var(--muted); margin-bottom: 6px }
  .counter .value { font-size: 28px; font-weight: 800; line-height: 1; letter-spacing: -.02em; color: var(--text); font-variant-numeric: tabular-nums }
  .counter .delta { margin-top: 6px; font-size: 11px; color: var(--muted); line-height: 1.4 }
  .counter .delta .up { color: var(--green); font-weight: 700 }
  .counter.accent-cyan .value { color: var(--cyan) }
  .counter.accent-green .value { color: var(--green) }
  .counter.accent-amber .value { color: var(--amber) }
  .counter.accent-purple .value { color: var(--purple) }
  .counter.accent-red .value { color: var(--red) }

  /* Two-column grid for body */
  .grid {
    display: grid; gap: 14px;
    grid-template-columns: 1fr; margin-bottom: 14px;
  }
  @media (min-width: 980px) {
    .grid-3-1 { grid-template-columns: 1.4fr 1fr 1fr }
    .grid-2 { grid-template-columns: 1fr 1fr }
    .grid-2-wide { grid-template-columns: 1.6fr 1fr }
  }

  /* Funnel */
  .funnel { display: flex; flex-direction: column; gap: 6px }
  .funnel-row {
    display: flex; align-items: center; gap: 12px;
    padding: 10px 12px; border-radius: 8px;
    background: var(--card-2);
  }
  .funnel-label { flex: 1; font-size: 13px; color: var(--text) }
  .funnel-num { font-size: 16px; font-weight: 700; min-width: 50px; text-align: left; font-variant-numeric: tabular-nums }
  .funnel-bar { flex: 2; height: 5px; border-radius: 3px; background: rgba(255,255,255,.04); overflow: hidden }
  .funnel-fill { height: 100%; border-radius: 3px; background: linear-gradient(90deg, var(--cyan), var(--purple)); transition: width .5s ease }
  .funnel-pct { font-size: 11px; color: var(--muted); min-width: 40px; text-align: left }

  /* Activity feed */
  .activity-list { display: flex; flex-direction: column; gap: 3px; max-height: 380px; overflow-y: auto; padding-inline-end: 4px }
  .activity-list::-webkit-scrollbar { width: 4px }
  .activity-list::-webkit-scrollbar-thumb { background: var(--border); border-radius: 2px }
  .act { display: flex; align-items: center; gap: 10px; padding: 7px 10px; border-radius: 6px; border-inline-start: 2px solid transparent; font-size: 12px; transition: background .15s }
  .act:hover { background: rgba(255,255,255,.02) }
  .act.act-send  { border-color: var(--cyan) }
  .act.act-open  { border-color: var(--green) }
  .act.act-click { border-color: var(--amber); background: rgba(245, 158, 11, .04) }
  .act.act-signup { border-color: var(--purple); background: rgba(168, 85, 247, .08) }
  .act.act-unsub { border-color: var(--red); opacity: .6 }
  .act-icon { font-size: 13px; width: 16px; text-align: center; color: var(--muted) }
  .act-time { color: var(--dim); font-size: 10px; min-width: 38px; font-family: 'JetBrains Mono', monospace }
  .act-who { color: var(--text); font-weight: 500; flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap }
  .act-meta { color: var(--muted); font-size: 11px; flex-shrink: 0 }

  /* Variant table */
  .vtbl { width: 100%; border-collapse: collapse; font-size: 13px }
  .vtbl th, .vtbl td { padding: 8px 8px; text-align: right; border-bottom: 1px solid var(--border) }
  .vtbl th { color: var(--muted); font-weight: 600; font-size: 10px; letter-spacing: .04em; text-transform: uppercase }
  .vtbl tr:last-child td { border-bottom: 0 }
  .vtbl tr:hover td { background: rgba(255,255,255,.02) }
  .vtbl td.num { font-variant-numeric: tabular-nums; font-weight: 700; font-family: 'JetBrains Mono', monospace }
  .pill { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 11px; font-weight: 700; background: rgba(6, 182, 212, .12); color: var(--cyan); font-family: 'JetBrains Mono', monospace }

  /* Hot leads */
  .hot-list { display: flex; flex-direction: column; gap: 6px }
  .hot { display: flex; align-items: center; gap: 10px; padding: 9px 12px; border-radius: 8px; background: var(--card-2); border: 1px solid var(--border) }
  .hot-rank { font-weight: 800; font-size: 12px; min-width: 20px; text-align: center; color: var(--amber); font-family: 'JetBrains Mono', monospace }
  .hot-info { flex: 1; min-width: 0 }
  .hot-email { font-size: 12px; color: var(--text); overflow: hidden; text-overflow: ellipsis; white-space: nowrap }
  .hot-stats { font-size: 10px; color: var(--muted); margin-top: 2px; font-family: 'JetBrains Mono', monospace }
  .hot-score { background: rgba(245, 158, 11, .15); color: var(--amber); padding: 3px 10px; border-radius: 6px; font-weight: 800; font-size: 12px; min-width: 44px; text-align: center; font-family: 'JetBrains Mono', monospace }

  /* AI suggestions */
  .ai-list { display: flex; flex-direction: column; gap: 8px }
  .ai { display: flex; gap: 10px; align-items: flex-start; padding: 10px 12px; border-radius: 8px; background: var(--card-2); border-inline-start: 3px solid var(--cyan) }
  .ai.ai-warn { border-color: var(--amber) }
  .ai.ai-good { border-color: var(--green) }
  .ai-icon { font-size: 16px; line-height: 1 }
  .ai-text { font-size: 12.5px; line-height: 1.6; color: var(--text) }

  /* 24h chart */
  .chart { display: flex; align-items: flex-end; gap: 2px; height: 80px; padding-top: 8px }
  .bar { flex: 1; min-width: 0; background: linear-gradient(to top, rgba(6,182,212,.25), rgba(6,182,212,.65)); border-radius: 2px 2px 0 0; min-height: 2px; position: relative }
  .bar.empty { background: rgba(255,255,255,.03) }
  .bar.has-click { background: linear-gradient(to top, rgba(245,158,11,.3), rgba(245,158,11,.8)) }
  .chart-x { display: flex; gap: 2px; margin-top: 6px; font-size: 9px; color: var(--dim); font-family: 'JetBrains Mono', monospace }
  .chart-x span { flex: 1; text-align: center }

  /* Empty state */
  .empty {
    padding: 32px 16px; text-align: center; color: var(--dim); font-size: 12px;
    display: flex; flex-direction: column; gap: 6px; align-items: center;
  }
  .empty-icon { font-size: 22px; opacity: .4 }
  .empty-hint { font-size: 11px; color: var(--dim); max-width: 200px; line-height: 1.6 }

  @media (max-width: 600px) {
    .container { padding: 12px }
    .counter .value { font-size: 24px }
    .topbar { padding: 10px 14px }
    .status-banner { margin: 12px 12px 0; padding: 14px 16px }
    .sb-title { font-size: 13px }
  }
</style>
</head>
<body>

<header class="topbar">
  <div class="brand">
    <span class="dot"></span>
    <span>TKAWEN Intel</span>
    <span class="brand-tag">LIVE</span>
  </div>
  <div class="topbar-right">
    <span id="last-refresh">—</span>
    <a href="logout.php" class="logout">خروج</a>
  </div>
</header>

<!-- Sticky campaign status banner -->
<div class="status-banner" id="status-banner">
  <div class="pulse"></div>
  <div style="flex:1;min-width:180px;">
    <div class="sb-title">حملة TKAWEN → MyStoq (الموجة 1)</div>
    <div class="sb-sub" id="sb-sub">جاري التحميل…</div>
  </div>
  <div class="sb-progress">
    <div class="sb-bar"><div class="sb-fill" id="sb-fill" style="width:0%"></div></div>
    <div class="sb-stats">
      <span><span id="sb-sent" class="lat">0</span> / <span class="lat">943</span> مرسل</span>
      <span><span id="sb-pct" class="lat">0٪</span> مكتمل</span>
    </div>
  </div>
</div>

<main class="container">

  <!-- Counters -->
  <section class="row">
    <div class="card counter accent-cyan"><div class="label">إيميل مرسل</div><div class="value" id="c-sent">—</div><div class="delta"><span class="up" id="c-sent-1h">+0</span> آخر ساعة</div></div>
    <div class="card counter accent-green"><div class="label">فتح فريد</div><div class="value" id="c-opens">—</div><div class="delta"><span id="c-open-rate">0٪</span> معدل الفتح</div></div>
    <div class="card counter accent-amber"><div class="label">نقر فريد</div><div class="value" id="c-clicks">—</div><div class="delta"><span id="c-click-rate">0٪</span> معدل النقر</div></div>
    <div class="card counter accent-purple"><div class="label">تسجيل MyStoq</div><div class="value" id="c-signups">—</div><div class="delta">من الحملة</div></div>
    <div class="card counter accent-red"><div class="label">إلغاء اشتراك</div><div class="value" id="c-unsub">—</div><div class="delta">من القائمة</div></div>
  </section>

  <!-- Funnel + AI suggestions -->
  <section class="grid grid-2">
    <div class="card">
      <div class="card-title">قمع التحويل <span class="badge">قيم فريدة</span></div>
      <div class="funnel" id="funnel-list"></div>
    </div>
    <div class="card">
      <div class="card-title">توصيات ذكية</div>
      <div class="ai-list" id="ai-list">
        <div class="empty"><div class="empty-icon">🤖</div><div>جار التحليل…</div></div>
      </div>
    </div>
  </section>

  <!-- 24h chart full width -->
  <section class="card" style="margin-bottom:14px;">
    <div class="card-title">آخر 24 ساعة · أزرق=فتح، ذهبي=نقر <span class="badge" id="ts-total">—</span></div>
    <div class="chart" id="ts-chart"></div>
    <div class="chart-x" id="ts-x"></div>
  </section>

  <!-- Hot leads + Variants -->
  <section class="grid grid-2-wide">
    <div class="card">
      <div class="card-title">أعلى المتفاعلين <span class="badge">أعلى 10</span></div>
      <div class="hot-list" id="hot-list">
        <div class="empty"><div class="empty-icon">📊</div><div>سيظهر هنا بعد أول فتح</div><div class="empty-hint">يرتب الأشخاص بحسب نشاطهم — كل فتح ١٠، كل نقر ٥٠ نقطة</div></div>
      </div>
    </div>
    <div class="card">
      <div class="card-title">أداء النسخ</div>
      <table class="vtbl">
        <thead><tr><th>النسخة</th><th>إرسال</th><th>فتح٪</th><th>نقر٪</th></tr></thead>
        <tbody id="variant-rows"><tr><td colspan="4"><div class="empty"><div class="empty-icon">📨</div><div>لا بيانات بعد</div></div></td></tr></tbody>
      </table>
    </div>
  </section>

  <!-- Live activity feed full width -->
  <section class="card">
    <div class="card-title">تدفق النشاط المباشر <span class="badge" id="act-count">—</span></div>
    <div class="activity-list" id="activity">
      <div class="empty"><div class="empty-icon">⏳</div><div>جار التحميل…</div></div>
    </div>
  </section>

</main>

<script>
  const $ = (sel) => document.querySelector(sel);
  const fmt = (n) => new Intl.NumberFormat('ar-DZ').format(n);
  const ago = (iso) => {
    if (!iso) return '—';
    const sec = (Date.now() - new Date(iso).getTime()) / 1000;
    if (sec < 60) return 'الآن';
    if (sec < 3600) return Math.round(sec/60) + 'د';
    if (sec < 86400) return Math.round(sec/3600) + 'س';
    return Math.round(sec/86400) + 'ي';
  };
  const trim = (s, n) => (s || '').length > n ? (s || '').slice(0, n) + '…' : (s || '');
  const ICONS = { send: '✉', open: '◉', click: '→', signup: '★', unsub: '×' };
  const WAVE_TOTAL = 943;

  async function refresh() {
    try {
      const [sum, act, hot, vrt, ts, ai] = await Promise.all([
        fetch('api.php?q=summary').then(r => r.json()),
        fetch('api.php?q=activity').then(r => r.json()),
        fetch('api.php?q=hot_leads').then(r => r.json()),
        fetch('api.php?q=per_variant').then(r => r.json()),
        fetch('api.php?q=timeseries').then(r => r.json()),
        fetch('api.php?q=ai_suggest').then(r => r.json()),
      ]);

      // ─── Top status banner ───
      const sent = sum.counters.sent_total;
      const pct = Math.round(sent / WAVE_TOTAL * 100);
      $('#sb-sent').textContent = fmt(sent);
      $('#sb-pct').textContent = pct + '٪';
      $('#sb-fill').style.width = pct + '%';
      const remaining = WAVE_TOTAL - sent;
      $('#sb-sub').textContent = remaining > 0
        ? `النسخة A · ${fmt(remaining)} متبق · ${sum.rates.open_rate}٪ معدل الفتح`
        : `الموجة 1 مكتملة · ${sum.rates.open_rate}٪ معدل الفتح`;

      // ─── Counters ───
      $('#c-sent').textContent = fmt(sum.counters.sent_total);
      $('#c-sent-1h').textContent = '+' + fmt(sum.counters.sent_1h);
      $('#c-opens').textContent = fmt(sum.counters.opens_total);
      $('#c-open-rate').textContent = sum.rates.open_rate + '٪';
      $('#c-clicks').textContent = fmt(sum.counters.clicks_total);
      $('#c-click-rate').textContent = sum.rates.click_rate + '٪';
      $('#c-signups').textContent = fmt(sum.counters.signups);
      $('#c-unsub').textContent = fmt(sum.counters.opt_outs);

      // ─── Funnel ───
      const top = sum.funnel[0]?.count || 1;
      $('#funnel-list').innerHTML = sum.funnel.map((s, i) => {
        const labels = { recipients_unique: 'مستقبلون', opened_unique: 'فتحوا', clicked_unique: 'نقروا', signups: 'سجلوا' };
        const pct = top > 0 ? (s.count / top * 100) : 0;
        const pctRel = i === 0 ? '—' : (top > 0 ? Math.round(s.count / top * 100) + '٪' : '—');
        return `<div class="funnel-row">
          <span class="funnel-label">${labels[s.stage] || s.stage}</span>
          <div class="funnel-bar"><div class="funnel-fill" style="width:${Math.max(0,pct)}%"></div></div>
          <span class="funnel-num lat">${fmt(s.count)}</span>
          <span class="funnel-pct lat">${pctRel}</span>
        </div>`;
      }).join('');

      // ─── Activity ───
      if (act.events && act.events.length) {
        $('#activity').innerHTML = act.events.map(e => `<div class="act act-${e.kind}">
          <span class="act-icon">${ICONS[e.kind] || '•'}</span>
          <span class="act-time">${ago(e.ts)}</span>
          <span class="act-who" title="${e.who||''}">${trim(e.who, 40)}</span>
          <span class="act-meta">${e.meta||''}</span>
        </div>`).join('');
        $('#act-count').textContent = act.events.length;
      } else {
        $('#activity').innerHTML = '<div class="empty"><div class="empty-icon">⏳</div><div>لا أحداث بعد</div></div>';
      }

      // ─── Hot leads ───
      const realHot = (hot.hot || []).filter(h => h.score > 0);
      if (realHot.length) {
        $('#hot-list').innerHTML = realHot.map((h, i) => `<div class="hot">
          <span class="hot-rank">#${i+1}</span>
          <div class="hot-info">
            <div class="hot-email">${trim(h.email, 32)}</div>
            <div class="hot-stats">${h.opens} فتح · ${h.clicks} نقر · ${ago(h.last)}</div>
          </div>
          <span class="hot-score">${h.score}</span>
        </div>`).join('');
      } else {
        $('#hot-list').innerHTML = '<div class="empty"><div class="empty-icon">📊</div><div>سيظهر هنا بعد أول فتح</div><div class="empty-hint">يرتب الأشخاص بحسب نشاطهم — كل فتح ١٠، كل نقر ٥٠ نقطة</div></div>';
      }

      // ─── Variants ───
      if (vrt.variants && vrt.variants.length) {
        $('#variant-rows').innerHTML = vrt.variants.map(v => `<tr>
          <td><span class="pill">${v.variant}</span></td>
          <td class="num">${fmt(v.sent)}</td>
          <td class="num">${v.open_rate}٪</td>
          <td class="num">${v.click_rate}٪</td>
        </tr>`).join('');
      }

      // ─── Timeseries ───
      if (ts.buckets && ts.buckets.length) {
        const maxT = Math.max(...ts.buckets.map(b => Math.max(b.opens, b.clicks)), 1);
        $('#ts-chart').innerHTML = ts.buckets.map(b => {
          const v = Math.max(b.opens, b.clicks);
          const h = v > 0 ? Math.max(4, v / maxT * 100) : 0;
          const cls = v === 0 ? 'empty' : (b.clicks > 0 ? 'has-click' : '');
          return `<div class="bar ${cls}" style="height:${h}%" title="${b.opens} فتح، ${b.clicks} نقر"></div>`;
        }).join('');
        $('#ts-x').innerHTML = ts.buckets.map((b, i) =>
          i === 0 ? '<span>-24h</span>' :
          i === 6 ? '<span>-18h</span>' :
          i === 12 ? '<span>-12h</span>' :
          i === 18 ? '<span>-6h</span>' :
          i === 23 ? '<span>الآن</span>' : '<span></span>'
        ).join('');
        const totalEvents = ts.buckets.reduce((a, b) => a + b.opens + b.clicks, 0);
        $('#ts-total').textContent = `${totalEvents} حدث`;
      }

      // ─── AI suggestions ───
      if (ai.suggestions && ai.suggestions.length) {
        $('#ai-list').innerHTML = ai.suggestions.map(s => `<div class="ai ai-${s.level || 'info'}">
          <span class="ai-icon">${s.icon}</span>
          <span class="ai-text">${s.text}</span>
        </div>`).join('');
      }

      $('#last-refresh').textContent = new Date().toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    } catch (e) {
      console.error('refresh failed', e);
    }
  }

  refresh();
  setInterval(refresh, 30000);
  document.addEventListener('visibilitychange', () => { if (!document.hidden) refresh(); });
</script>
</body>
</html>
