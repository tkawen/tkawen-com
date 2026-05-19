<?php
/**
 * tools/wilaya.php — Algerian postal code → wilaya lookup + wilaya browser.
 *
 * DZ postal codes are 5 digits where the first 2 digits = wilaya number.
 *   16000 → 16 → الجزائر
 *   19000 → 19 → سطيف
 *
 * Supports search by:
 *   - Postal code (full or just prefix)
 *   - Wilaya number
 *   - Wilaya name (Arabic or French)
 *
 * SEO targets:
 *   "ولاية رمز بريدي"  ·  "code postal algerie"  ·  "wilaya finder Algeria"
 */

declare(strict_types=1);
header_remove('X-Powered-By');

// Full DZ wilaya data — name (ar), name (fr), zone, major communes
$WILAYAS = [
    1  => ['ar'=>'أدرار',         'fr'=>'Adrar',           'zone'=>'C', 'communes'=>['Adrar','Reggane','Aoulef','Timimoun']],
    2  => ['ar'=>'الشلف',         'fr'=>'Chlef',           'zone'=>'B', 'communes'=>['Chlef','Ténès','Oued Sly']],
    3  => ['ar'=>'الأغواط',       'fr'=>'Laghouat',        'zone'=>'B', 'communes'=>['Laghouat','Aflou','Ksar el Hirane']],
    4  => ['ar'=>'أم البواقي',    'fr'=>'Oum El Bouaghi',  'zone'=>'B', 'communes'=>['Oum El Bouaghi','Aïn Beïda','Aïn M\'lila']],
    5  => ['ar'=>'باتنة',         'fr'=>'Batna',           'zone'=>'B', 'communes'=>['Batna','Barika','Arris','Merouana']],
    6  => ['ar'=>'بجاية',         'fr'=>'Béjaïa',          'zone'=>'B', 'communes'=>['Béjaïa','Akbou','Kherrata','El Kseur']],
    7  => ['ar'=>'بسكرة',         'fr'=>'Biskra',          'zone'=>'B', 'communes'=>['Biskra','Sidi Okba','El Outaya']],
    8  => ['ar'=>'بشار',          'fr'=>'Béchar',          'zone'=>'C', 'communes'=>['Béchar','Beni Ounif','Kenadsa']],
    9  => ['ar'=>'البليدة',       'fr'=>'Blida',           'zone'=>'A', 'communes'=>['Blida','Boufarik','Larbaâ','Chebli']],
    10 => ['ar'=>'البويرة',       'fr'=>'Bouira',          'zone'=>'B', 'communes'=>['Bouira','Lakhdaria','M\'Chedallah']],
    11 => ['ar'=>'تمنراست',       'fr'=>'Tamanrasset',     'zone'=>'C', 'communes'=>['Tamanrasset','Tin Zaouatine','In Salah']],
    12 => ['ar'=>'تبسة',          'fr'=>'Tébessa',         'zone'=>'B', 'communes'=>['Tébessa','Bir el Ater','Cheria']],
    13 => ['ar'=>'تلمسان',        'fr'=>'Tlemcen',         'zone'=>'B', 'communes'=>['Tlemcen','Maghnia','Remchi','Nedroma']],
    14 => ['ar'=>'تيارت',         'fr'=>'Tiaret',          'zone'=>'B', 'communes'=>['Tiaret','Sougueur','Frenda','Mahdia']],
    15 => ['ar'=>'تيزي وزو',      'fr'=>'Tizi Ouzou',      'zone'=>'B', 'communes'=>['Tizi Ouzou','Azazga','Draâ Ben Khedda','Larbaâ Nath Irathen']],
    16 => ['ar'=>'الجزائر',       'fr'=>'Alger',           'zone'=>'A', 'communes'=>['Alger Centre','Bab El Oued','Hussein Dey','Bir Mourad Raïs','Dar El Beïda','Rouïba','Zéralda']],
    17 => ['ar'=>'الجلفة',        'fr'=>'Djelfa',          'zone'=>'B', 'communes'=>['Djelfa','Aïn Oussera','Messaad']],
    18 => ['ar'=>'جيجل',          'fr'=>'Jijel',           'zone'=>'B', 'communes'=>['Jijel','Taher','El Milia','Chekfa']],
    19 => ['ar'=>'سطيف',          'fr'=>'Sétif',           'zone'=>'B', 'communes'=>['Sétif','El Eulma','Aïn Oulmène','Bougaâ']],
    20 => ['ar'=>'سعيدة',         'fr'=>'Saïda',           'zone'=>'B', 'communes'=>['Saïda','Aïn El Hadjar','Sidi Boubekeur']],
    21 => ['ar'=>'سكيكدة',        'fr'=>'Skikda',          'zone'=>'B', 'communes'=>['Skikda','Azzaba','Collo']],
    22 => ['ar'=>'سيدي بلعباس',   'fr'=>'Sidi Bel Abbès',  'zone'=>'B', 'communes'=>['Sidi Bel Abbès','Telagh','Marhoum']],
    23 => ['ar'=>'عنابة',         'fr'=>'Annaba',          'zone'=>'B', 'communes'=>['Annaba','El Bouni','Berrahal','El Hadjar']],
    24 => ['ar'=>'قالمة',         'fr'=>'Guelma',          'zone'=>'B', 'communes'=>['Guelma','Hammam Debagh','Oued Zenati']],
    25 => ['ar'=>'قسنطينة',       'fr'=>'Constantine',     'zone'=>'B', 'communes'=>['Constantine','El Khroub','Hamma Bouziane','Aïn Smara']],
    26 => ['ar'=>'المدية',        'fr'=>'Médéa',           'zone'=>'B', 'communes'=>['Médéa','Berrouaghia','Ksar El Boukhari']],
    27 => ['ar'=>'مستغانم',       'fr'=>'Mostaganem',      'zone'=>'B', 'communes'=>['Mostaganem','Hassi Mameche','Aïn Tédelès']],
    28 => ['ar'=>'المسيلة',       'fr'=>'M\'Sila',         'zone'=>'B', 'communes'=>['M\'Sila','Bou Saâda','Sidi Aïssa']],
    29 => ['ar'=>'معسكر',         'fr'=>'Mascara',         'zone'=>'B', 'communes'=>['Mascara','Tighennif','Mohammadia']],
    30 => ['ar'=>'ورقلة',         'fr'=>'Ouargla',         'zone'=>'C', 'communes'=>['Ouargla','Touggourt','Hassi Messaoud']],
    31 => ['ar'=>'وهران',         'fr'=>'Oran',            'zone'=>'B', 'communes'=>['Oran','Es Senia','Bir El Djir','Arzew','Aïn El Turk']],
    32 => ['ar'=>'البيض',         'fr'=>'El Bayadh',       'zone'=>'C', 'communes'=>['El Bayadh','Bougtoub','Bouâlem']],
    33 => ['ar'=>'إليزي',         'fr'=>'Illizi',          'zone'=>'C', 'communes'=>['Illizi','Djanet','In Aménas']],
    34 => ['ar'=>'برج بوعريريج',  'fr'=>'Bordj Bou Arreridj','zone'=>'B', 'communes'=>['Bordj Bou Arreridj','Ras El Oued','El Hamadia']],
    35 => ['ar'=>'بومرداس',       'fr'=>'Boumerdes',       'zone'=>'A', 'communes'=>['Boumerdes','Boudouaou','Khemis El Khechna','Thénia']],
    36 => ['ar'=>'الطارف',        'fr'=>'El Tarf',         'zone'=>'B', 'communes'=>['El Tarf','El Kala','Bouhadjar','Drean']],
    37 => ['ar'=>'تندوف',         'fr'=>'Tindouf',         'zone'=>'C', 'communes'=>['Tindouf','Oum El Assel']],
    38 => ['ar'=>'تيسمسيلت',      'fr'=>'Tissemsilt',      'zone'=>'B', 'communes'=>['Tissemsilt','Bordj Bounaâma','Theniet El Had']],
    39 => ['ar'=>'الوادي',        'fr'=>'El Oued',         'zone'=>'C', 'communes'=>['El Oued','Guemar','Robbah','Reguiba']],
    40 => ['ar'=>'خنشلة',         'fr'=>'Khenchela',       'zone'=>'B', 'communes'=>['Khenchela','Kaïs','Bouhmama']],
    41 => ['ar'=>'سوق أهراس',     'fr'=>'Souk Ahras',      'zone'=>'B', 'communes'=>['Souk Ahras','Sedrata','M\'daourouch']],
    42 => ['ar'=>'تيبازة',        'fr'=>'Tipaza',          'zone'=>'A', 'communes'=>['Tipaza','Cherchell','Koléa','Hadjout']],
    43 => ['ar'=>'ميلة',          'fr'=>'Mila',            'zone'=>'B', 'communes'=>['Mila','Ferdjioua','Chelghoum Laïd']],
    44 => ['ar'=>'عين الدفلى',    'fr'=>'Aïn Defla',       'zone'=>'B', 'communes'=>['Aïn Defla','Khemis Miliana','El Attaf']],
    45 => ['ar'=>'النعامة',       'fr'=>'Naâma',           'zone'=>'C', 'communes'=>['Naâma','Aïn Sefra','Mécheria']],
    46 => ['ar'=>'عين تموشنت',    'fr'=>'Aïn Témouchent',  'zone'=>'B', 'communes'=>['Aïn Témouchent','Hammam Bou Hadjar','Beni Saf']],
    47 => ['ar'=>'غرداية',        'fr'=>'Ghardaïa',        'zone'=>'C', 'communes'=>['Ghardaïa','Berriane','El Atteuf','Metlili']],
    48 => ['ar'=>'غليزان',        'fr'=>'Relizane',        'zone'=>'B', 'communes'=>['Relizane','Yellel','Oued Rhiou','Ammi Moussa']],
];

$q = trim((string)($_GET['q'] ?? $_POST['q'] ?? ''));
$result = null;

if ($q !== '') {
    // Strip spaces
    $clean = preg_replace('/\s+/', '', $q);

    // Postal code (numeric, 1-5 digits → extract first 2 as wilaya)
    if (ctype_digit($clean)) {
        $prefix = (int)substr($clean, 0, 2);
        if (isset($WILAYAS[$prefix])) {
            $result = ['mode' => 'postal', 'wilaya_id' => $prefix, 'wilaya' => $WILAYAS[$prefix], 'input' => $q];
        }
    } else {
        // Search by name (ar or fr, case-insensitive)
        $needle = mb_strtolower($q);
        $matches = [];
        foreach ($WILAYAS as $id => $w) {
            if (mb_stripos($w['ar'], $q) !== false || stripos($w['fr'], $needle) !== false) {
                $matches[$id] = $w;
            }
            // Also match against communes
            else {
                foreach ($w['communes'] as $c) {
                    if (stripos($c, $needle) !== false || mb_stripos($c, $q) !== false) {
                        $matches[$id] = $w;
                        break;
                    }
                }
            }
        }
        if (count($matches) === 1) {
            $id = array_key_first($matches);
            $result = ['mode' => 'name', 'wilaya_id' => $id, 'wilaya' => $matches[$id], 'input' => $q];
        } elseif (count($matches) > 1) {
            $result = ['mode' => 'list', 'matches' => $matches, 'input' => $q];
        }
    }
}

$page_title = $result && isset($result['wilaya'])
    ? 'ولاية ' . $result['wilaya']['ar'] . ' (' . $result['wilaya_id'] . ') · ' . $result['wilaya']['fr']
    : 'أداة البحث عن الولاية الجزائرية · TKAWEN';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#020617">
<title><?= htmlspecialchars($page_title) ?></title>
<meta name="description" content="اعرف ولايتك من الرمز البريدي. 48 ولاية، آلاف البلديات. ابحث بالرمز أو اسم البلدية أو الولاية.">
<meta name="keywords" content="رمز بريدي, code postal Algerie, wilaya finder, ولايات الجزائر, postal code">

<meta property="og:title" content="<?= htmlspecialchars($page_title) ?>">
<meta property="og:description" content="48 ولاية، آلاف البلديات">
<meta property="og:type" content="website">
<meta property="og:locale" content="ar_DZ">

<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebApplication",
  "name": "أداة الولاية والرمز البريدي",
  "url": "https://tkawen.online/tools/wilaya.php",
  "applicationCategory": "ReferenceApplication",
  "operatingSystem": "Web",
  "offers": {"@type": "Offer", "price": "0", "priceCurrency": "DZD"}
}
</script>

<link rel="canonical" href="https://tkawen.online/tools/wilaya.php">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">

<style>
  :root { --bg:#fff; --bg-2:#f8fafc; --text:#0f172a; --muted:#64748b; --dim:#94a3b8;
          --line:#e2e8f0; --accent:#a855f7; --accent-dark:#9333ea; --green:#10b981; }
  *{box-sizing:border-box} body{margin:0;background:var(--bg-2);color:var(--text);
    font-family:'Cairo',-apple-system,'Segoe UI',sans-serif;line-height:1.6;direction:rtl;-webkit-font-smoothing:antialiased}
  .lat{font-family:'JetBrains Mono','SF Mono',monospace;direction:ltr;unicode-bidi:embed}

  header.top{background:var(--bg);border-bottom:1px solid var(--line);padding:14px 24px}
  .top-row{max-width:880px;margin:0 auto;display:flex;align-items:center;justify-content:space-between}
  .brand{font-weight:800;font-size:17px}.brand .x{color:var(--dim);margin:0 6px}
  .brand .by{color:var(--muted);font-weight:500;font-size:13px}
  .top-link{color:var(--muted);text-decoration:none;font-size:13px}

  .hero{max-width:760px;margin:0 auto;padding:32px 20px 8px;text-align:center}
  h1{margin:0 0 12px;font-size:28px;font-weight:800;letter-spacing:-.01em;line-height:1.3}
  .lede{color:var(--muted);font-size:15px;max-width:520px;margin:0 auto}
  .eyebrow{display:inline-block;background:rgba(168,85,247,.12);color:var(--accent-dark);
    padding:4px 12px;border-radius:999px;font-size:11px;font-weight:700;letter-spacing:.06em;margin-bottom:14px}

  main{max-width:760px;margin:0 auto;padding:24px 20px}
  .card{background:var(--bg);border:1px solid var(--line);border-radius:14px;
    padding:24px;box-shadow:0 1px 3px rgba(0,0,0,.03);margin-bottom:20px}

  input[type=text]{width:100%;padding:14px 16px;font-size:17px;border:2px solid var(--line);
    border-radius:10px;background:#fff;color:var(--text);outline:none;font-family:'Cairo',sans-serif}
  input[type=text]:focus{border-color:var(--accent)}
  .calc-btn{width:100%;margin-top:14px;padding:14px;background:var(--accent);color:#fff;
    border:0;border-radius:10px;font-size:16px;font-weight:800;cursor:pointer;font-family:'Cairo',sans-serif;
    transition:background .15s}
  .calc-btn:hover{background:var(--accent-dark)}
  .help{font-size:12px;color:var(--muted);margin-top:8px;line-height:1.6}
  .help code{font-family:'JetBrains Mono',monospace;background:rgba(0,0,0,.04);padding:2px 6px;border-radius:4px;direction:ltr;display:inline-block}

  /* Single result */
  .result-card{background:linear-gradient(135deg,#faf5ff,#f3e8ff);border:1px solid #d8b4fe;
    border-radius:14px;padding:24px;margin-bottom:20px}
  .res-id{font-size:60px;font-weight:900;color:var(--accent-dark);line-height:1;letter-spacing:-.04em;font-family:'JetBrains Mono',monospace}
  .res-ar{font-size:26px;font-weight:800;margin:8px 0 4px;color:#581c87}
  .res-fr{font-size:14px;color:#7e22ce;font-style:italic}
  .res-zone{display:inline-block;margin-top:10px;padding:3px 10px;background:rgba(88,28,135,.12);
    border-radius:999px;font-size:11px;color:#581c87;font-weight:700;letter-spacing:.04em}
  .res-section{margin-top:18px;padding-top:18px;border-top:1px dashed #d8b4fe}
  .res-section h4{margin:0 0 8px;font-size:12px;color:#7e22ce;font-weight:700;letter-spacing:.06em}
  .res-tags{display:flex;flex-wrap:wrap;gap:6px}
  .res-tag{display:inline-block;padding:5px 10px;background:rgba(255,255,255,.7);
    border:1px solid #d8b4fe;border-radius:6px;font-size:13px;color:#581c87}

  /* List result */
  .list-card{background:var(--bg);border:1px solid var(--line);border-radius:14px;padding:20px;margin-bottom:20px}
  .list-row{display:flex;align-items:center;gap:12px;padding:10px 12px;border-radius:8px;
    text-decoration:none;color:var(--text);transition:background .15s}
  .list-row:hover{background:var(--bg-2)}
  .list-num{font-family:'JetBrains Mono',monospace;font-weight:800;color:var(--accent);
    background:rgba(168,85,247,.12);padding:4px 8px;border-radius:6px;font-size:13px;min-width:34px;text-align:center}
  .list-info{flex:1}
  .list-ar{font-weight:700;font-size:15px}
  .list-fr{color:var(--muted);font-size:12px}

  /* All wilayas browse */
  .wilaya-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:6px;margin-top:12px}
  .wilaya-grid a{padding:8px 10px;background:var(--bg);border:1px solid var(--line);border-radius:8px;
    text-decoration:none;color:var(--text);display:flex;align-items:center;gap:8px;transition:all .15s;font-size:13px}
  .wilaya-grid a:hover{border-color:var(--accent);background:#faf5ff}
  .wilaya-grid .num{font-family:'JetBrains Mono',monospace;font-weight:700;color:var(--accent-dark);min-width:24px;font-size:11px}

  /* Email capture */
  .capture{background:var(--bg);border:1px solid var(--line);border-radius:14px;padding:20px 24px;margin-bottom:20px}
  .capture-h{font-weight:800;font-size:16px;margin-bottom:6px}
  .capture-p{color:var(--muted);font-size:13px;margin-bottom:14px}
  .capture-form{display:flex;gap:8px}
  .capture-form input{flex:1;padding:11px 12px;font-size:14px;border:1px solid var(--line);border-radius:8px}
  .capture-form button{padding:11px 20px;background:var(--text);color:#fff;border:0;border-radius:8px;
    font-weight:700;cursor:pointer;font-family:'Cairo',sans-serif}
  .capture-msg{margin-top:10px;font-size:13px;display:none}
  .capture-msg.ok{display:block;color:var(--green)}
  .capture-msg.err{display:block;color:#dc2626}

  .promo-card{background:linear-gradient(135deg,#0f172a,#1e293b);color:#fff;border-radius:14px;padding:24px;text-align:center}
  .promo-card h3{margin:0 0 8px;font-size:18px}
  .promo-card p{color:#94a3b8;font-size:13px;margin:0 0 14px}
  .promo-card a{display:inline-block;background:#a855f7;color:#fff;text-decoration:none;
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
  <span class="eyebrow">48 ولاية · آلاف البلديات</span>
  <h1>أداة البحث عن الولاية الجزائرية</h1>
  <p class="lede">أدخل الرمز البريدي (مثلا 16000) أو اسم الولاية أو البلدية، احصل على كل المعلومات فورا.</p>
</section>

<main>
  <form method="get" class="card">
    <input type="text" name="q" autocomplete="off"
           value="<?= htmlspecialchars($q, ENT_QUOTES, 'UTF-8') ?>"
           placeholder="16000 أو الجزائر أو Alger">
    <button type="submit" class="calc-btn">ابحث ←</button>
    <div class="help">
      جرب: <code><a href="?q=16000">16000</a></code> ·
           <code><a href="?q=Sétif">Sétif</a></code> ·
           <code><a href="?q=البليدة">البليدة</a></code> ·
           <code><a href="?q=Akbou">Akbou</a></code>
    </div>
  </form>

  <?php if ($result && isset($result['wilaya'])): ?>
    <?php $w = $result['wilaya']; $id = $result['wilaya_id']; ?>
    <section class="result-card">
      <div class="res-id"><?= str_pad((string)$id, 2, '0', STR_PAD_LEFT) ?></div>
      <div class="res-ar">ولاية <?= htmlspecialchars($w['ar']) ?></div>
      <div class="res-fr">Wilaya de <?= htmlspecialchars($w['fr']) ?></div>
      <span class="res-zone">Zone <?= $w['zone'] ?> · <?= $w['zone'] === 'A' ? 'سريع' : ($w['zone'] === 'B' ? 'عادي' : 'بعيد') ?></span>

      <div class="res-section">
        <h4>الرمز البريدي</h4>
        <div class="lat" style="font-size:18px;font-weight:700;color:#581c87">
          <?= str_pad((string)$id, 2, '0', STR_PAD_LEFT) ?>000 — <?= str_pad((string)$id, 2, '0', STR_PAD_LEFT) ?>999
        </div>
      </div>

      <div class="res-section">
        <h4>بلديات رئيسية</h4>
        <div class="res-tags">
          <?php foreach ($w['communes'] as $c): ?>
            <span class="res-tag"><?= htmlspecialchars($c) ?></span>
          <?php endforeach; ?>
        </div>
      </div>

      <div class="res-section">
        <h4>روابط مفيدة</h4>
        <a href="/tools/yalidine.php?to=<?= $id ?>" style="color:#581c87;font-size:13px;text-decoration:underline">احسب سعر التوصيل إلى هذه الولاية ←</a>
      </div>
    </section>

    <!-- Email capture -->
    <div class="capture">
      <div class="capture-h">📩 احصل على ملف PDF كامل لكل ولايات الجزائر</div>
      <div class="capture-p">48 ولاية + 1,541 بلدية + الرمز البريدي + المسافة من الجزائر العاصمة. PDF مجاني.</div>
      <form class="capture-form" id="cap-form">
        <input type="email" name="email" placeholder="you@example.com" required>
        <input type="text" name="trap" class="trap" tabindex="-1" autocomplete="off" aria-hidden="true">
        <button type="submit">أرسل</button>
      </form>
      <div class="capture-msg" id="cap-msg"></div>
    </div>
  <?php elseif ($result && $result['mode'] === 'list'): ?>
    <section class="list-card">
      <h3 style="margin-top:0;font-size:14px;color:#7e22ce">عدة نتائج مطابقة:</h3>
      <?php foreach ($result['matches'] as $id => $w): ?>
        <a class="list-row" href="?q=<?= urlencode($w['ar']) ?>">
          <span class="list-num"><?= str_pad((string)$id, 2, '0', STR_PAD_LEFT) ?></span>
          <div class="list-info">
            <div class="list-ar"><?= htmlspecialchars($w['ar']) ?></div>
            <div class="list-fr"><?= htmlspecialchars($w['fr']) ?></div>
          </div>
        </a>
      <?php endforeach; ?>
    </section>
  <?php endif; ?>

  <!-- Browse all wilayas -->
  <div class="card">
    <h3 style="margin-top:0;font-size:13px;color:var(--muted);font-weight:700;letter-spacing:.06em;text-transform:uppercase">تصفح كل الولايات</h3>
    <div class="wilaya-grid">
      <?php foreach ($WILAYAS as $id => $w): ?>
        <a href="?q=<?= urlencode($w['ar']) ?>">
          <span class="num"><?= str_pad((string)$id, 2, '0', STR_PAD_LEFT) ?></span>
          <span><?= htmlspecialchars($w['ar']) ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="promo-card">
    <h3>تبيع في كل الجزائر؟</h3>
    <p>MyStoq يعرف رسوم التوصيل لكل ولاية تلقائيا — Yalidine، CTM، Aramex، PostaTN.</p>
    <a href="/try/?p=mystoq&utm_source=tools-wilaya">جرب MyStoq 90 يوم مجانا ←</a>
  </div>
</main>

<footer>
  أداة من <a href="/" class="lat">TKAWEN</a>
</footer>

<script async src="/intel/track.js" data-source="tools-wilaya"></script>

<script>
  const capForm = document.getElementById('cap-form');
  const capMsg = document.getElementById('cap-msg');
  if (capForm) {
    capForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const fd = new FormData(capForm);
      capMsg.className = 'capture-msg';
      try {
        const r = await fetch('/intel/capture.php', {
          method: 'POST', headers: {'Content-Type':'application/json'}, credentials: 'include',
          body: JSON.stringify({
            email: fd.get('email'),
            source: 'tools-wilaya', page: '/tools/wilaya.php',
            kind: 'tool_result_request',
            fields: { tool: 'wilaya-finder', wilaya: '<?= $result['wilaya_id'] ?? '' ?>' },
            trap: fd.get('trap'),
          }),
        });
        const d = await r.json();
        if (d.ok) { capMsg.className='capture-msg ok'; capMsg.textContent='✓ تم! PDF في الطريق.';
          capForm.querySelector('button').disabled = true; capForm.querySelector('button').textContent='✓ تم'; }
        else { capMsg.className='capture-msg err'; capMsg.textContent=d.error==='rate_limited'?'انتظر دقيقة':'خطأ — حاول مجددا'; }
      } catch (e) { capMsg.className='capture-msg err'; capMsg.textContent='خطأ في الاتصال'; }
    });
  }
</script>
</body>
</html>
