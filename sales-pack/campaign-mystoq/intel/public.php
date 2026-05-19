<?php
/**
 * intel/public.php — public-readable social proof page.
 *
 * No auth. Shows ONLY aggregate stats (no emails, no PII).
 * Embeddable as a "trust badge" iframe. SEO-friendly.
 */
declare(strict_types=1);
header_remove('X-Powered-By');

const C_DIR = __DIR__ . '/../mystoq-invite';
const I_DIR = __DIR__ . '/data';

function count_lines(string $path): int {
    if (!file_exists($path)) return 0;
    $n = 0;
    $fh = @fopen($path, 'r');
    if (!$fh) return 0;
    while (fgets($fh) !== false) $n++;
    fclose($fh);
    return $n;
}

function load_jsonl(string $path): array {
    if (!file_exists($path)) return [];
    $out = [];
    $fh = @fopen($path, 'r');
    if (!$fh) return [];
    while (($line = fgets($fh)) !== false) {
        $o = json_decode($line, true);
        if (is_array($o)) $out[] = $o;
    }
    fclose($fh);
    return $out;
}

// ─── Aggregate metrics (no PII) ───────────────────────────────
$total_users = 3827;  // tkawen.online registered members (from extract)

$sends = load_jsonl(C_DIR . '/send.jsonl');
$emails_sent = count(array_filter($sends, fn($s) => ($s['success'] ?? false) === true));

$events = load_jsonl(I_DIR . '/events.jsonl');
$total_pageviews = count(array_filter($events, fn($e) => ($e['kind'] ?? '') === 'pageview' || ($e['kind'] ?? '') === 'tool_view'));
$tool_uses = count(array_filter($events, fn($e) => str_starts_with(($e['kind'] ?? ''), 'tool_')));

$leads = load_jsonl(I_DIR . '/leads.jsonl');
$by_id = []; foreach ($leads as $l) $by_id[$l['lead_id'] ?? ''] = $l;
$unique_leads = count(array_filter(array_keys($by_id), fn($k) => $k !== ''));

// Sources breakdown
$sources_count = [];
foreach ($by_id as $l) {
    $s = $l['source'] ?? 'unknown';
    if (!$s) continue;
    $sources_count[$s] = ($sources_count[$s] ?? 0) + 1;
}
arsort($sources_count);

// Wave 1 campaign metrics
$opens = count_lines(C_DIR . '/opens.log');
$clicks = count_lines(C_DIR . '/visits.log');

$is_iframe = isset($_GET['embed']);
$theme = ($_GET['theme'] ?? 'light') === 'dark' ? 'dark' : 'light';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="<?= $theme === 'dark' ? '#020617' : '#ffffff' ?>">
<title>أرقام TKAWEN الحية · <?= number_format($total_users) ?> عضو</title>
<meta name="description" content="إحصاءات TKAWEN الحية: <?= number_format($total_users) ?> عضو، <?= number_format($emails_sent) ?> إيميل، <?= number_format($tool_uses) ?> استخدام للأدوات.">
<?php if (!$is_iframe): ?>
<meta property="og:title" content="أرقام TKAWEN — <?= number_format($total_users) ?> تاجر جزائري">
<meta property="og:type" content="website">
<meta property="og:locale" content="ar_DZ">
<link rel="canonical" href="https://tkawen.online/intel/public.php">
<?php endif; ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700;800;900&family=JetBrains+Mono:wght@500;700;800&display=swap" rel="stylesheet">

<style>
  :root.light {
    --bg:#ffffff; --bg-2:#f8fafc; --text:#0f172a; --muted:#475569; --dim:#94a3b8;
    --line:#e2e8f0; --cyan:#0891b2; --green:#059669; --purple:#7c3aed; --amber:#d97706;
  }
  :root.dark {
    --bg:#020617; --bg-2:#0f172a; --text:#f8fafc; --muted:#94a3b8; --dim:#475569;
    --line:rgba(148,163,184,.14); --cyan:#06b6d4; --green:#10b981; --purple:#a855f7; --amber:#f59e0b;
  }
  *{box-sizing:border-box}
  body{margin:0;background:var(--bg);color:var(--text);font-family:'Cairo',-apple-system,sans-serif;direction:rtl;line-height:1.6;-webkit-font-smoothing:antialiased}
  .lat{font-family:'JetBrains Mono','SF Mono',monospace;direction:ltr;unicode-bidi:embed}

  <?php if (!$is_iframe): ?>
  .hero{max-width:920px;margin:0 auto;padding:48px 20px 12px;text-align:center}
  .tag{display:inline-block;font-size:11px;font-weight:700;letter-spacing:.08em;
    color:var(--cyan);background:rgba(6,182,212,.12);padding:5px 12px;border-radius:999px;margin-bottom:14px;text-transform:uppercase}
  h1{margin:0 0 12px;font-size:32px;font-weight:900;letter-spacing:-.02em;line-height:1.25}
  .lede{color:var(--muted);font-size:15px;max-width:540px;margin:0 auto}
  <?php endif; ?>

  main{max-width:920px;margin:0 auto;padding:<?= $is_iframe ? '16px' : '24px 20px' ?>}

  .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:14px;margin-bottom:24px}
  .stat-card{background:var(--bg);border:1px solid var(--line);border-radius:12px;padding:22px 20px;
    text-align:center;transition:transform .2s}
  :root.dark .stat-card{background:var(--bg-2)}
  .stat-card:hover{transform:translateY(-2px)}
  .stat-val{font-family:'JetBrains Mono',monospace;font-size:32px;font-weight:900;letter-spacing:-.02em;line-height:1;direction:ltr}
  .stat-card.c-cyan .stat-val{color:var(--cyan)}
  .stat-card.c-green .stat-val{color:var(--green)}
  .stat-card.c-purple .stat-val{color:var(--purple)}
  .stat-card.c-amber .stat-val{color:var(--amber)}
  .stat-label{font-size:12px;color:var(--muted);margin-top:8px;font-weight:600}
  .stat-sub{font-size:10px;color:var(--dim);margin-top:4px;font-family:'JetBrains Mono',monospace}

  .live-dot{display:inline-block;width:8px;height:8px;border-radius:50%;background:var(--green);box-shadow:0 0 10px var(--green);animation:pulse 2s infinite;margin-inline-end:6px;vertical-align:middle}
  @keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}

  <?php if (!$is_iframe): ?>
  .section{background:var(--bg-2);border:1px solid var(--line);border-radius:14px;padding:24px;margin-bottom:18px}
  .section h2{margin:0 0 12px;font-size:14px;font-weight:700;letter-spacing:.04em;color:var(--muted);text-transform:uppercase}
  .source-row{display:flex;align-items:center;gap:14px;padding:8px 0}
  .source-name{flex:1;font-size:14px}
  .source-bar{flex:2;height:6px;border-radius:3px;background:rgba(255,255,255,.04);overflow:hidden}
  :root.light .source-bar{background:#e2e8f0}
  .source-fill{height:100%;background:linear-gradient(90deg,var(--cyan),var(--purple));border-radius:3px}
  .source-num{font-family:'JetBrains Mono',monospace;font-weight:800;color:var(--cyan);min-width:36px;text-align:left}

  .promo-card{background:linear-gradient(135deg,#1e40af,#1d4ed8);color:#fff;border-radius:16px;padding:28px;text-align:center;margin-top:30px}
  .promo-card h2{margin:0 0 8px;font-size:20px;color:#fff;letter-spacing:-.01em}
  .promo-card p{color:#bfdbfe;font-size:13px;margin:0 0 16px}
  .promo-card a{display:inline-block;background:#fff;color:#1d4ed8;padding:12px 28px;border-radius:10px;font-weight:800;text-decoration:none;font-size:14px}

  .embed-box{background:var(--bg-2);border:1px solid var(--line);border-radius:12px;padding:18px;margin-top:30px}
  .embed-box h3{margin:0 0 10px;font-size:13px;letter-spacing:.04em;color:var(--muted);font-weight:700;text-transform:uppercase}
  .embed-box code{display:block;background:var(--text);color:#e2e8f0;padding:14px 16px;border-radius:8px;
    font-family:'JetBrains Mono',monospace;font-size:12px;direction:ltr;overflow-x:auto;line-height:1.7}
  :root.dark .embed-box code{background:#000}

  footer{padding:24px 20px;text-align:center;color:var(--dim);font-size:11px;border-top:1px solid var(--line)}
  footer a{color:var(--muted);text-decoration:none}
  <?php endif; ?>

  @media (max-width:600px){
    h1{font-size:24px}
    .stat-val{font-size:26px}
    .grid{grid-template-columns:repeat(2,1fr)}
  }
</style>
</head>
<body class="<?= $theme ?>">
<script>document.documentElement.classList.add('<?= $theme ?>');</script>

<?php if (!$is_iframe): ?>
<section class="hero">
  <span class="tag"><span class="live-dot"></span>أرقام حية · تتحدث تلقائيا</span>
  <h1>TKAWEN بالأرقام</h1>
  <p class="lede">إحصاءات حقيقية لمنصة TKAWEN — الأعضاء، الأنشطة، الأدوات المستخدمة. تتحدث تلقائيا.</p>
</section>
<?php endif; ?>

<main>

  <div class="grid">
    <div class="stat-card c-cyan">
      <div class="stat-val"><?= number_format($total_users) ?></div>
      <div class="stat-label">عضو في عائلة TKAWEN</div>
      <div class="stat-sub">منذ 2021</div>
    </div>
    <div class="stat-card c-green">
      <div class="stat-val"><?= number_format($emails_sent) ?></div>
      <div class="stat-label">إيميل مرسل</div>
      <div class="stat-sub">campaign + transactional</div>
    </div>
    <div class="stat-card c-purple">
      <div class="stat-val"><?= number_format($tool_uses) ?></div>
      <div class="stat-label">استخدام للأدوات</div>
      <div class="stat-sub">Yalidine · IBAN · TVA · ولايات</div>
    </div>
    <div class="stat-card c-amber">
      <div class="stat-val"><?= number_format($unique_leads) ?></div>
      <div class="stat-label">عميل محتمل جديد</div>
      <div class="stat-sub">harvested cross-domain</div>
    </div>
  </div>

  <?php if (!$is_iframe): ?>
  <div class="section">
    <h2>📡 توزيع المصادر</h2>
    <?php $max_src = max(array_values($sources_count) ?: [1]);
    foreach (array_slice($sources_count, 0, 8, true) as $name => $n):
        $pct = $max_src > 0 ? ($n / $max_src * 100) : 0;
        $labels = [
            'try-portal' => '🚀 بوابة التسجيل',
            'tools-yalidine' => '📦 حاسبة Yalidine',
            'tools-iban' => '🏦 تحقق IBAN',
            'tools-wilaya' => '🗺️ ولايات',
            'tools-tva' => '🧾 حاسبة TVA',
            'mystoq-invite' => '✉️ حملة Email',
            'blog-shopify-vs-mystoq' => '📝 مدونة',
            'blog-edahabia-vs-cib' => '📝 مدونة',
            'embed-demo-light' => '🔌 widget',
            'unknown' => '❓ غير معروف',
        ];
    ?>
      <div class="source-row">
        <span class="source-name"><?= $labels[$name] ?? '· ' . htmlspecialchars($name) ?></span>
        <div class="source-bar"><div class="source-fill" style="width:<?= $pct ?>%"></div></div>
        <span class="source-num lat"><?= number_format($n) ?></span>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="section">
    <h2>📨 حملة TKAWEN → MyStoq · Wave 1</h2>
    <div class="grid">
      <div class="stat-card c-cyan">
        <div class="stat-val">943</div>
        <div class="stat-label">في القائمة</div>
        <div class="stat-sub">عملاء tkawen.online</div>
      </div>
      <div class="stat-card c-green">
        <div class="stat-val"><?= $emails_sent ?></div>
        <div class="stat-label">مرسل</div>
        <div class="stat-sub"><?= round($emails_sent / 943 * 100, 1) ?>٪ مكتمل</div>
      </div>
      <div class="stat-card c-purple">
        <div class="stat-val"><?= $opens ?></div>
        <div class="stat-label">فتح</div>
        <div class="stat-sub"><?= $emails_sent > 0 ? round($opens / $emails_sent * 100, 1) : 0 ?>٪</div>
      </div>
      <div class="stat-card c-amber">
        <div class="stat-val"><?= $clicks ?></div>
        <div class="stat-label">نقر</div>
        <div class="stat-sub"><?= $emails_sent > 0 ? round($clicks / $emails_sent * 100, 1) : 0 ?>٪</div>
      </div>
    </div>
  </div>

  <div class="promo-card">
    <h2>انضم إلى عائلة TKAWEN</h2>
    <p>3,827 تاجر جزائري بدأوا قبلك. الآن دورك.</p>
    <a href="/try/?utm_source=public-stats">جرب MyStoq 90 يوماً مجاناً ←</a>
  </div>

  <div class="embed-box">
    <h3>🔌 شارك هذه الأرقام على موقعك</h3>
    <code>&lt;iframe src="https://tkawen.online/intel/public.php?embed=1&amp;theme=light"
  width="100%" height="280" frameborder="0" style="border:0;border-radius:12px;"&gt;
&lt;/iframe&gt;</code>
  </div>
  <?php endif; ?>

</main>

<?php if (!$is_iframe): ?>
<footer>
  <a href="/">TKAWEN</a> · <a href="/blog/">مدونة</a> · <a href="/tools/">أدوات</a> ·
  أرقام محدثة كل بضع دقائق
</footer>
<?php endif; ?>

<script async src="/intel/track.js" data-source="public-stats"></script>
</body>
</html>
