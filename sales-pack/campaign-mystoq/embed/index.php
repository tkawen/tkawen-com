<?php declare(strict_types=1); header_remove('X-Powered-By'); ?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>أدوات الإضافة الخارجية · TKAWEN</title>
<meta name="description" content="أضف أدوات TKAWEN إلى موقعك بـ iframe واحد. لقطة بريد، حاسبات، شارة الثقة — كلها مجانية وتعمل في 30 ثانية.">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700;800&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
<style>
  :root{--bg:#fff;--bg-2:#f8fafc;--text:#0f172a;--muted:#475569;--line:#e2e8f0;--accent:#06b6d4;--dark:#0f172a}
  *{box-sizing:border-box}body{margin:0;background:var(--bg-2);color:var(--text);
    font-family:'Cairo',sans-serif;direction:rtl;line-height:1.7;-webkit-font-smoothing:antialiased}
  .lat{font-family:'JetBrains Mono',monospace;direction:ltr;unicode-bidi:embed;font-size:.95em}
  pre{background:var(--dark);color:#e2e8f0;padding:18px 20px;border-radius:10px;overflow-x:auto;
    font-family:'JetBrains Mono',monospace;font-size:13px;direction:ltr;line-height:1.7;margin:14px 0}
  pre code{font-family:inherit}
  .hl{color:#06b6d4}
  header.top{background:var(--bg);border-bottom:1px solid var(--line);padding:14px 24px}
  .top-row{max-width:880px;margin:0 auto;display:flex;align-items:center;justify-content:space-between}
  .brand{font-weight:800;font-size:17px}
  .top-link{color:var(--muted);text-decoration:none;font-size:13px}

  .hero{max-width:760px;margin:0 auto;padding:40px 20px 12px;text-align:center}
  h1{margin:0 0 12px;font-size:30px;font-weight:900;letter-spacing:-.01em}
  .lede{color:var(--muted);font-size:15px}

  main{max-width:880px;margin:0 auto;padding:24px 20px}
  .grid{display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:30px}
  @media(max-width:680px){.grid{grid-template-columns:1fr}}
  .col{background:var(--bg);border:1px solid var(--line);border-radius:12px;padding:24px;box-shadow:0 1px 3px rgba(0,0,0,.03)}
  .col h2{margin-top:0;font-size:18px}

  .demo-box{border:1px dashed var(--line);border-radius:8px;padding:8px;background:var(--bg-2);margin-bottom:14px}

  .feat{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px;margin-top:20px}
  .feat-item{padding:14px 16px;background:var(--bg);border:1px solid var(--line);border-radius:10px;font-size:13px;color:var(--muted)}
  .feat-item strong{display:block;color:var(--text);margin-bottom:4px;font-weight:700}

  footer{padding:30px;text-align:center;color:var(--muted);font-size:12px}
</style>
</head>
<body>

<header class="top">
  <div class="top-row">
    <div class="brand"><span class="lat">TKAWEN</span> · Embed</div>
    <a href="/" class="top-link">→ الموقع الرئيسي</a>
  </div>
</header>

<section class="hero">
  <h1>أدوات TKAWEN على موقعك بـ iframe واحد</h1>
  <p class="lede">انسخ السطر التالي، الصقه في صفحتك، انتهى. يعمل على WordPress، Shopify، أي HTML.</p>
</section>

<main>

  <div class="col">
    <h2>1. لقطة البريد (Lead Capture)</h2>
    <p style="color:var(--muted);font-size:14px;margin-bottom:14px">شارة بسيطة تجمع البريد، تربطه تلقائيا بحملاتك. الزائر يضغط زر واحد، تصلك ال leads في dashboard.</p>

    <div class="demo-box">
      <iframe src="/embed/capture.html?theme=light&source=demo-light"
              width="100%" height="280" frameborder="0" loading="lazy"
              style="border:0;display:block;border-radius:6px;"></iframe>
    </div>

    <p style="font-weight:700;font-size:13px;margin:18px 0 8px">انسخ هذا الكود:</p>
    <pre><code>&lt;iframe
  src=<span class="hl">"https://tkawen.online/embed/capture.html?theme=light&source=YOUR-SITE"</span>
  width="100%" height="280" frameborder="0" loading="lazy"
  style="border:0;display:block;"&gt;
&lt;/iframe&gt;</code></pre>

    <p style="font-weight:700;font-size:13px;margin:18px 0 8px">خيارات (URL params):</p>
    <div class="feat">
      <div class="feat-item"><strong>theme</strong>light · dark</div>
      <div class="feat-item"><strong>source</strong>اسم موقعك (للإحصاء)</div>
      <div class="feat-item"><strong>headline</strong>عنوان مخصص</div>
      <div class="feat-item"><strong>desc</strong>وصف مخصص</div>
      <div class="feat-item"><strong>cta</strong>نص الزر</div>
      <div class="feat-item"><strong>target</strong>mystoq · liqaa</div>
    </div>
  </div>

  <div class="grid" style="margin-top:30px">
    <div class="col">
      <h2>مثال للموقع الداكن</h2>
      <iframe src="/embed/capture.html?theme=dark&source=demo-dark&headline=%D8%A7%D9%86%D8%B6%D9%85%20%D9%84%D8%B4%D8%A8%D9%83%D8%A9%20TKAWEN&cta=%D8%A7%D9%86%D8%B6%D9%85"
              width="100%" height="280" frameborder="0" loading="lazy" style="border:0;display:block"></iframe>
    </div>
    <div class="col">
      <h2>مثال للأخضر / mystoq</h2>
      <iframe src="/embed/capture.html?theme=light&source=demo-mystoq&headline=90%20%D9%8A%D9%88%D9%85%20%D9%85%D8%AC%D8%A7%D9%86%D8%A7&desc=%D9%85%D8%AA%D8%AC%D8%B1%20%D8%A5%D9%84%D9%83%D8%AA%D8%B1%D9%88%D9%86%D9%8A%20%D9%83%D8%A7%D9%85%D9%84&cta=%D8%AC%D8%B1%D8%A8%20%D8%A7%D9%84%D8%A2%D9%86"
              width="100%" height="300" frameborder="0" loading="lazy" style="border:0;display:block"></iframe>
    </div>
  </div>

  <div class="col">
    <h2>كل الـ leads تصلك في dashboard</h2>
    <p style="color:var(--muted);font-size:14px">عند كل اشتراك، يظهر في <a href="/intel/" style="color:var(--accent)">intel dashboard</a> مباشرة مع مصدر الزيارة (موقع الشريك). تعرف بالضبط من أين جاء كل بريد.</p>
  </div>

  <div class="col" style="margin-top:24px">
    <h2>الأمان والخصوصية</h2>
    <ul style="color:var(--muted);font-size:14px;line-height:2;padding-inline-start:22px">
      <li><strong>HTTPS فقط</strong> — لا يعمل على HTTP</li>
      <li><strong>Rate-limited</strong> — 5 محاولات في الدقيقة لكل زائر</li>
      <li><strong>Honeypot</strong> — يحارب bots تلقائيا</li>
      <li><strong>GDPR-friendly</strong> — لا تتبع غير ضروري</li>
      <li><strong>Origin-checked</strong> — يقبل فقط الـ origins الموثوقة</li>
    </ul>
  </div>

</main>

<footer>
  أدوات الإضافة من <a href="/" style="color:var(--muted)"><strong>TKAWEN</strong></a> — مجانية، مستقرة، مدعومة
</footer>

</body>
</html>
