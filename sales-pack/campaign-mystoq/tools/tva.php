<?php
/**
 * tools/tva.php — DZ VAT (TVA) calculator.
 *
 * DZ VAT rates (per Direction Générale des Impôts):
 *   19%  — standard rate (most products + services)
 *   9%   — reduced (food staples, books, public transport, gas, electricity)
 *   0%   — exempt (exports, medical services, education)
 *
 * Operations:
 *   - HT → TTC: TTC = HT × (1 + rate)
 *   - TTC → HT: HT = TTC / (1 + rate)
 *   - VAT amount: TTC - HT
 */
declare(strict_types=1);
header_remove('X-Powered-By');

$amount = (float)str_replace([',', ' '], ['.', ''], (string)($_POST['amount'] ?? $_GET['amount'] ?? ''));
$amount = max(0, min(999999999.99, $amount));
$rate = (float)($_POST['rate'] ?? $_GET['rate'] ?? '19');
if (!in_array($rate, [0, 9, 19], true)) $rate = 19;
$direction = in_array(($_POST['direction'] ?? $_GET['direction'] ?? 'ht-to-ttc'), ['ht-to-ttc', 'ttc-to-ht'], true)
    ? ($_POST['direction'] ?? $_GET['direction'] ?? 'ht-to-ttc') : 'ht-to-ttc';

$result = null;
if ($amount > 0) {
    $r = $rate / 100;
    if ($direction === 'ht-to-ttc') {
        $ht = $amount;
        $ttc = $ht * (1 + $r);
        $vat = $ttc - $ht;
    } else {
        $ttc = $amount;
        $ht = $ttc / (1 + $r);
        $vat = $ttc - $ht;
    }
    $result = [
        'ht'  => round($ht, 2),
        'ttc' => round($ttc, 2),
        'vat' => round($vat, 2),
        'rate' => $rate,
        'direction' => $direction,
    ];
}

$rate_labels = [
    0 => ['name' => 'معفى من TVA', 'desc' => 'الصادرات · الخدمات الطبية · التعليم · الأدوية'],
    9 => ['name' => 'معدل مخفض', 'desc' => 'المواد الغذائية الأساسية · الكتب · النقل العمومي · الغاز والكهرباء'],
    19=> ['name' => 'المعدل الأساسي', 'desc' => 'معظم المنتجات والخدمات التجارية'],
];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#020617">
<title><?= $result ? 'TVA: ' . number_format($result['vat'], 2) . ' دج' : 'حاسبة TVA الجزائرية · 19% / 9% / 0%' ?></title>
<meta name="description" content="احسب TVA (الضريبة على القيمة المضافة) في الجزائر فوريا. 19% أو 9% أو معفى. تحويل HT ↔ TTC بضغطة واحدة.">
<meta name="keywords" content="TVA, ضريبة على القيمة المضافة, calcul TVA, VAT Algeria, حاسبة TVA, HT TTC">

<meta property="og:title" content="حاسبة TVA الجزائرية">
<meta property="og:description" content="19% / 9% / 0% — HT ↔ TTC">
<meta property="og:type" content="website">
<meta property="og:locale" content="ar_DZ">

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebApplication",
  "name": "حاسبة TVA الجزائرية",
  "url": "https://tkawen.online/tools/tva.php",
  "applicationCategory": "FinanceApplication",
  "operatingSystem": "Web",
  "offers": {"@type": "Offer", "price": "0", "priceCurrency": "DZD"}
}
</script>

<link rel="canonical" href="https://tkawen.online/tools/tva.php">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">

<style>
  :root{--bg:#fff;--bg-2:#f8fafc;--text:#0f172a;--muted:#64748b;--dim:#94a3b8;
    --line:#e2e8f0;--accent:#f59e0b;--accent-dark:#d97706;--green:#10b981;}
  *{box-sizing:border-box}body{margin:0;background:var(--bg-2);color:var(--text);
    font-family:'Cairo',-apple-system,'Segoe UI',sans-serif;line-height:1.6;direction:rtl;-webkit-font-smoothing:antialiased}
  .lat{font-family:'JetBrains Mono','SF Mono',monospace;direction:ltr;unicode-bidi:embed}

  header.top{background:var(--bg);border-bottom:1px solid var(--line);padding:14px 24px}
  .top-row{max-width:760px;margin:0 auto;display:flex;align-items:center;justify-content:space-between}
  .brand{font-weight:800;font-size:17px}.brand .x{color:var(--dim);margin:0 6px}
  .brand .by{color:var(--muted);font-weight:500;font-size:13px}
  .top-link{color:var(--muted);text-decoration:none;font-size:13px}

  .hero{max-width:680px;margin:0 auto;padding:32px 20px 8px;text-align:center}
  h1{margin:0 0 12px;font-size:28px;font-weight:800;letter-spacing:-.01em;line-height:1.3}
  .lede{color:var(--muted);font-size:15px;max-width:520px;margin:0 auto}
  .eyebrow{display:inline-block;background:rgba(245,158,11,.12);color:var(--accent-dark);
    padding:4px 12px;border-radius:999px;font-size:11px;font-weight:700;letter-spacing:.06em;margin-bottom:14px}

  main{max-width:680px;margin:0 auto;padding:24px 20px}
  .card{background:var(--bg);border:1px solid var(--line);border-radius:14px;
    padding:24px;box-shadow:0 1px 3px rgba(0,0,0,.03);margin-bottom:18px}

  /* Direction toggle */
  .dir-toggle{display:grid;grid-template-columns:1fr 1fr;gap:6px;padding:4px;background:var(--bg-2);border-radius:10px;margin-bottom:14px}
  .dir-toggle label{margin:0;cursor:pointer;text-align:center;padding:10px 12px;border-radius:7px;
    transition:all .15s;color:var(--muted);font-size:13px;font-weight:600}
  .dir-toggle input{display:none}
  .dir-toggle input:checked + span{background:var(--bg);color:var(--text);box-shadow:0 1px 2px rgba(0,0,0,.05)}
  .dir-toggle label span{display:block;padding:10px 12px;border-radius:7px}

  label{display:block;font-size:12px;color:var(--muted);margin-bottom:6px;font-weight:600}
  input[type=text]{width:100%;padding:13px 16px;font-size:20px;border:2px solid var(--line);
    border-radius:10px;background:#fff;color:var(--text);font-family:'JetBrains Mono',monospace;
    direction:ltr;text-align:left;outline:none;font-weight:700}
  input[type=text]:focus{border-color:var(--accent)}

  /* Rate picker */
  .rate-picker{display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;margin-top:14px}
  .rate-card{cursor:pointer;padding:14px 12px;border:2px solid var(--line);border-radius:10px;text-align:center;transition:all .15s}
  .rate-card input{position:absolute;opacity:0}
  .rate-card:has(input:checked){border-color:var(--accent);background:rgba(245,158,11,.08)}
  .rate-pct{font-size:24px;font-weight:900;color:var(--text);font-family:'JetBrains Mono',monospace}
  .rate-name{font-size:11px;color:var(--muted);margin-top:4px;font-weight:600}
  .rate-card:has(input:checked) .rate-pct{color:var(--accent-dark)}

  .calc-btn{width:100%;margin-top:18px;padding:14px;background:var(--accent);color:#fff;
    border:0;border-radius:10px;font-size:16px;font-weight:800;cursor:pointer;font-family:'Cairo',sans-serif;
    transition:background .15s}
  .calc-btn:hover{background:var(--accent-dark)}

  /* Result */
  .result-card{background:linear-gradient(135deg,#fffbeb,#fef3c7);border:1px solid #fbbf24;
    border-radius:14px;padding:24px;margin-bottom:18px}
  .res-grid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;margin-bottom:14px}
  .res-item{text-align:center;padding:14px 10px;background:rgba(255,255,255,.6);border-radius:10px}
  .res-label{font-size:11px;color:#78350f;font-weight:700;letter-spacing:.04em;margin-bottom:6px;text-transform:uppercase}
  .res-value{font-size:22px;font-weight:900;color:#451a03;font-family:'JetBrains Mono',monospace;line-height:1}
  .res-currency{font-size:12px;color:#78350f;margin-top:4px}
  .res-explain{font-size:13px;color:#78350f;text-align:center;padding-top:14px;border-top:1px dashed #fbbf24}
  .res-explain code{font-family:'JetBrains Mono',monospace;background:rgba(255,255,255,.6);padding:2px 6px;border-radius:4px;direction:ltr;display:inline-block;font-weight:700}

  /* Email capture */
  .capture{background:var(--bg);border:1px solid var(--line);border-radius:14px;padding:18px 20px;margin-bottom:18px}
  .capture-h{font-weight:800;font-size:15px;margin-bottom:6px}
  .capture-p{color:var(--muted);font-size:13px;margin-bottom:12px}
  .capture-form{display:flex;gap:8px}
  .capture-form input{flex:1;padding:10px 12px;font-size:14px;border:1px solid var(--line);border-radius:8px}
  .capture-form button{padding:10px 20px;background:var(--text);color:#fff;border:0;border-radius:8px;font-weight:700;cursor:pointer;font-family:'Cairo',sans-serif}
  .capture-msg{margin-top:10px;font-size:13px;display:none}
  .capture-msg.ok{display:block;color:var(--green)}

  /* Reference */
  .ref-card{background:var(--bg);border:1px dashed var(--line);border-radius:12px;padding:16px 20px;margin-bottom:18px}
  .ref-h{font-size:11px;color:var(--muted);font-weight:700;letter-spacing:.06em;margin-bottom:8px;text-transform:uppercase}
  .ref-row{padding:8px 0;border-bottom:1px solid var(--line);font-size:13px;display:flex;align-items:baseline;gap:10px}
  .ref-row:last-child{border-bottom:0}
  .ref-pct{font-family:'JetBrains Mono',monospace;font-weight:800;color:var(--accent-dark);min-width:38px}
  .ref-name{font-weight:700}
  .ref-desc{color:var(--muted);font-size:11px;margin-inline-start:auto}

  .promo-card{background:linear-gradient(135deg,#0f172a,#1e293b);color:#fff;border-radius:14px;padding:22px;text-align:center}
  .promo-card h3{margin:0 0 8px;font-size:17px}
  .promo-card p{color:#94a3b8;font-size:13px;margin:0 0 14px}
  .promo-card a{display:inline-block;background:#f59e0b;color:#fff;text-decoration:none;
    padding:10px 24px;border-radius:8px;font-weight:700;font-size:14px}

  footer{padding:24px 20px;text-align:center;color:var(--dim);font-size:12px}
  footer a{color:var(--muted);text-decoration:none}
  .trap{position:absolute;left:-9999px;visibility:hidden}
</style>
</head>
<body>

<header class="top">
  <div class="top-row">
    <div class="brand"><span class="lat">TKAWEN</span><span class="x">·</span><span class="by">أدوات مجانية</span></div>
    <a href="/tools/" class="top-link">← كل الأدوات</a>
  </div>
</header>

<section class="hero">
  <span class="eyebrow">100٪ دقيق · للسوق الجزائري</span>
  <h1>حاسبة TVA الجزائرية</h1>
  <p class="lede">احسب الضريبة على القيمة المضافة فوريا. 19% أو 9% أو معفى. تحويل من السعر قبل الضريبة إلى السعر شاملا (HT ↔ TTC).</p>
</section>

<main>
  <form method="post" class="card">
    <div class="dir-toggle">
      <label><input type="radio" name="direction" value="ht-to-ttc" <?= $direction === 'ht-to-ttc' ? 'checked' : '' ?>><span>من HT إلى TTC (إضافة TVA)</span></label>
      <label><input type="radio" name="direction" value="ttc-to-ht" <?= $direction === 'ttc-to-ht' ? 'checked' : '' ?>><span>من TTC إلى HT (استخراج TVA)</span></label>
    </div>

    <label for="amount">المبلغ (دج)</label>
    <input type="text" name="amount" id="amount" inputmode="decimal" autocomplete="off" required
           value="<?= $amount > 0 ? rtrim(rtrim(number_format($amount, 2, '.', ''), '0'), '.') : '' ?>"
           placeholder="0.00">

    <label style="margin-top:14px">معدل TVA</label>
    <div class="rate-picker">
      <?php foreach ([19, 9, 0] as $r): ?>
        <label class="rate-card">
          <input type="radio" name="rate" value="<?= $r ?>" <?= (int)$rate === $r ? 'checked' : '' ?>>
          <div class="rate-pct"><?= $r ?>٪</div>
          <div class="rate-name"><?= $rate_labels[$r]['name'] ?></div>
        </label>
      <?php endforeach; ?>
    </div>

    <button type="submit" class="calc-btn">احسب ←</button>
  </form>

  <?php if ($result): ?>
  <section class="result-card">
    <div class="res-grid">
      <div class="res-item">
        <div class="res-label">HT</div>
        <div class="res-value"><?= number_format($result['ht'], 2) ?></div>
        <div class="res-currency">دج · قبل TVA</div>
      </div>
      <div class="res-item">
        <div class="res-label">TVA <?= $result['rate'] ?>٪</div>
        <div class="res-value"><?= number_format($result['vat'], 2) ?></div>
        <div class="res-currency">دج · الضريبة</div>
      </div>
      <div class="res-item">
        <div class="res-label">TTC</div>
        <div class="res-value"><?= number_format($result['ttc'], 2) ?></div>
        <div class="res-currency">دج · شاملا</div>
      </div>
    </div>
    <div class="res-explain">
      <?php if ($result['direction'] === 'ht-to-ttc'): ?>
        إذا فاتورتك <code><?= number_format($result['ht'], 2) ?></code> دج قبل الضريبة، يدفع الزبون <code><?= number_format($result['ttc'], 2) ?></code> دج
      <?php else: ?>
        إذا الزبون يدفع <code><?= number_format($result['ttc'], 2) ?></code> دج، فأنت تحتفظ بـ <code><?= number_format($result['ht'], 2) ?></code> دج
      <?php endif; ?>
    </div>
  </section>

  <div class="capture">
    <div class="capture-h">📩 احصل على فاتورة جاهزة للطباعة بهذه الأرقام</div>
    <div class="capture-p">قالب فاتورة جزائرية رسمي بـ TVA + NIS + NIF + RC. PDF مجاني.</div>
    <form class="capture-form" id="cap-form">
      <input type="email" name="email" placeholder="you@example.com" required>
      <input type="text" name="trap" class="trap" tabindex="-1" autocomplete="off">
      <button type="submit">أرسل</button>
    </form>
    <div class="capture-msg" id="cap-msg"></div>
  </div>
  <?php endif; ?>

  <div class="ref-card">
    <div class="ref-h">مرجع معدلات TVA في الجزائر</div>
    <?php foreach ($rate_labels as $r => $info): ?>
      <div class="ref-row">
        <span class="ref-pct"><?= $r ?>٪</span>
        <span class="ref-name"><?= htmlspecialchars($info['name']) ?></span>
        <span class="ref-desc"><?= htmlspecialchars($info['desc']) ?></span>
      </div>
    <?php endforeach; ?>
  </div>

  <div class="promo-card">
    <h3>متجرك على MyStoq يحسب TVA تلقائيا</h3>
    <p>إعدادات TVA لكل ولاية، فواتير رسمية بـ NIS/NIF/RC، تقارير ضريبية شهرية. كله مدمج.</p>
    <a href="/try/?p=mystoq&utm_source=tools-tva">جرب MyStoq 90 يوم مجانا ←</a>
  </div>
</main>

<footer>
  أداة من <a href="/" class="lat">TKAWEN</a> · للنزاعات الضريبية تواصل مع DGI مباشرة
</footer>

<script async src="/intel/track.js" data-source="tools-tva"></script>
<script>
  const capForm = document.getElementById('cap-form');
  const capMsg = document.getElementById('cap-msg');
  if (capForm) capForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(capForm); capMsg.className = 'capture-msg';
    try {
      const r = await fetch('/intel/capture.php', {
        method:'POST', headers:{'Content-Type':'application/json'}, credentials:'include',
        body: JSON.stringify({
          email: fd.get('email'), source: 'tools-tva', page: '/tools/tva.php', kind: 'tool_result_request',
          fields: { tool: 'tva-calc', rate: '<?= $rate ?>', amount: '<?= $amount ?>' },
          trap: fd.get('trap'),
        }),
      });
      const d = await r.json();
      if (d.ok) { capMsg.className='capture-msg ok'; capMsg.textContent='✓ تم! الفاتورة في الطريق.';
        capForm.querySelector('button').disabled = true; capForm.querySelector('button').textContent='✓ تم'; }
    } catch (e) {}
  });
</script>
</body>
</html>
