<?php
declare(strict_types=1);
header_remove('X-Powered-By');

$tools = [
    'yalidine' => [
        'icon' => '📦', 'name' => 'حاسبة Yalidine', 'fr' => 'Calculateur Yalidine',
        'desc' => 'احسب سعر التوصيل لـ 48 ولاية فورا. أوزان حتى 30 كغ، توصيل منزلي أو Stop-Desk.',
        'tags' => ['شحن', 'تجارة إلكترونية'],
        'href' => '/tools/yalidine.php',
        'color' => '#10b981',
    ],
    'iban' => [
        'icon' => '🏦', 'name' => 'تأكد من IBAN', 'fr' => 'Valider IBAN',
        'desc' => 'تحقق فوري من IBAN جزائري (24 محرفا) أو RIP/CCP بريد الجزائر. يعرض اسم البنك.',
        'tags' => ['بنوك', 'مالية'],
        'href' => '/tools/iban.php',
        'color' => '#2563eb',
    ],
    'wilaya' => [
        'icon' => '🗺️', 'name' => 'البحث عن الولاية', 'fr' => 'Wilaya finder',
        'desc' => 'الرمز البريدي → الولاية. ابحث بالرقم أو اسم البلدية. 48 ولاية كاملة.',
        'tags' => ['جغرافيا', 'لوجستيك'],
        'href' => '/tools/wilaya.php',
        'color' => '#a855f7',
    ],
];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#020617">
<title>أدوات مجانية للجزائريين · TKAWEN</title>
<meta name="description" content="مجموعة أدوات مجانية للتجار الجزائريين: حساب Yalidine، تحقق من IBAN، البحث عن الولاية، وأكثر. كلها مجانية، دقيقة، بالعربية.">

<meta property="og:title" content="أدوات TKAWEN المجانية للتجار الجزائريين">
<meta property="og:description" content="حسابات Yalidine، IBAN، ولايات، والمزيد قريبا">
<meta property="og:type" content="website">
<meta property="og:locale" content="ar_DZ">

<link rel="canonical" href="https://tkawen.online/tools/">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">

<style>
  :root { --bg:#fff; --bg-2:#f8fafc; --text:#0f172a; --muted:#64748b; --dim:#94a3b8;
          --line:#e2e8f0; --accent:#0f172a; }
  *{box-sizing:border-box} body{margin:0;background:var(--bg-2);color:var(--text);
    font-family:'Cairo',-apple-system,'Segoe UI',sans-serif;line-height:1.6;direction:rtl;-webkit-font-smoothing:antialiased}
  .lat{font-family:'JetBrains Mono','SF Mono',monospace;direction:ltr;unicode-bidi:embed}

  header.top{background:var(--bg);border-bottom:1px solid var(--line);padding:14px 24px}
  .top-row{max-width:1080px;margin:0 auto;display:flex;align-items:center;justify-content:space-between}
  .brand{font-weight:800;font-size:17px}.brand .x{color:var(--dim);margin:0 6px}
  .brand .by{color:var(--muted);font-weight:500;font-size:13px}
  .top-link{color:var(--muted);text-decoration:none;font-size:13px}

  .hero{max-width:760px;margin:0 auto;padding:48px 20px 20px;text-align:center}
  h1{margin:0 0 14px;font-size:36px;font-weight:900;letter-spacing:-.02em;line-height:1.25}
  .lede{color:var(--muted);font-size:16px;max-width:560px;margin:0 auto}
  .eyebrow{display:inline-block;background:rgba(15,23,42,.08);color:var(--accent);
    padding:6px 14px;border-radius:999px;font-size:11px;font-weight:700;letter-spacing:.06em;margin-bottom:18px}

  main{max-width:1080px;margin:0 auto;padding:24px 20px 40px}
  .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:16px}
  .tool-card{background:var(--bg);border:1px solid var(--line);border-radius:16px;padding:24px;
    text-decoration:none;color:inherit;transition:all .2s;display:flex;flex-direction:column;
    box-shadow:0 1px 3px rgba(0,0,0,.03)}
  .tool-card:hover{transform:translateY(-2px);box-shadow:0 8px 20px rgba(0,0,0,.08)}
  .tool-icon{font-size:36px;margin-bottom:14px;line-height:1}
  .tool-name{font-size:20px;font-weight:800;margin-bottom:4px}
  .tool-fr{font-size:12px;color:var(--muted);margin-bottom:10px;font-style:italic}
  .tool-desc{color:var(--muted);font-size:13px;line-height:1.7;margin-bottom:14px;flex:1}
  .tool-tags{display:flex;gap:6px;flex-wrap:wrap}
  .tool-tag{font-size:11px;padding:3px 9px;background:var(--bg-2);border-radius:999px;color:var(--muted)}
  .tool-cta{margin-top:14px;padding-top:14px;border-top:1px solid var(--line);font-size:13px;color:var(--accent);font-weight:700}

  /* Coming soon section */
  .coming{margin-top:36px;background:var(--bg);border:1px dashed var(--line);border-radius:16px;padding:24px;text-align:center}
  .coming-h{font-size:12px;color:var(--muted);font-weight:700;letter-spacing:.08em;margin-bottom:14px}
  .coming-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:8px;max-width:680px;margin:0 auto}
  .coming-item{padding:10px 14px;background:var(--bg-2);border-radius:8px;font-size:13px;color:var(--muted);
    display:flex;align-items:center;gap:8px;justify-content:center}
  .coming-item span{font-size:18px}

  /* Promo */
  .promo{margin-top:36px;background:linear-gradient(135deg,#0f172a,#1e293b);color:#fff;
    border-radius:16px;padding:32px;text-align:center}
  .promo h2{margin:0 0 8px;font-size:22px}
  .promo p{color:#94a3b8;font-size:14px;margin:0 0 18px;max-width:520px;margin-inline:auto}
  .promo a{display:inline-block;background:#10b981;color:#fff;text-decoration:none;
    padding:12px 28px;border-radius:10px;font-weight:700;font-size:15px}

  footer{padding:30px 20px;text-align:center;color:var(--dim);font-size:12px}
  footer a{color:var(--muted);text-decoration:none}

  @media (max-width:600px){ h1{font-size:28px} .hero{padding:32px 16px 12px} }
</style>
</head>
<body>

<header class="top">
  <div class="top-row">
    <div class="brand"><span class="lat">TKAWEN</span><span class="x">·</span><span class="by">أدوات مجانية</span></div>
    <a href="https://tkawen.online" class="top-link">→ الموقع الرئيسي</a>
  </div>
</header>

<section class="hero">
  <span class="eyebrow">أدوات مفتوحة المصدر</span>
  <h1>أدوات مجانية للتجار الجزائريين</h1>
  <p class="lede">بنينا هذه الأدوات لأننا احتجناها نحن أيضا. مجانية، دقيقة، بالعربية، ولا تطلب تسجيل.</p>
</section>

<main>
  <div class="grid">
    <?php foreach ($tools as $slug => $t): ?>
      <a href="<?= htmlspecialchars($t['href']) ?>" class="tool-card">
        <div class="tool-icon" style="color:<?= $t['color'] ?>"><?= $t['icon'] ?></div>
        <div class="tool-name"><?= htmlspecialchars($t['name']) ?></div>
        <div class="tool-fr"><?= htmlspecialchars($t['fr']) ?></div>
        <div class="tool-desc"><?= htmlspecialchars($t['desc']) ?></div>
        <div class="tool-tags">
          <?php foreach ($t['tags'] as $tag): ?>
            <span class="tool-tag"><?= htmlspecialchars($tag) ?></span>
          <?php endforeach; ?>
        </div>
        <div class="tool-cta">جرب الأداة ←</div>
      </a>
    <?php endforeach; ?>
  </div>

  <div class="coming">
    <div class="coming-h">قريبا · جار التطوير</div>
    <div class="coming-grid">
      <div class="coming-item"><span>💱</span> محول العملات</div>
      <div class="coming-item"><span>🧾</span> حاسبة TVA</div>
      <div class="coming-item"><span>📊</span> حاسبة الأرباح</div>
      <div class="coming-item"><span>📅</span> موعد الزكاة</div>
      <div class="coming-item"><span>🆔</span> توليد رمز QR</div>
      <div class="coming-item"><span>📱</span> فحص رقم هاتف</div>
    </div>
  </div>

  <div class="promo">
    <h2>كل هذه الأدوات مدمجة في MyStoq تلقائيا</h2>
    <p>افتح متجرك الإلكتروني وهذه الأدوات تشتغل في الخلفية — حسابات Yalidine، تحقق من العناوين، كل شيء.</p>
    <a href="/try/?p=mystoq&utm_source=tools-index&utm_medium=tool-directory">جرب MyStoq 90 يوم مجانا ←</a>
  </div>
</main>

<footer>
  TKAWEN · أدوات بنيت لتبقى مفيدة · <a href="https://tkawen.online">tkawen.online</a>
</footer>

<script async src="/intel/track.js" data-source="tools-index"></script>
</body>
</html>
