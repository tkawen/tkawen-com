<?php
// tkawen → mystoq invite landing — personalised, brand-aligned, mobile-first.
// AGPL-3.0-or-later

declare(strict_types=1);
header_remove('X-Powered-By');

// ─── Pre-fill from URL ─────────────────────────────────────────
$user_id    = isset($_GET['u']) ? preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['u']) : '';
$first_name = isset($_GET['n']) ? htmlspecialchars(substr($_GET['n'], 0, 50), ENT_QUOTES, 'UTF-8') : '';
$email      = isset($_GET['e']) ? filter_var($_GET['e'], FILTER_SANITIZE_EMAIL) : '';
$year       = isset($_GET['y']) ? preg_replace('/[^0-9]/', '', substr($_GET['y'], 0, 4)) : date('Y');

// Tracking pixel — log the visit
@file_put_contents(
    __DIR__ . '/visits.log',
    sprintf("%s\t%s\t%s\t%s\n", date('c'), $user_id, $email, $_SERVER['HTTP_USER_AGENT'] ?? ''),
    FILE_APPEND | LOCK_EX
);

$greeting = $first_name !== '' ? "{$first_name}، حان وقت متجرك" : 'حان وقت متجرك';

// ─── HTML ──────────────────────────────────────────────────────
?><!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<meta name="theme-color" content="#020617">
<title>عرض خاصّ لأعضاء TKAWEN — متجرك الإلكترونيّ مع MyStoq</title>
<meta name="description" content="بصفتك جزءاً من عائلة TKAWEN، احصل على 90 يوماً مجاناً على MyStoq + إعداد متجرك معك شخصياً + WhatsApp Commerce مفعَّل.">
<meta name="robots" content="noindex, nofollow"><!-- private campaign page -->
<link rel="icon" type="image/svg+xml" href="/favicon.svg">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&family=Inter:wght@500;600;700;800&display=swap">

<meta property="og:title" content="عرض خاصّ لأعضاء TKAWEN — متجرك مع MyStoq">
<meta property="og:description" content="90 يوم مجاناً + إعداد كامل + WhatsApp Commerce">
<meta property="og:image" content="https://raw.githubusercontent.com/tkawen/tkawen-com/main/assets/og.png">

<style>
:root{
  --bg:#020617;--bg-elev:#0f172a;--card:#1e293b;
  --ink:#1d4ed8;--back-1:#3b82f6;--back-2:#1d4ed8;--back-3:#0c1a3d;
  --front-1:#93c5fd;--front-2:#60a5fa;--front-3:#1e3a8a;
  --mystoq-1:#1d4ed8;--mystoq-2:#67e8f9;
  --white:#f8fafc;--dim:#94a3b8;--faint:#64748b;
  --green:#10b981;--border:rgba(255,255,255,.08);--border-s:rgba(255,255,255,.16);
}
*{box-sizing:border-box;margin:0;padding:0}
html,body{background:var(--bg);color:var(--white);font-family:'Cairo',system-ui,sans-serif;line-height:1.6;-webkit-font-smoothing:antialiased;overflow-x:hidden}
.wrap{max-width:720px;margin:0 auto;padding:clamp(32px,6vw,64px) clamp(20px,5vw,32px)}

.brand{display:flex;align-items:center;gap:12px;justify-content:center;margin-bottom:48px}
.brand-mark{width:36px;height:36px}
.brand-name{font-weight:800;font-size:18px;letter-spacing:-.02em}

.hero{text-align:center;margin-bottom:48px}
.eyebrow{display:inline-flex;align-items:center;gap:8px;padding:6px 14px;border-radius:999px;background:rgba(59,130,246,.10);border:1px solid rgba(59,130,246,.30);color:var(--front-1);font-size:13px;font-weight:600;margin-bottom:24px}
.eyebrow .dot{width:6px;height:6px;border-radius:50%;background:var(--green);box-shadow:0 0 8px var(--green);animation:pulse 2s ease-in-out infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}

h1{font-size:clamp(28px,6vw,46px);font-weight:900;line-height:1.15;letter-spacing:-.02em;margin-bottom:16px}
h1 .accent{background:linear-gradient(135deg,var(--front-1),var(--front-2),var(--back-1));-webkit-background-clip:text;background-clip:text;color:transparent}
.subtitle{font-size:clamp(16px,2vw,18px);color:var(--dim);max-width:560px;margin:0 auto}

.offer-card{background:linear-gradient(180deg,rgba(59,130,246,.08),rgba(59,130,246,.02));border:1px solid rgba(59,130,246,.30);border-radius:18px;padding:32px clamp(20px,4vw,36px);margin-bottom:32px}
.offer-title{font-size:13px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;color:var(--front-2);margin-bottom:14px}
.offer-items{list-style:none}
.offer-items li{display:flex;align-items:flex-start;gap:12px;padding:10px 0;font-size:15px;color:var(--white);line-height:1.5}
.offer-items li:first-child{padding-top:0}.offer-items li:last-child{padding-bottom:0}
.offer-items .check{display:grid;place-items:center;width:22px;height:22px;border-radius:50%;background:rgba(16,185,129,.18);color:var(--green);flex-shrink:0;margin-top:2px;font-weight:900;font-size:14px}
.offer-items b{color:var(--white);font-weight:800}
.offer-items s{color:var(--faint);text-decoration:line-through;margin-inline-start:4px}

.cta-block{text-align:center;margin-bottom:40px}
.btn-primary{display:inline-flex;align-items:center;justify-content:center;gap:10px;padding:18px 36px;border-radius:999px;background:linear-gradient(135deg,var(--back-1),var(--back-2));color:#fff;font-weight:700;font-size:17px;text-decoration:none;box-shadow:0 12px 36px -10px rgba(59,130,246,.6),inset 0 1px 0 rgba(255,255,255,.2);transition:transform 180ms ease,box-shadow 180ms ease;margin-bottom:16px;border:0;font-family:inherit;cursor:pointer}
.btn-primary:hover{transform:translateY(-2px);box-shadow:0 18px 48px -10px rgba(59,130,246,.75)}
.btn-arrow{display:inline-block;transition:transform 200ms}
.btn-primary:hover .btn-arrow{transform:translateX(-4px)}
.cta-note{font-size:13px;color:var(--faint);margin-top:8px}

.form{background:var(--bg-elev);border:1px solid var(--border-s);border-radius:14px;padding:28px;margin-bottom:32px}
.form-row{margin-bottom:18px}
.form-row label{display:block;font-size:13px;font-weight:600;color:var(--dim);margin-bottom:6px}
.form-row input{display:block;width:100%;padding:14px 16px;border-radius:10px;background:var(--card);border:1px solid var(--border-s);color:var(--white);font-size:15px;font-family:inherit}
.form-row input:focus{outline:none;border-color:var(--back-1);box-shadow:0 0 0 3px rgba(59,130,246,.15)}
.form-row input:disabled{background:#0a0e1a;color:var(--dim);cursor:not-allowed}
.form-hint{font-size:12px;color:var(--faint);margin-top:6px}

.alt-wa{display:flex;align-items:center;justify-content:center;gap:8px;color:var(--dim);font-size:14px;margin:24px 0;padding-top:24px;border-top:1px dashed var(--border)}
.alt-wa a{color:var(--green);font-weight:700;text-decoration:none}
.alt-wa a:hover{text-decoration:underline}

.proof{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin:32px 0}
.proof-item{text-align:center;padding:18px 12px;background:var(--bg-elev);border:1px solid var(--border);border-radius:12px}
.proof-num{font-size:22px;font-weight:900;color:var(--white);letter-spacing:-.02em;margin-bottom:4px}
.proof-label{font-size:12px;color:var(--dim);line-height:1.3}

footer{text-align:center;color:var(--faint);font-size:12px;padding-top:32px;border-top:1px solid var(--border);margin-top:48px}
footer a{color:var(--dim)}
.legal{margin-top:10px;font-size:11px;color:var(--faint);line-height:1.6}

@media(max-width:520px){
  .proof{grid-template-columns:1fr}
  .proof-num{font-size:20px}
  .form{padding:22px}
}
</style>
</head>
<body>
<div class="wrap">

  <div class="brand">
    <svg class="brand-mark" viewBox="0 0 40 40" xmlns="http://www.w3.org/2000/svg" fill="none" aria-label="TKAWEN">
      <defs>
        <linearGradient id="b1" x1="0" y1="0" x2="40" y2="40" gradientUnits="userSpaceOnUse">
          <stop offset="0%" stop-color="#3b82f6"/><stop offset="50%" stop-color="#1d4ed8"/><stop offset="100%" stop-color="#0c1a3d"/>
        </linearGradient>
        <linearGradient id="b2" x1="0" y1="0" x2="40" y2="40" gradientUnits="userSpaceOnUse">
          <stop offset="0%" stop-color="#60a5fa"/><stop offset="50%" stop-color="#3b82f6"/><stop offset="100%" stop-color="#1e40af"/>
        </linearGradient>
        <linearGradient id="b3" x1="0" y1="0" x2="40" y2="40" gradientUnits="userSpaceOnUse">
          <stop offset="0%" stop-color="#93c5fd"/><stop offset="50%" stop-color="#60a5fa"/><stop offset="100%" stop-color="#1e3a8a"/>
        </linearGradient>
      </defs>
      <rect x="2" y="20" width="18" height="18" rx="4.5" fill="url(#b1)"/>
      <rect x="11" y="11" width="18" height="18" rx="4.5" fill="url(#b2)"/>
      <rect x="20" y="2" width="18" height="18" rx="4.5" fill="url(#b3)"/>
    </svg>
    <span class="brand-name">TKAWEN × MyStoq</span>
  </div>

  <section class="hero">
    <div class="eyebrow">
      <span class="dot"></span>
      <span>عرض خاصّ بأعضاء TKAWEN منذ <?= $year ?></span>
    </div>
    <h1><?= htmlspecialchars($greeting, ENT_QUOTES, 'UTF-8') ?></h1>
    <h1 class="accent" style="margin-top:8px">افتح متجرك في 5 دقائق.</h1>
    <p class="subtitle" style="margin-top:18px">
      MyStoq منصّة متاجر إلكترونيّة جزائريّة كاملة — Yalidine + CTM + EDAHABIA + CIB مدمَجة. اليوم +200 تاجر يبيعون عليها. لك أنت عرض لا يحصل عليه أحد آخر.
    </p>
  </section>

  <div class="offer-card">
    <div class="offer-title">ما تحصل عليه (عضو TKAWEN فقط)</div>
    <ul class="offer-items">
      <li><span class="check">✓</span><span><b>90 يوماً مجاناً</b> على Builder plan <s>(الجمهور يحصل على 60 فقط)</s></span></li>
      <li><span class="check">✓</span><span><b>إعداد المتجر معك شخصياً</b> — مكالمة 1 ساعة على LIQAA Meet</span></li>
      <li><span class="check">✓</span><span><b>WhatsApp Commerce مفعَّل</b> — bot يردّ على زبائنك تلقائياً (قيمة 5,000 DZD مجاناً)</span></li>
      <li><span class="check">✓</span><span><b>قالب جاهز + 10 منتجات seeded</b> — من اختيارك (Beauty, Pharma, Electronics, …)</span></li>
      <li><span class="check">✓</span><span><b>WhatsApp مباشر معي</b> لأوّل 30 يوم — أيّ سؤال، أيّ وقت</span></li>
    </ul>
  </div>

  <form class="form" action="submit.php" method="post">
    <input type="hidden" name="user_id" value="<?= htmlspecialchars($user_id, ENT_QUOTES, 'UTF-8') ?>">
    <input type="hidden" name="src" value="email-tkawen-invite">

    <div class="form-row">
      <label for="name">اسمك الكامل</label>
      <input id="name" name="name" type="text" required placeholder="مثال: محمد بن أحمد" value="<?= $first_name ?>">
    </div>

    <div class="form-row">
      <label for="email">بريدك الإلكترونيّ</label>
      <input id="email" name="email" type="email" required value="<?= htmlspecialchars($email, ENT_QUOTES, 'UTF-8') ?>" <?= $email ? 'readonly' : '' ?>>
      <?php if ($email): ?><div class="form-hint">مُسجَّل لديك في TKAWEN — لا حاجة للتغيير</div><?php endif; ?>
    </div>

    <div class="form-row">
      <label for="phone">رقم WhatsApp <span style="color:var(--front-2)">(لا أراسلك إلّا عند الضرورة)</span></label>
      <input id="phone" name="phone" type="tel" required placeholder="+213 5XX XXX XXX" pattern="[\+]?[0-9\s]{9,}">
    </div>

    <div class="form-row">
      <label for="business">ماذا تريد أن تبيع؟ <span style="color:var(--front-2)">(اختياريّ)</span></label>
      <input id="business" name="business" type="text" placeholder="مثال: مستحضرات تجميل، ملابس، منتجات أكل صحّيّة...">
    </div>

    <div class="cta-block" style="margin-bottom:0">
      <button type="submit" class="btn-primary">
        احفظ حسابي المجاني الآن
        <span class="btn-arrow">←</span>
      </button>
      <div class="cta-note">بلا بطاقة بنكيّة. بلا التزام. 90 يوماً مجاناً تبدأ فوراً.</div>
    </div>
  </form>

  <div class="alt-wa">
    تفضّل WhatsApp؟ <a href="https://wa.me/213XXXXXXXXX?text=مرحبا%20يعقوب%2C%20أريد%20فتح%20متجر%20على%20MyStoq%20%28عضو%20TKAWEN%29">راسلني مباشرة</a>
  </div>

  <div class="proof">
    <div class="proof-item">
      <div class="proof-num">+200</div>
      <div class="proof-label">تاجر LIVE اليوم</div>
    </div>
    <div class="proof-item">
      <div class="proof-num">5</div>
      <div class="proof-label">دقائق لفتح متجرك</div>
    </div>
    <div class="proof-item">
      <div class="proof-num">99.9%</div>
      <div class="proof-label">uptime SLA</div>
    </div>
  </div>

  <footer>
    <div>عرض ساري لأعضاء TKAWEN فقط · صالح حتّى <?= date('d/m/Y', strtotime('+14 days')) ?></div>
    <div class="legal">
      TKAWEN — Annaba, Algeria · D-U-N-S 353551313<br>
      <a href="https://mystoq.com">mystoq.com</a> · <a href="https://tkawen.online">tkawen.online</a> · <a href="mailto:DIRECTION@takawen.dz">DIRECTION@takawen.dz</a><br>
      <span style="opacity:.6">إن لم ترغب في تلقّي رسائل مستقبليّة، رُدّ على الإيميل بكلمة «نسي» وأزيلك فوراً.</span>
    </div>
  </footer>

</div>
</body>
</html>
