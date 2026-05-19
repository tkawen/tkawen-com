<?php
/**
 * try.tkawen.online (mounted at /try/) — universal product signup portal.
 *
 * Picks a product, captures email + name + phone + wilaya, fires the lead
 * to intel/capture.php, then redirects to the right product signup with
 * everything pre-filled.
 *
 * URL params (all optional):
 *   ?p=mystoq|liqaa|certify|all   — pre-select product
 *   ?email=...                    — pre-fill email
 *   ?name=...                     — pre-fill name
 *   ?ref=...                      — referral attribution
 *   ?utm_source / utm_medium / utm_campaign / utm_content
 */
declare(strict_types=1);
header_remove('X-Powered-By');

$pre_product = preg_replace('/[^a-z]/', '', strtolower($_GET['p'] ?? ''));
$pre_email   = filter_var($_GET['email'] ?? '', FILTER_VALIDATE_EMAIL) ?: '';
$pre_name    = htmlspecialchars(substr($_GET['name'] ?? '', 0, 50), ENT_QUOTES, 'UTF-8');
$ref         = htmlspecialchars(substr($_GET['ref'] ?? '', 0, 32), ENT_QUOTES, 'UTF-8');
$utm_source  = htmlspecialchars(substr($_GET['utm_source'] ?? '', 0, 50), ENT_QUOTES, 'UTF-8');
$utm_medium  = htmlspecialchars(substr($_GET['utm_medium'] ?? '', 0, 50), ENT_QUOTES, 'UTF-8');
$utm_campaign= htmlspecialchars(substr($_GET['utm_campaign'] ?? '', 0, 80), ENT_QUOTES, 'UTF-8');
$utm_content = htmlspecialchars(substr($_GET['utm_content'] ?? '', 0, 80), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#020617">
<meta name="description" content="ابدأ مع TKAWEN في 30 ثانية — اختر منتجك واحصل على وصول مجاني فوري">
<meta name="robots" content="index,follow">
<title>TKAWEN · ابدأ في 30 ثانية</title>

<!-- OpenGraph -->
<meta property="og:title" content="TKAWEN · ابدأ في 30 ثانية">
<meta property="og:description" content="اختر منتجك واحصل على وصول مجاني فوري">
<meta property="og:type" content="website">
<meta property="og:locale" content="ar_DZ">
<meta property="og:url" content="https://tkawen.online/try/">

<link rel="canonical" href="https://tkawen.online/try/">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">

<style>
  :root {
    --bg: #020617;
    --bg-2: #0f172a;
    --card: rgba(15, 23, 42, .72);
    --border: rgba(148, 163, 184, .14);
    --border-hi: rgba(6, 182, 212, .4);
    --text: #f8fafc;
    --muted: #94a3b8;
    --dim: #475569;
    --cyan: #06b6d4;
    --green: #10b981;
    --amber: #f59e0b;
    --purple: #a855f7;
    --rose: #f43f5e;
  }
  * { box-sizing: border-box }
  body {
    margin: 0;
    min-height: 100vh;
    background:
      radial-gradient(ellipse 80% 50% at 20% -20%, rgba(6, 182, 212, .12), transparent 60%),
      radial-gradient(ellipse 60% 40% at 90% 110%, rgba(168, 85, 247, .08), transparent 60%),
      var(--bg);
    color: var(--text);
    font-family: 'Cairo', -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
    direction: rtl; text-align: right;
    -webkit-font-smoothing: antialiased;
    line-height: 1.65;
    padding-bottom: 60px;
  }
  .lat { font-family: 'JetBrains Mono', 'SF Mono', Consolas, monospace; direction: ltr; unicode-bidi: embed }

  /* Top brand */
  .topbar { padding: 18px 24px; display: flex; align-items: center; justify-content: space-between }
  .brand {
    font-weight: 900; font-size: 18px; letter-spacing: -.02em;
    display: flex; align-items: center; gap: 8px;
  }
  .brand .dot { width: 8px; height: 8px; border-radius: 50%; background: var(--green); box-shadow: 0 0 12px var(--green) }
  .brand .lat { font-weight: 800 }
  .top-link { color: var(--muted); font-size: 13px; text-decoration: none }
  .top-link:hover { color: var(--text) }

  /* Hero */
  .hero { padding: 24px 24px 12px; text-align: center; max-width: 720px; margin: 0 auto }
  .eyebrow {
    display: inline-block; padding: 6px 14px;
    background: rgba(6, 182, 212, .12); color: var(--cyan);
    border-radius: 999px; font-size: 11px; font-weight: 700;
    letter-spacing: .08em; margin-bottom: 16px;
  }
  h1 {
    font-size: 34px; font-weight: 900; line-height: 1.25;
    letter-spacing: -.02em; margin: 0 0 14px;
    background: linear-gradient(135deg, #f8fafc 0%, #94a3b8 100%);
    -webkit-background-clip: text; background-clip: text;
    -webkit-text-fill-color: transparent;
  }
  .lede { color: var(--muted); font-size: 15px; max-width: 480px; margin: 0 auto }

  /* Product grid */
  .products {
    max-width: 920px; margin: 32px auto 24px; padding: 0 16px;
    display: grid; gap: 12px;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
  }
  .product {
    background: var(--card);
    border: 2px solid var(--border);
    border-radius: 14px;
    padding: 20px 18px;
    cursor: pointer;
    transition: all .2s;
    text-align: right;
    position: relative;
    overflow: hidden;
  }
  .product:hover { border-color: var(--border-hi); transform: translateY(-2px) }
  .product.selected {
    border-color: var(--cyan);
    background: linear-gradient(135deg, rgba(6, 182, 212, .12), var(--card));
    box-shadow: 0 0 0 4px rgba(6, 182, 212, .08);
  }
  .product input[type=radio] { position: absolute; opacity: 0; pointer-events: none }
  .product .p-icon {
    width: 40px; height: 40px; border-radius: 10px;
    background: rgba(6, 182, 212, .12);
    display: grid; place-items: center;
    font-size: 22px; margin-bottom: 12px;
  }
  .product.selected .p-icon { background: var(--cyan); color: #fff }
  .product .p-name { font-weight: 800; font-size: 16px; margin-bottom: 4px }
  .product .p-desc { color: var(--muted); font-size: 12px; line-height: 1.55 }
  .product .p-tag {
    position: absolute; top: 14px; left: 14px;
    padding: 2px 8px; border-radius: 4px;
    font-size: 10px; font-weight: 700; letter-spacing: .04em;
    background: rgba(16, 185, 129, .15); color: var(--green);
  }
  .product.selected .p-tag { background: var(--green); color: #fff }

  /* Form */
  .form {
    max-width: 480px; margin: 0 auto; padding: 0 16px;
  }
  .form-card {
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 14px;
    padding: 24px;
  }
  .form-row { margin-bottom: 14px }
  .form-row.row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px }
  label { display: block; font-size: 12px; color: var(--muted); margin-bottom: 6px; font-weight: 600 }
  label .req { color: var(--rose); margin-inline-start: 4px }
  input, select {
    width: 100%; padding: 12px 14px; font-size: 15px;
    background: rgba(2, 6, 23, .6);
    border: 1px solid var(--border);
    color: var(--text); border-radius: 8px;
    outline: none; transition: border-color .15s;
    font-family: 'Cairo', sans-serif;
  }
  input:focus, select:focus { border-color: var(--cyan) }
  input.lat-input { font-family: 'JetBrains Mono', monospace; direction: ltr; text-align: left }
  /* Honeypot — hide from humans but bots fill it */
  .trap { position: absolute; left: -9999px; visibility: hidden }

  /* Submit */
  .submit-btn {
    width: 100%; margin-top: 18px; padding: 15px;
    background: linear-gradient(135deg, var(--cyan), #0891b2);
    color: #fff; border: 0; border-radius: 10px;
    font-size: 16px; font-weight: 800; cursor: pointer;
    transition: all .15s;
    font-family: 'Cairo', sans-serif;
  }
  .submit-btn:hover { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(6, 182, 212, .3) }
  .submit-btn:disabled { opacity: .5; cursor: not-allowed }

  .trust-row {
    margin-top: 14px; display: flex; justify-content: center; gap: 18px;
    font-size: 11px; color: var(--dim);
  }
  .trust-row span::before { content: '✓ '; color: var(--green) }

  /* Status message */
  .msg {
    margin-top: 16px; padding: 12px 14px; border-radius: 8px;
    font-size: 13px; line-height: 1.6; display: none;
  }
  .msg.ok { background: rgba(16, 185, 129, .12); border: 1px solid rgba(16, 185, 129, .3); color: #6ee7b7; display: block }
  .msg.err { background: rgba(244, 63, 94, .12); border: 1px solid rgba(244, 63, 94, .3); color: #fda4af; display: block }

  /* Bottom: alt products + counter */
  .meta-bar {
    max-width: 720px; margin: 28px auto 0; padding: 0 16px;
    text-align: center; font-size: 12px; color: var(--dim);
  }

  @media (max-width: 600px) {
    h1 { font-size: 26px }
    .topbar { padding: 14px 16px }
    .form-row.row-2 { grid-template-columns: 1fr }
  }
</style>
</head>
<body>

<header class="topbar">
  <div class="brand">
    <span class="dot"></span>
    <span class="lat">TKAWEN</span>
  </div>
  <a href="https://tkawen.online" class="top-link">→ الموقع الرئيسي</a>
</header>

<section class="hero">
  <span class="eyebrow">ابدأ في 30 ثانية</span>
  <h1>اختر منتجك من TKAWEN</h1>
  <p class="lede">منصة جزائرية متكاملة — اختر ما يهمك ونحن نجهز لك كل شيء.</p>
</section>

<form id="signup" class="form">

  <!-- Product picker -->
  <div class="products" role="radiogroup" aria-label="اختر منتجك">
    <label class="product <?= $pre_product === 'mystoq' ? 'selected' : '' ?>">
      <input type="radio" name="product" value="mystoq" <?= $pre_product === 'mystoq' || !$pre_product ? 'checked' : '' ?>>
      <span class="p-tag">90 يوم مجانا</span>
      <div class="p-icon">🛍️</div>
      <div class="p-name">MyStoq</div>
      <div class="p-desc">متجر إلكتروني كامل · WhatsApp · Yalidine · Edahabia</div>
    </label>

    <label class="product <?= $pre_product === 'liqaa' ? 'selected' : '' ?>">
      <input type="radio" name="product" value="liqaa">
      <span class="p-tag">مجاني</span>
      <div class="p-icon">🎥</div>
      <div class="p-name">LIQAA Meet</div>
      <div class="p-desc">اجتماعات فيديو · بديل Zoom · مفتوح المصدر</div>
    </label>

    <label class="product <?= $pre_product === 'certify' ? 'selected' : '' ?>">
      <input type="radio" name="product" value="certify">
      <span class="p-tag">من 0 دج</span>
      <div class="p-icon">📜</div>
      <div class="p-name">AlgeriaCertify</div>
      <div class="p-desc">شهادات تكوينية · تحقق فوري · بلوكتشين</div>
    </label>

    <label class="product <?= $pre_product === 'all' ? 'selected' : '' ?>">
      <input type="radio" name="product" value="all">
      <span class="p-tag">الكل</span>
      <div class="p-icon">🚀</div>
      <div class="p-name">جميع المنتجات</div>
      <div class="p-desc">حساب موحد · فاتورة واحدة · دخول واحد</div>
    </label>
  </div>

  <!-- Email + Name + Phone -->
  <div class="form-card">
    <div class="form-row">
      <label for="email">البريد الإلكتروني <span class="req">*</span></label>
      <input type="email" id="email" name="email" class="lat-input" required autocomplete="email"
             value="<?= htmlspecialchars($pre_email, ENT_QUOTES, 'UTF-8') ?>"
             placeholder="you@example.com">
    </div>

    <div class="form-row row-2">
      <div>
        <label for="name">الاسم</label>
        <input type="text" id="name" name="name" autocomplete="given-name"
               value="<?= $pre_name ?>" placeholder="يعقوب">
      </div>
      <div>
        <label for="phone">الهاتف</label>
        <input type="tel" id="phone" name="phone" class="lat-input" autocomplete="tel"
               pattern="0[567][0-9]{8}" placeholder="0555 123 456">
      </div>
    </div>

    <div class="form-row">
      <label for="wilaya">الولاية</label>
      <select id="wilaya" name="wilaya">
        <option value="">— اختر ولايتك (اختياري) —</option>
        <?php $wilayas = [
          1=>'أدرار',2=>'الشلف',3=>'الأغواط',4=>'أم البواقي',5=>'باتنة',6=>'بجاية',7=>'بسكرة',8=>'بشار',
          9=>'البليدة',10=>'البويرة',11=>'تمنراست',12=>'تبسة',13=>'تلمسان',14=>'تيارت',15=>'تيزي وزو',
          16=>'الجزائر',17=>'الجلفة',18=>'جيجل',19=>'سطيف',20=>'سعيدة',21=>'سكيكدة',22=>'سيدي بلعباس',
          23=>'عنابة',24=>'قالمة',25=>'قسنطينة',26=>'المدية',27=>'مستغانم',28=>'المسيلة',29=>'معسكر',
          30=>'ورقلة',31=>'وهران',32=>'البيض',33=>'إليزي',34=>'برج بوعريريج',35=>'بومرداس',36=>'الطارف',
          37=>'تندوف',38=>'تيسمسيلت',39=>'الوادي',40=>'خنشلة',41=>'سوق أهراس',42=>'تيبازة',43=>'ميلة',
          44=>'عين الدفلى',45=>'النعامة',46=>'عين تموشنت',47=>'غرداية',48=>'غليزان',
        ]; foreach ($wilayas as $id => $name): ?>
          <option value="<?= $id ?>"><?= $id ?> · <?= $name ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <!-- Honeypot — hidden from users -->
    <input type="text" name="trap" class="trap" tabindex="-1" autocomplete="off" aria-hidden="true">

    <!-- Attribution (hidden) -->
    <input type="hidden" name="ref" value="<?= $ref ?>">
    <input type="hidden" name="utm_source" value="<?= $utm_source ?: 'try-portal' ?>">
    <input type="hidden" name="utm_medium" value="<?= $utm_medium ?: 'direct' ?>">
    <input type="hidden" name="utm_campaign" value="<?= $utm_campaign ?>">
    <input type="hidden" name="utm_content" value="<?= $utm_content ?>">

    <button type="submit" class="submit-btn" id="submit-btn">
      افتح حسابي مجانا ←
    </button>

    <div class="trust-row">
      <span>بدون بطاقة بنكية</span>
      <span>إلغاء أي وقت</span>
      <span>دعم بالعربية</span>
    </div>

    <div class="msg" id="msg"></div>
  </div>
</form>

<div class="meta-bar">
  انضممت بعد <strong style="color:var(--text);"><span id="count-spot">3,827</span></strong> عضو في عائلة TKAWEN
</div>

<script>
  // Product selection
  document.querySelectorAll('.product').forEach(p => {
    p.addEventListener('click', () => {
      document.querySelectorAll('.product').forEach(x => x.classList.remove('selected'));
      p.classList.add('selected');
      p.querySelector('input').checked = true;
    });
  });

  // Product redirect targets (with promo + autofill)
  const PRODUCT_URLS = {
    mystoq:  'https://mystoq.com/dashboard/register',
    liqaa:   'https://liqaa.io/register',
    certify: 'https://algeriacertify.com/register',
    all:     'https://tkawen.online/dashboard',
  };
  const PRODUCT_NAMES = {
    mystoq: 'MyStoq', liqaa: 'LIQAA', certify: 'AlgeriaCertify', all: 'الحساب الموحد',
  };

  const form = document.getElementById('signup');
  const msg = document.getElementById('msg');
  const btn = document.getElementById('submit-btn');

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    msg.className = 'msg';
    msg.textContent = '';
    btn.disabled = true;
    btn.textContent = 'جار الإعداد…';

    const fd = new FormData(form);
    const product = fd.get('product') || 'mystoq';
    const payload = {
      email: fd.get('email'),
      name: fd.get('name'),
      phone: fd.get('phone'),
      wilaya: fd.get('wilaya'),
      product: product,
      source: 'try-portal',
      page: location.pathname,
      ref: fd.get('ref'),
      utm_source: fd.get('utm_source'),
      utm_medium: fd.get('utm_medium'),
      utm_campaign: fd.get('utm_campaign'),
      utm_content: fd.get('utm_content'),
      trap: fd.get('trap'),
      kind: 'try_signup',
    };

    try {
      const r = await fetch('/intel/capture.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        credentials: 'include',
        body: JSON.stringify(payload),
      });
      const data = await r.json();

      if (!data.ok) {
        msg.className = 'msg err';
        msg.textContent = data.error === 'rate_limited' ? 'كثير من المحاولات — انتظر دقيقة ثم حاول مجددا' : 'حدث خطأ. حاول مرة أخرى.';
        btn.disabled = false; btn.textContent = 'افتح حسابي مجانا ←';
        return;
      }

      msg.className = 'msg ok';
      msg.textContent = '✓ تم! نحولك الآن لصفحة ' + PRODUCT_NAMES[product] + '…';

      // Build redirect URL with full attribution
      const params = new URLSearchParams({
        email: payload.email,
        name: payload.name || '',
        promo: product === 'mystoq' ? 'TKAWEN90' : '',
        promo_code: product === 'mystoq' ? 'TKAWEN90' : '',
        utm_source: 'tkawen-try',
        utm_medium: 'signup-portal',
        utm_campaign: payload.utm_campaign || 'try-portal-2026q2',
        utm_content: data.lead_id,
        ref: payload.ref || '',
      });
      // Drop empty params
      for (const [k, v] of [...params.entries()]) if (!v) params.delete(k);

      setTimeout(() => {
        location.href = PRODUCT_URLS[product] + '?' + params.toString();
      }, 800);
    } catch (err) {
      msg.className = 'msg err';
      msg.textContent = 'خطأ في الاتصال. تحقق من الإنترنت وحاول مجددا.';
      btn.disabled = false; btn.textContent = 'افتح حسابي مجانا ←';
    }
  });

  // Fire pageview event (anonymous, no email yet)
  fetch('/intel/capture.php?event=1', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    credentials: 'include',
    body: JSON.stringify({
      kind: 'pageview',
      source: 'try-portal',
      page: '/try/',
      referrer: document.referrer,
      utm_source: '<?= $utm_source ?>',
      utm_medium: '<?= $utm_medium ?>',
      utm_campaign: '<?= $utm_campaign ?>',
    }),
  }).catch(() => {});
</script>

</body>
</html>
