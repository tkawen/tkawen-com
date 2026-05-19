<?php
declare(strict_types=1);
header_remove('X-Powered-By');

// Manual blog index — each post is a .php file in this folder.
// In production this should be auto-discovered; for now hand-curated.
$posts = [
    [
        'slug' => 'best-shipping-companies-dz-2026',
        'title' => 'أفضل 5 شركات توصيل في الجزائر 2026 — مقارنة كاملة',
        'desc' => 'مقارنة Yalidine، CTM، Aramex، ZR Express، PostaTN — الأسعار، التغطية، السرعة. من تجربة 50+ تاجر.',
        'pub' => '2026-05-19',
        'mod' => '2026-05-19',
        'read_min' => 8,
        'tags' => ['شحن', 'Yalidine', 'CTM'],
        'hero_color' => '#1d4ed8',
    ],
    [
        'slug' => 'edahabia-vs-cib-2026',
        'title' => 'Edahabia أم CIB في 2026؟ المقارنة الكاملة من تاجر استعمل الاثنين',
        'desc' => 'مقارنة بين بطاقتي الدفع الإلكتروني — الرسوم، التغطية، الأمان، التكامل مع المتاجر، والأنسب للتاجر مقابل الزبون.',
        'pub' => '2026-05-19',
        'mod' => '2026-05-19',
        'read_min' => 7,
        'tags' => ['دفع إلكتروني', 'Edahabia', 'CIB'],
        'hero_color' => '#d97706',
    ],
    [
        'slug' => 'shopify-vs-mystoq-2026',
        'title' => 'Shopify أم MyStoq للتاجر الجزائري في 2026؟ مقارنة كاملة',
        'desc' => 'تحليل صريح من تاجر جزائري جرب الاثنين. الأسعار الحقيقية بالدينار، الدفع، التوصيل، الدعم، والقرار النهائي.',
        'pub' => '2026-05-19',
        'mod' => '2026-05-19',
        'read_min' => 9,
        'tags' => ['تجارة إلكترونية', 'Shopify', 'MyStoq'],
        'hero_color' => '#10b981',
    ],
];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#020617">
<title>مدونة TKAWEN · تجارة إلكترونية بالعربية للجزائر</title>
<meta name="description" content="مقالات عميقة عن التجارة الإلكترونية في الجزائر — أدوات، استراتيجيات، تجارب حقيقية من تجار جزائريين.">
<meta property="og:title" content="مدونة TKAWEN">
<meta property="og:description" content="تجارة إلكترونية للجزائريين، بالعربية">
<meta property="og:type" content="website">

<link rel="canonical" href="https://tkawen.online/blog/">
<link rel="alternate" type="application/rss+xml" title="مدونة TKAWEN" href="/blog/rss.xml">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&family=Amiri:wght@400;700&display=swap" rel="stylesheet">

<style>
  :root { --bg:#fff; --bg-2:#f8fafc; --text:#0f172a; --muted:#64748b; --dim:#94a3b8;
          --line:#e2e8f0; --accent:#0f172a; --link:#1d4ed8; }
  *{box-sizing:border-box}
  body{margin:0;background:var(--bg);color:var(--text);font-family:'Cairo',-apple-system,'Segoe UI',sans-serif;
    line-height:1.7;direction:rtl;-webkit-font-smoothing:antialiased}
  .lat{font-family:'JetBrains Mono','SF Mono',monospace;direction:ltr;unicode-bidi:embed}
  a{color:var(--link);text-decoration:none}
  a:hover{text-decoration:underline}

  header.top{background:var(--bg);border-bottom:1px solid var(--line);padding:18px 24px;position:sticky;top:0;z-index:10;backdrop-filter:blur(8px);background:rgba(255,255,255,.92)}
  .top-row{max-width:880px;margin:0 auto;display:flex;align-items:center;justify-content:space-between}
  .brand{font-weight:800;font-size:18px}.brand .x{color:var(--dim);margin:0 6px}
  .brand .by{color:var(--muted);font-weight:500;font-size:14px}
  .top-nav{display:flex;gap:18px;font-size:14px}
  .top-nav a{color:var(--muted)}.top-nav a:hover{color:var(--text);text-decoration:none}

  .hero{max-width:760px;margin:0 auto;padding:64px 20px 24px}
  .eyebrow{display:inline-block;background:rgba(15,23,42,.08);color:var(--accent);
    padding:5px 12px;border-radius:999px;font-size:11px;font-weight:700;letter-spacing:.08em;margin-bottom:16px}
  h1{margin:0 0 14px;font-size:42px;font-weight:900;letter-spacing:-.02em;line-height:1.2}
  .lede{color:var(--muted);font-size:17px;max-width:560px}

  main{max-width:760px;margin:0 auto;padding:24px 20px 60px}

  .post-card{display:block;padding:28px;border-bottom:1px solid var(--line);text-decoration:none;color:inherit;transition:background .15s}
  .post-card:hover{background:var(--bg-2)}
  .post-card:hover{text-decoration:none}
  .post-meta{font-size:12px;color:var(--muted);margin-bottom:10px;display:flex;gap:10px;align-items:center}
  .post-meta time{font-family:'JetBrains Mono',monospace}
  .post-title{font-size:24px;font-weight:800;line-height:1.35;margin-bottom:10px;letter-spacing:-.01em}
  .post-desc{color:var(--muted);font-size:15px;line-height:1.65;margin-bottom:12px}
  .post-tags{display:flex;gap:6px;flex-wrap:wrap}
  .post-tag{font-size:11px;padding:3px 9px;background:var(--bg-2);border-radius:999px;color:var(--muted)}
  .post-cta{margin-top:14px;font-size:13px;color:var(--link);font-weight:700}

  .newsletter{margin-top:48px;background:linear-gradient(135deg,#0f172a,#1e293b);color:#fff;border-radius:16px;padding:32px;text-align:center}
  .newsletter h2{margin:0 0 8px;font-size:22px}
  .newsletter p{color:#94a3b8;font-size:14px;margin:0 0 20px}
  .news-form{display:flex;gap:8px;max-width:380px;margin:0 auto}
  .news-form input{flex:1;padding:12px 14px;font-size:14px;border:1px solid rgba(255,255,255,.15);
    background:rgba(255,255,255,.05);color:#fff;border-radius:8px;outline:none;font-family:'Cairo',sans-serif}
  .news-form input::placeholder{color:#64748b}
  .news-form button{padding:12px 24px;background:#10b981;color:#fff;border:0;border-radius:8px;font-weight:700;cursor:pointer;font-family:'Cairo',sans-serif;white-space:nowrap}
  .news-msg{margin-top:12px;font-size:13px;color:#10b981;display:none}
  .news-msg.show{display:block}
  .trap{position:absolute;left:-9999px;visibility:hidden}

  footer{padding:30px 20px;border-top:1px solid var(--line);text-align:center;color:var(--dim);font-size:12px}
  footer a{color:var(--muted)}

  @media (max-width:600px){
    .hero{padding:40px 16px 12px} h1{font-size:30px} .lede{font-size:15px}
    .post-card{padding:20px 16px} .post-title{font-size:20px}
  }
</style>
</head>
<body>

<header class="top">
  <div class="top-row">
    <div class="brand"><span class="lat">TKAWEN</span><span class="x">·</span><span class="by">المدونة</span></div>
    <nav class="top-nav">
      <a href="/tools/">الأدوات</a>
      <a href="/try/">جرب MyStoq</a>
    </nav>
  </div>
</header>

<section class="hero">
  <span class="eyebrow">مدونة TKAWEN</span>
  <h1>تجارة إلكترونية للجزائريين، بالعربية، من الميدان</h1>
  <p class="lede">مقالات عميقة عن التجارة الإلكترونية في الجزائر — أدوات، استراتيجيات، تجارب حقيقية. ليس محتوى مترجم من الإنجليزية.</p>
</section>

<main>
  <?php foreach ($posts as $p): ?>
    <a href="/blog/<?= $p['slug'] ?>.php" class="post-card">
      <div class="post-meta">
        <time datetime="<?= $p['pub'] ?>"><?= $p['pub'] ?></time>
        <span>·</span>
        <span><?= $p['read_min'] ?> دقائق قراءة</span>
      </div>
      <h2 class="post-title"><?= htmlspecialchars($p['title']) ?></h2>
      <p class="post-desc"><?= htmlspecialchars($p['desc']) ?></p>
      <div class="post-tags">
        <?php foreach ($p['tags'] as $t): ?>
          <span class="post-tag"><?= htmlspecialchars($t) ?></span>
        <?php endforeach; ?>
      </div>
      <div class="post-cta">اقرأ المقال الكامل ←</div>
    </a>
  <?php endforeach; ?>

  <div class="newsletter">
    <h2>📨 مقال جديد كل أسبوع</h2>
    <p>ندوّن عن أدوات حقيقية يستعملها التجار الجزائريون. مرة واحدة في الأسبوع، بدون حشو.</p>
    <form class="news-form" id="news-form" data-tkawen-track data-tkawen-source="blog-newsletter">
      <input type="email" name="email" placeholder="you@example.com" required autocomplete="email">
      <input type="text" name="trap" class="trap" tabindex="-1" autocomplete="off" aria-hidden="true">
      <button type="submit">اشترك</button>
    </form>
    <div class="news-msg" id="news-msg">✓ تم! ستصلك أول رسالة قريبا.</div>
  </div>
</main>

<footer>
  TKAWEN · أنشئ متجرك على <a href="/try/">MyStoq</a> · <a href="/tools/">أدوات مجانية</a> · <a href="/blog/rss.xml">RSS</a>
</footer>

<script async src="/intel/track.js" data-source="blog-index"></script>
<script>
  const newsForm = document.getElementById('news-form');
  const newsMsg = document.getElementById('news-msg');
  newsForm.addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(newsForm);
    try {
      const r = await fetch('/intel/capture.php', {
        method: 'POST', headers: {'Content-Type':'application/json'}, credentials:'include',
        body: JSON.stringify({
          email: fd.get('email'),
          source: 'blog-newsletter',
          kind: 'newsletter_subscribe',
          page: '/blog/',
          trap: fd.get('trap'),
        }),
      });
      const d = await r.json();
      if (d.ok) {
        newsMsg.classList.add('show');
        newsForm.style.display = 'none';
      }
    } catch (e) {}
  });
</script>
</body>
</html>
