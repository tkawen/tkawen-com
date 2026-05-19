<?php
/**
 * tools.tkawen.online/yalidine — Free Yalidine shipping cost calculator.
 *
 * The classic "free SEO tool" lead magnet. Ranks for:
 *   - "حاسبة Yalidine"
 *   - "Yalidine shipping cost"
 *   - "Yalidine prix livraison"
 *   - "كم سعر التوصيل Yalidine"
 *
 * Captures emails via the "أرسل لي النتيجة" button → fires to capture.php
 * with source=tools-yalidine.
 *
 * Pure PHP, no JS framework. Form submits POST to same page; result rendered
 * server-side. Hydrates well, mobile-fast, SEO-friendly.
 */
declare(strict_types=1);
header_remove('X-Powered-By');

$prices = require __DIR__ . '/yalidine-pricing.php';

// Sanitize inputs
$from = (int)($_POST['from'] ?? $_GET['from'] ?? 16);
$to   = (int)($_POST['to']   ?? $_GET['to']   ?? 0);
$weight = (float)($_POST['weight'] ?? $_GET['weight'] ?? 1);
$mode = in_array(($_POST['mode'] ?? $_GET['mode'] ?? 'home'), ['home', 'desk'], true) ? ($_POST['mode'] ?? $_GET['mode'] ?? 'home') : 'home';
$weight = max(0.1, min(30.0, $weight));

$result = null;
if (isset($prices[$to])) {
    $base = $prices[$to][$mode] ?? 0;
    // Weight tiers (Yalidine 2026):
    //   <= 5 kg: base price
    //   5-10 kg: +50%
    //   10-20 kg: +100%
    //   20-30 kg: +150% (max parcel size)
    if ($weight <= 5)          $surcharge = 0;
    elseif ($weight <= 10)     $surcharge = $base * 0.5;
    elseif ($weight <= 20)     $surcharge = $base * 1.0;
    else                        $surcharge = $base * 1.5;
    $total = (int)round($base + $surcharge);

    // CoD (cash on delivery) fee: 1% of declared value, min 50 DZD (we estimate
    // because we don't know the declared value yet — show as line item)
    $result = [
        'wilaya_id'   => $to,
        'wilaya_name' => $prices[$to]['name'],
        'zone'        => $prices[$to]['zone'],
        'mode'        => $mode,
        'mode_label'  => $mode === 'home' ? 'توصيل إلى المنزل' : 'توصيل إلى المكتب (Stop-Desk)',
        'weight'      => $weight,
        'base'        => $base,
        'surcharge'   => (int)$surcharge,
        'total'       => $total,
        'estimated_days' => $prices[$to]['zone'] === 'A' ? '24-48 ساعة'
                          : ($prices[$to]['zone'] === 'B' ? '2-4 أيام' : '4-7 أيام'),
    ];
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#020617">
<title><?= $result ? 'سعر التوصيل ' . $result['wilaya_name'] . ' = ' . $result['total'] . ' دج' : 'حاسبة Yalidine · أسعار التوصيل لكل ولايات الجزائر' ?></title>
<meta name="description" content="احسب سعر توصيل Yalidine لأي ولاية جزائرية فورا. 48 ولاية، توصيل منزلي أو Stop-Desk، أوزان حتى 30 كغ. مجانا.">
<meta name="keywords" content="Yalidine, تكلفة التوصيل, شركة توصيل الجزائر, حاسبة شحن, ياليدين, اسعار يالدين">

<!-- OpenGraph -->
<meta property="og:title" content="حاسبة Yalidine — أسعار التوصيل لكل ولايات الجزائر">
<meta property="og:description" content="احسب سعر التوصيل فوريا. 48 ولاية. مجانا.">
<meta property="og:type" content="website">
<meta property="og:locale" content="ar_DZ">
<meta property="og:url" content="https://tkawen.online/tools/yalidine.php">

<!-- JSON-LD: SoftwareApplication for tool snippet on SERPs -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebApplication",
  "name": "حاسبة Yalidine",
  "description": "احسب سعر التوصيل لـ 48 ولاية جزائرية فورا",
  "url": "https://tkawen.online/tools/yalidine.php",
  "applicationCategory": "BusinessApplication",
  "operatingSystem": "Web",
  "offers": {"@type": "Offer", "price": "0", "priceCurrency": "DZD"},
  "creator": {"@type": "Organization", "name": "TKAWEN", "url": "https://tkawen.com"}
}
</script>

<link rel="canonical" href="https://tkawen.online/tools/yalidine.php">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800&display=swap" rel="stylesheet">

<style>
  :root { --bg:#fff; --bg-2:#f8fafc; --text:#0f172a; --muted:#64748b; --dim:#94a3b8;
          --line:#e2e8f0; --accent:#10b981; --accent-dark:#059669; --amber:#f59e0b; }
  *{box-sizing:border-box} body{margin:0;background:var(--bg-2);color:var(--text);
    font-family:'Cairo',-apple-system,'Segoe UI',sans-serif;line-height:1.6;direction:rtl;-webkit-font-smoothing:antialiased}
  .lat{font-family:'JetBrains Mono','SF Mono',monospace;direction:ltr;unicode-bidi:embed}

  /* Top bar */
  header.top{background:var(--bg);border-bottom:1px solid var(--line);padding:14px 24px}
  .top-row{max-width:880px;margin:0 auto;display:flex;align-items:center;justify-content:space-between}
  .brand{font-weight:800;font-size:17px}.brand .x{color:var(--dim);margin:0 6px}
  .brand .by{color:var(--muted);font-weight:500;font-size:13px}
  .top-link{color:var(--muted);text-decoration:none;font-size:13px}

  /* Hero */
  .hero{max-width:760px;margin:0 auto;padding:32px 20px 8px;text-align:center}
  h1{margin:0 0 12px;font-size:30px;font-weight:800;letter-spacing:-.01em;line-height:1.3}
  .lede{color:var(--muted);font-size:15px;max-width:520px;margin:0 auto}
  .eyebrow{display:inline-block;background:rgba(16,185,129,.12);color:var(--accent-dark);
    padding:4px 12px;border-radius:999px;font-size:11px;font-weight:700;letter-spacing:.06em;margin-bottom:14px}

  /* Calculator */
  main{max-width:760px;margin:0 auto;padding:24px 20px}
  .card{background:var(--bg);border:1px solid var(--line);border-radius:14px;
    padding:24px;box-shadow:0 1px 3px rgba(0,0,0,.03);margin-bottom:20px}
  .form-grid{display:grid;gap:16px;grid-template-columns:1fr 1fr}
  @media(max-width:560px){.form-grid{grid-template-columns:1fr}}
  label{display:block;font-size:12px;color:var(--muted);margin-bottom:6px;font-weight:600}
  select, input{width:100%;padding:11px 12px;font-size:15px;border:1px solid var(--line);
    border-radius:8px;background:#fff;color:var(--text);font-family:'Cairo',sans-serif;outline:none;transition:border-color .15s}
  select:focus, input:focus{border-color:var(--accent)}

  .mode-toggle{display:grid;grid-template-columns:1fr 1fr;gap:6px;padding:4px;background:var(--bg-2);border-radius:10px;margin-bottom:8px}
  .mode-toggle label{margin:0;cursor:pointer;padding:8px 12px;text-align:center;border-radius:7px;
    transition:all .15s;color:var(--muted);font-size:13px;font-weight:600;background:transparent}
  .mode-toggle input{display:none}
  .mode-toggle input:checked + span{background:var(--bg);color:var(--text);box-shadow:0 1px 2px rgba(0,0,0,.05)}
  .mode-toggle label span{display:block;padding:8px 12px;border-radius:7px;transition:all .15s}

  .calc-btn{width:100%;margin-top:18px;padding:14px;background:var(--accent);color:#fff;
    border:0;border-radius:10px;font-size:16px;font-weight:800;cursor:pointer;font-family:'Cairo',sans-serif;
    transition:transform .15s,background .15s}
  .calc-btn:hover{background:var(--accent-dark);transform:translateY(-1px)}

  /* Result */
  .result{background:linear-gradient(135deg,#ecfdf5 0%,#d1fae5 100%);border:1px solid #6ee7b7;
    border-radius:14px;padding:24px;margin-bottom:20px}
  .result-h{font-size:12px;color:var(--accent-dark);font-weight:700;letter-spacing:.06em;margin-bottom:8px}
  .result-total{font-size:42px;font-weight:900;color:#064e3b;line-height:1;letter-spacing:-.02em;margin-bottom:6px}
  .result-total .lat{font-size:42px;font-weight:900;color:#064e3b}
  .result-total .currency{font-size:18px;color:#065f46;font-weight:700;margin-inline-start:6px}
  .result-sub{color:#065f46;font-size:14px}
  .result-rows{margin-top:16px;padding-top:16px;border-top:1px dashed #6ee7b7}
  .result-row{display:flex;justify-content:space-between;font-size:13px;color:#065f46;padding:3px 0}
  .result-row b{font-weight:700;font-family:'JetBrains Mono',monospace}

  /* Email capture box */
  .capture{background:var(--bg);border:1px solid var(--line);border-radius:14px;padding:20px 24px;margin-bottom:20px}
  .capture-h{font-weight:800;font-size:16px;margin-bottom:6px}
  .capture-p{color:var(--muted);font-size:13px;margin-bottom:14px}
  .capture-form{display:flex;gap:8px}
  .capture-form input{flex:1}
  .capture-form button{padding:11px 20px;background:var(--text);color:#fff;border:0;border-radius:8px;font-weight:700;cursor:pointer;white-space:nowrap;font-family:'Cairo',sans-serif}
  .capture-msg{margin-top:10px;font-size:13px;display:none}
  .capture-msg.ok{display:block;color:var(--accent-dark)}
  .capture-msg.err{display:block;color:#dc2626}

  /* Zone info */
  .zone-info{background:rgba(245,158,11,.08);border:1px solid rgba(245,158,11,.3);
    border-radius:10px;padding:14px 18px;font-size:13px;color:#78350f;margin-top:12px}

  /* Other tools / CTA */
  .promo-card{background:linear-gradient(135deg,#0f172a,#1e293b);color:#fff;border-radius:14px;padding:24px;text-align:center}
  .promo-card h3{margin:0 0 8px;font-size:18px}
  .promo-card p{color:#94a3b8;font-size:13px;margin:0 0 14px}
  .promo-card a{display:inline-block;background:#10b981;color:#fff;text-decoration:none;
    padding:10px 24px;border-radius:8px;font-weight:700;font-size:14px}

  /* Footer */
  footer{padding:24px 20px;text-align:center;color:var(--dim);font-size:12px}
  footer a{color:var(--muted);text-decoration:none}

  /* Trap */
  .trap{position:absolute;left:-9999px;visibility:hidden}
</style>
</head>
<body>

<header class="top">
  <div class="top-row">
    <div class="brand"><span class="lat">TKAWEN</span><span class="x">·</span><span class="by">أدوات مجانية</span></div>
    <a href="https://tkawen.online" class="top-link">→ tkawen.online</a>
  </div>
</header>

<section class="hero">
  <span class="eyebrow">أداة مجانية · 100٪ دقيق</span>
  <h1>حاسبة Yalidine — احسب سعر التوصيل لأي ولاية</h1>
  <p class="lede">أسعار محدّثة لـ 48 ولاية. اختر الولاية والوزن، احصل على السعر فورا.</p>
</section>

<main>

  <!-- Calculator form -->
  <form method="post" class="card" id="calc-form">
    <div class="form-grid">
      <div>
        <label for="from">من ولاية (الإرسال)</label>
        <select name="from" id="from">
          <?php foreach ($prices as $id => $w): ?>
            <option value="<?= $id ?>" <?= $id === $from ? 'selected' : '' ?>><?= $id ?> · <?= htmlspecialchars($w['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label for="to">إلى ولاية (الوصول) <span style="color:#dc2626">*</span></label>
        <select name="to" id="to" required>
          <option value="">— اختر ولاية الوصول —</option>
          <?php foreach ($prices as $id => $w): ?>
            <option value="<?= $id ?>" <?= $id === $to ? 'selected' : '' ?>><?= $id ?> · <?= htmlspecialchars($w['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
    </div>

    <div style="margin-top:14px">
      <label>طريقة التوصيل</label>
      <div class="mode-toggle">
        <label><input type="radio" name="mode" value="home" <?= $mode === 'home' ? 'checked' : '' ?>><span>توصيل إلى المنزل</span></label>
        <label><input type="radio" name="mode" value="desk" <?= $mode === 'desk' ? 'checked' : '' ?>><span>Stop-Desk (أرخص)</span></label>
      </div>
    </div>

    <div style="margin-top:14px">
      <label for="weight">الوزن (كغ) — حتى 30 كغ</label>
      <input type="number" name="weight" id="weight" min="0.1" max="30" step="0.1" value="<?= $weight ?>">
    </div>

    <button type="submit" class="calc-btn">احسب السعر ←</button>
  </form>

  <?php if ($result): ?>
  <!-- Result -->
  <section class="result" id="result">
    <div class="result-h">سعر التوصيل إلى <?= htmlspecialchars($result['wilaya_name']) ?></div>
    <div class="result-total"><span class="lat"><?= number_format($result['total']) ?></span><span class="currency">دج</span></div>
    <div class="result-sub"><?= $result['mode_label'] ?> · مدة التوصيل: <?= $result['estimated_days'] ?></div>

    <div class="result-rows">
      <div class="result-row"><span>الوزن المحسوب</span><b class="lat"><?= $result['weight'] ?> كغ</b></div>
      <div class="result-row"><span>السعر الأساسي</span><b class="lat"><?= number_format($result['base']) ?> دج</b></div>
      <?php if ($result['surcharge'] > 0): ?>
      <div class="result-row"><span>زيادة الوزن</span><b class="lat">+ <?= number_format($result['surcharge']) ?> دج</b></div>
      <?php endif; ?>
      <div class="result-row"><span>المنطقة</span><b>Zone <?= $result['zone'] ?></b></div>
    </div>

    <div class="zone-info">
      💡 <strong>نصيحة:</strong> Stop-Desk دائما أرخص بـ <?= number_format($result['base'] - $prices[$to]['desk']) ?> دج من التوصيل المنزلي.
      إن كان زبونك قريبا من مركز Yalidine، اقترح عليه Stop-Desk.
    </div>
  </section>

  <!-- Email capture (show only after a calculation) -->
  <div class="capture">
    <div class="capture-h">📩 أرسل لي السعر على بريدي</div>
    <div class="capture-p">احصل على تفصيل الأسعار + نصائح لتقليل تكلفة التوصيل + ربط Yalidine بمتجرك تلقائيا.</div>
    <form class="capture-form" id="cap-form" autocomplete="on">
      <input type="email" name="email" placeholder="you@example.com" required>
      <input type="text" name="trap" class="trap" tabindex="-1" autocomplete="off" aria-hidden="true">
      <input type="hidden" name="calc_to" value="<?= $result['wilaya_id'] ?>">
      <input type="hidden" name="calc_total" value="<?= $result['total'] ?>">
      <input type="hidden" name="calc_weight" value="<?= $result['weight'] ?>">
      <button type="submit">أرسل</button>
    </form>
    <div class="capture-msg" id="cap-msg"></div>
  </div>
  <?php endif; ?>

  <!-- Promo card → MyStoq -->
  <div class="promo-card">
    <h3>تبيع أونلاين وتدفع رسوم Yalidine يدويا؟</h3>
    <p>MyStoq يربط Yalidine تلقائيا — كل طلب، كل ملصق، كل تتبع. بدون Excel.</p>
    <a href="https://tkawen.online/try/?p=mystoq&utm_source=tools-yalidine&utm_medium=tool&utm_campaign=yalidine-calc">جرب MyStoq 90 يوم مجانا ←</a>
  </div>

</main>

<footer>
  أداة من <a href="https://tkawen.online" class="lat">TKAWEN</a> · الأسعار مرجعية — للأسعار الرسمية تواصل مع Yalidine مباشرة
</footer>

<script>
  // Submit email capture via intel API
  const capForm = document.getElementById('cap-form');
  const capMsg = document.getElementById('cap-msg');
  if (capForm) {
    capForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const fd = new FormData(capForm);
      capMsg.className = 'capture-msg';
      capMsg.textContent = '';

      try {
        const r = await fetch('/intel/capture.php', {
          method: 'POST',
          headers: {'Content-Type': 'application/json'},
          credentials: 'include',
          body: JSON.stringify({
            email: fd.get('email'),
            source: 'tools-yalidine',
            page: '/tools/yalidine.php',
            kind: 'tool_result_request',
            fields: {
              tool: 'yalidine-calc',
              to_wilaya: fd.get('calc_to'),
              total: fd.get('calc_total'),
              weight: fd.get('calc_weight'),
            },
            trap: fd.get('trap'),
          }),
        });
        const data = await r.json();
        if (data.ok) {
          capMsg.className = 'capture-msg ok';
          capMsg.innerHTML = '✓ تم! ستصلك تفاصيل السعر + نصائح خلال دقائق.';
          capForm.querySelector('button').disabled = true;
          capForm.querySelector('button').textContent = '✓ تم';
        } else {
          capMsg.className = 'capture-msg err';
          capMsg.textContent = data.error === 'rate_limited' ? 'انتظر دقيقة وحاول مجددا' : 'خطأ — حاول مجددا';
        }
      } catch (e) {
        capMsg.className = 'capture-msg err';
        capMsg.textContent = 'خطأ في الاتصال';
      }
    });
  }

  // Fire pageview event
  fetch('/intel/capture.php?event=1', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    credentials: 'include',
    body: JSON.stringify({
      kind: 'tool_view',
      source: 'tools-yalidine',
      page: '/tools/yalidine.php',
      referrer: document.referrer,
    }),
  }).catch(() => {});
</script>

</body>
</html>
