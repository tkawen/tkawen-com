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
    --bg-2: #0f172a;
    --card: rgba(15, 23, 42, .65);
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
    min-height: 100vh; padding-bottom: 80px;
    font-size: 14px; line-height: 1.55;
    -webkit-font-smoothing: antialiased;
  }
  .lat { font-family: 'JetBrains Mono', 'SF Mono', 'Consolas', monospace; direction: ltr; unicode-bidi: embed }

  /* Top bar */
  .topbar {
    position: sticky; top: 0; z-index: 50;
    background: rgba(2, 6, 23, .85);
    backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px);
    border-bottom: 1px solid var(--border);
    padding: 12px 20px;
    display: flex; align-items: center; gap: 16px;
  }
  .brand {
    display: flex; align-items: center; gap: 10px;
    font-weight: 800; font-size: 15px; letter-spacing: -.01em;
  }
  .brand .dot {
    width: 8px; height: 8px; border-radius: 50%;
    background: var(--green);
    box-shadow: 0 0 10px var(--green);
    animation: pulse 2s infinite;
  }
  @keyframes pulse {
    0%, 100% { opacity: 1; box-shadow: 0 0 10px var(--green) }
    50% { opacity: .5; box-shadow: 0 0 4px var(--green) }
  }
  .brand-tag {
    font-size: 10px; padding: 2px 8px; border-radius: 4px;
    background: rgba(6, 182, 212, .12); color: var(--cyan);
    letter-spacing: .06em; font-weight: 700;
  }
  .topbar-right { margin-inline-start: auto; display: flex; align-items: center; gap: 12px; font-size: 12px; color: var(--muted) }
  .topbar-right .lat { color: var(--text) }
  #last-refresh { color: var(--dim) }

  /* Layout */
  .container { max-width: 1280px; margin: 0 auto; padding: 20px }
  .row {
    display: grid; gap: 14px;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    margin-bottom: 18px;
  }
  .card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 18px 20px;
    backdrop-filter: blur(10px);
    transition: border-color .2s;
  }
  .card:hover { border-color: var(--border-hi) }
  .card.lg { grid-column: 1 / -1 }
  .card-title {
    font-size: 11px; font-weight: 700; letter-spacing: .12em;
    text-transform: uppercase; color: var(--muted);
    margin-bottom: 12px; display: flex; align-items: center; gap: 6px;
  }
  .card-title .badge {
    margin-inline-start: auto; padding: 2px 8px; border-radius: 4px;
    font-size: 10px; background: rgba(255,255,255,.04);
  }

  /* Counter cards */
  .counter {
    position: relative; overflow: hidden;
  }
  .counter .label {
    font-size: 11px; font-weight: 600; letter-spacing: .08em;
    text-transform: uppercase; color: var(--muted);
    margin-bottom: 6px;
  }
  .counter .value {
    font-size: 32px; font-weight: 800; line-height: 1;
    letter-spacing: -.02em; color: var(--text);
  }
  .counter .delta {
    margin-top: 8px; font-size: 11px; color: var(--muted);
  }
  .counter .delta .up { color: var(--green) }
  .counter .delta .flat { color: var(--dim) }
  .counter.accent-cyan .value { color: var(--cyan) }
  .counter.accent-green .value { color: var(--green) }
  .counter.accent-amber .value { color: var(--amber) }
  .counter.accent-purple .value { color: var(--purple) }
  .counter.accent-red .value { color: var(--red) }

  /* Funnel */
  .funnel { display: flex; flex-direction: column; gap: 8px; margin-top: 8px }
  .funnel-row {
    display: flex; align-items: center; gap: 12px;
    padding: 12px 14px; border-radius: 8px;
    background: rgba(2, 6, 23, .4);
  }
  .funnel-label { flex: 1; font-size: 13px; color: var(--text) }
  .funnel-num { font-size: 18px; font-weight: 700; min-width: 60px; text-align: left }
  .funnel-bar {
    flex: 2; height: 6px; border-radius: 3px;
    background: rgba(255,255,255,.05); overflow: hidden;
  }
  .funnel-fill {
    height: 100%; border-radius: 3px;
    background: linear-gradient(90deg, var(--cyan), var(--purple));
    transition: width .5s ease;
  }

  /* Activity feed */
  .activity-list { display: flex; flex-direction: column; gap: 4px; max-height: 420px; overflow-y: auto }
  .activity-list::-webkit-scrollbar { width: 4px }
  .activity-list::-webkit-scrollbar-thumb { background: var(--border); border-radius: 2px }
  .act {
    display: flex; align-items: center; gap: 10px;
    padding: 8px 10px; border-radius: 6px;
    border-inline-start: 2px solid transparent;
    font-size: 12px; transition: background .15s;
  }
  .act:hover { background: rgba(255,255,255,.02) }
  .act.act-send  { border-color: var(--cyan) }
  .act.act-open  { border-color: var(--green) }
  .act.act-click { border-color: var(--amber) }
  .act.act-signup { border-color: var(--purple); background: rgba(168, 85, 247, .08) }
  .act.act-unsub { border-color: var(--red); opacity: .6 }
  .act-icon { font-size: 14px; width: 18px; text-align: center }
  .act-time { color: var(--dim); font-size: 10px; min-width: 50px }
  .act-who { color: var(--text); font-weight: 600; flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap }
  .act-meta { color: var(--muted); font-size: 11px }

  /* Variant table */
  .vtbl { width: 100%; border-collapse: collapse; font-size: 13px }
  .vtbl th, .vtbl td {
    padding: 8px 10px; text-align: right;
    border-bottom: 1px solid var(--border);
  }
  .vtbl th { color: var(--muted); font-weight: 600; font-size: 11px; letter-spacing: .04em }
  .vtbl tr:hover td { background: rgba(255,255,255,.02) }
  .vtbl td.num { font-variant-numeric: tabular-nums; font-weight: 700 }
  .pill {
    display: inline-block; padding: 2px 8px; border-radius: 4px;
    font-size: 11px; font-weight: 700;
    background: rgba(6, 182, 212, .12); color: var(--cyan);
  }

  /* Hot leads */
  .hot-list { display: flex; flex-direction: column; gap: 8px }
  .hot {
    display: flex; align-items: center; gap: 12px;
    padding: 10px 12px; border-radius: 8px;
    background: rgba(2, 6, 23, .4);
    border: 1px solid var(--border);
  }
  .hot-rank {
    font-weight: 800; font-size: 14px; min-width: 22px; text-align: center;
    color: var(--amber);
  }
  .hot-info { flex: 1; min-width: 0 }
  .hot-email { font-size: 12px; color: var(--text); overflow: hidden; text-overflow: ellipsis; white-space: nowrap }
  .hot-stats { font-size: 10px; color: var(--muted); margin-top: 2px }
  .hot-score {
    background: rgba(245, 158, 11, .15); color: var(--amber);
    padding: 4px 10px; border-radius: 6px;
    font-weight: 800; font-size: 13px;
    min-width: 50px; text-align: center;
  }

  /* AI suggestions */
  .ai-list { display: flex; flex-direction: column; gap: 10px }
  .ai {
    display: flex; gap: 12px; align-items: flex-start;
    padding: 12px 14px; border-radius: 10px;
    background: rgba(2, 6, 23, .4);
    border-inline-start: 3px solid var(--cyan);
  }
  .ai.ai-warn { border-color: var(--amber) }
  .ai.ai-good { border-color: var(--green) }
  .ai-icon { font-size: 18px; line-height: 1 }
  .ai-text { font-size: 13px; line-height: 1.55 }

  /* Bar chart for timeseries */
  .chart { display: flex; align-items: flex-end; gap: 3px; height: 100px; padding-top: 8px }
  .bar {
    flex: 1; min-width: 0;
    background: linear-gradient(to top, rgba(6,182,212,.3), rgba(6,182,212,.7));
    border-radius: 2px 2px 0 0;
    transition: opacity .15s;
  }
  .bar:hover { opacity: .8 }
  .bar.empty { background: rgba(255,255,255,.04) }
  .chart-x { display: flex; gap: 3px; margin-top: 6px; font-size: 9px; color: var(--dim) }
  .chart-x span { flex: 1; text-align: center }

  /* Empty state */
  .empty { padding: 40px 20px; text-align: center; color: var(--dim); font-size: 13px }

  /* Grid layouts */
  .grid-2 { display: grid; gap: 14px; grid-template-columns: 1fr; margin-bottom: 18px }
  @media (min-width: 980px) { .grid-2 { grid-template-columns: 1.4fr 1fr } }
  @media (min-width: 980px) { .grid-3 { display: grid; gap: 14px; grid-template-columns: 1fr 1fr 1fr; margin-bottom: 18px } }
  @media (max-width: 979px) { .grid-3 { display: grid; gap: 14px; margin-bottom: 18px } }

  /* Loading skeleton */
  .skel {
    background: linear-gradient(90deg, rgba(255,255,255,.04) 0%, rgba(255,255,255,.08) 50%, rgba(255,255,255,.04) 100%);
    background-size: 200% 100%;
    animation: skel 1.5s ease-in-out infinite;
    border-radius: 4px; height: 16px;
  }
  @keyframes skel { 0% { background-position: 200% 0 } 100% { background-position: -200% 0 } }

  @media (max-width: 600px) {
    .container { padding: 12px }
    .counter .value { font-size: 26px }
    .topbar { padding: 10px 14px }
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
    <span>تحديث: <span id="last-refresh" class="lat">—</span></span>
  </div>
</header>

<main class="container">

  <!-- Top counters -->
  <section class="row">
    <div class="card counter accent-cyan"><div class="label">إيميل مرسل</div><div class="value" id="c-sent">—</div><div class="delta"><span class="up" id="c-sent-1h">—</span> الساعة الأخيرة</div></div>
    <div class="card counter accent-green"><div class="label">فتح فريد</div><div class="value" id="c-opens">—</div><div class="delta"><span id="c-open-rate">—</span> معدل الفتح</div></div>
    <div class="card counter accent-amber"><div class="label">نقر فريد</div><div class="value" id="c-clicks">—</div><div class="delta"><span id="c-click-rate">—</span> معدل النقر</div></div>
    <div class="card counter accent-purple"><div class="label">تسجيل جديد</div><div class="value" id="c-signups">—</div><div class="delta">في MyStoq</div></div>
    <div class="card counter accent-red"><div class="label">إلغاء اشتراك</div><div class="value" id="c-unsub">—</div><div class="delta">من القائمة</div></div>
  </section>

  <!-- Funnel + AI -->
  <section class="grid-2">
    <div class="card">
      <div class="card-title">قمع التحويل <span class="badge" id="funnel-unique">—</span></div>
      <div class="funnel" id="funnel-list"></div>
    </div>
    <div class="card">
      <div class="card-title">توصيات ذكية</div>
      <div class="ai-list" id="ai-list"><div class="empty">جار التحليل…</div></div>
    </div>
  </section>

  <!-- 24h chart + variants + hot leads -->
  <section class="grid-3">
    <div class="card">
      <div class="card-title">آخر 24 ساعة · فتح + نقر</div>
      <div class="chart" id="ts-chart"></div>
      <div class="chart-x" id="ts-x"></div>
    </div>
    <div class="card">
      <div class="card-title">أداء النسخ</div>
      <table class="vtbl">
        <thead><tr><th>النسخة</th><th>إرسال</th><th>فتح %</th><th>نقر %</th></tr></thead>
        <tbody id="variant-rows"><tr><td colspan="4" class="empty">لا بيانات بعد</td></tr></tbody>
      </table>
    </div>
    <div class="card">
      <div class="card-title">أعلى المتفاعلين</div>
      <div class="hot-list" id="hot-list"><div class="empty">جار الحساب…</div></div>
    </div>
  </section>

  <!-- Live activity feed -->
  <section class="card lg">
    <div class="card-title">تدفق النشاط المباشر <span class="badge">آخر 50 حدث</span></div>
    <div class="activity-list" id="activity"><div class="empty">جار التحميل…</div></div>
  </section>

</main>

<script>
  const $ = (sel) => document.querySelector(sel);
  const fmt = (n) => new Intl.NumberFormat('ar-DZ').format(n);
  const ts2time = (iso) => {
    if (!iso) return '—';
    const d = new Date(iso);
    if (isNaN(d.getTime())) return '—';
    return d.toLocaleTimeString('ar-DZ', { hour: '2-digit', minute: '2-digit', hour12: false });
  };
  const ago = (iso) => {
    if (!iso) return '—';
    const sec = (Date.now() - new Date(iso).getTime()) / 1000;
    if (sec < 60) return 'الآن';
    if (sec < 3600) return Math.round(sec/60) + 'د';
    if (sec < 86400) return Math.round(sec/3600) + 'س';
    return Math.round(sec/86400) + 'ي';
  };
  const trim = (s, n) => (s || '').length > n ? (s || '').slice(0, n) + '…' : (s || '');

  const ICONS = {
    send: '✉', open: '◉', click: '→', signup: '★', unsub: '×',
  };

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
      const maxF = Math.max(...sum.funnel.map(s => s.count), 1);
      $('#funnel-list').innerHTML = sum.funnel.map(s => {
        const labels = {
          recipients_unique: 'مستقبلون',
          opened_unique: 'فتحوا',
          clicked_unique: 'نقروا',
          signups: 'سجلوا',
        };
        const pct = (s.count / maxF * 100).toFixed(1);
        return `<div class="funnel-row">
          <span class="funnel-label">${labels[s.stage] || s.stage}</span>
          <div class="funnel-bar"><div class="funnel-fill" style="width:${pct}%"></div></div>
          <span class="funnel-num lat">${fmt(s.count)}</span>
        </div>`;
      }).join('');
      $('#funnel-unique').textContent = 'فريد';

      // ─── Activity ───
      if (act.events && act.events.length) {
        $('#activity').innerHTML = act.events.map(e => {
          return `<div class="act act-${e.kind}">
            <span class="act-icon">${ICONS[e.kind] || '•'}</span>
            <span class="act-time lat">${ago(e.ts)}</span>
            <span class="act-who" title="${e.who||''}">${trim(e.who, 40)}</span>
            <span class="act-meta">${e.meta||''}</span>
          </div>`;
        }).join('');
      } else {
        $('#activity').innerHTML = '<div class="empty">لا أحداث بعد</div>';
      }

      // ─── Hot leads ───
      if (hot.hot && hot.hot.length) {
        $('#hot-list').innerHTML = hot.hot.map((h, i) => `<div class="hot">
          <span class="hot-rank">#${i+1}</span>
          <div class="hot-info">
            <div class="hot-email">${trim(h.email, 30)}</div>
            <div class="hot-stats lat">${h.opens} فتح · ${h.clicks} نقر · ${ago(h.last)}</div>
          </div>
          <span class="hot-score lat">${h.score}</span>
        </div>`).join('');
      } else {
        $('#hot-list').innerHTML = '<div class="empty">لا تفاعل بعد</div>';
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
        const maxT = Math.max(...ts.buckets.map(b => b.opens + b.clicks), 1);
        $('#ts-chart').innerHTML = ts.buckets.map(b => {
          const v = b.opens + b.clicks;
          const h = v > 0 ? Math.max(6, v / maxT * 100) : 0;
          return `<div class="bar${v === 0 ? ' empty' : ''}" style="height:${h}%" title="${v}"></div>`;
        }).join('');
        $('#ts-x').innerHTML = ts.buckets.map((b, i) =>
          i % 4 === 0 ? `<span>${-23 + i}h</span>` : '<span></span>'
        ).join('');
      }

      // ─── AI suggestions ───
      if (ai.suggestions && ai.suggestions.length) {
        $('#ai-list').innerHTML = ai.suggestions.map(s => `<div class="ai ai-${s.level || 'info'}">
          <span class="ai-icon">${s.icon}</span>
          <span class="ai-text">${s.text}</span>
        </div>`).join('');
      }

      $('#last-refresh').textContent = new Date().toLocaleTimeString('ar-DZ', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false });
    } catch (e) {
      console.error('refresh failed', e);
    }
  }

  refresh();
  setInterval(refresh, 30000);
  // Pause refresh when tab hidden
  document.addEventListener('visibilitychange', () => { if (!document.hidden) refresh(); });
</script>
</body>
</html>
