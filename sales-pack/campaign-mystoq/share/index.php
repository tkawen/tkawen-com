<?php
/**
 * share.tkawen.online — referral landing.
 *
 * URL pattern:  /share/?r=<referral_code>
 * Behaviors:
 *   1. Sets cookie tkawen_ref=<code> (30-day, .tkawen.online scope)
 *   2. Logs the referral visit to intel/data/events.jsonl
 *   3. Shows the referred-by-X welcome page
 *   4. Redirects to /try/ after a confirmation
 *
 * Each TKAWEN user gets their own referral code (derived from their
 * lead_id) accessible at /share/dashboard.php after login.
 */
declare(strict_types=1);
header_remove('X-Powered-By');

$ref_raw = $_GET['r'] ?? $_GET['code'] ?? '';
$ref = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $ref_raw));

$referrer_name = '';
$valid = false;

if ($ref && strlen($ref) >= 4 && strlen($ref) <= 16) {
    $valid = true;

    // Set referral cookie (30-day, cross-subdomain)
    setcookie('tkawen_ref', $ref, [
        'expires'  => time() + 86400 * 30,
        'path'     => '/',
        'domain'   => '.tkawen.online',
        'secure'   => true,
        'httponly' => false,  // JS can read for analytics
        'samesite' => 'Lax',
    ]);

    // Log to intel
    @file_put_contents(__DIR__ . '/../intel/data/events.jsonl',
        json_encode([
            'ts' => gmdate('c'),
            'kind' => 'referral_visit',
            'lead_id' => 'a_' . substr(hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . '|' . ($_SERVER['HTTP_USER_AGENT'] ?? '')), 0, 16),
            'source' => 'share',
            'fields' => ['ref_code' => $ref, 'referrer' => $_SERVER['HTTP_REFERER'] ?? ''],
        ], JSON_UNESCAPED_UNICODE) . "\n",
        FILE_APPEND | LOCK_EX
    );
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#020617">
<title>دعوة من صديق · TKAWEN</title>
<meta name="robots" content="noindex,nofollow">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700;800;900&display=swap" rel="stylesheet">

<style>
  :root{--bg:#020617;--card:rgba(15,23,42,.72);--border:rgba(148,163,184,.14);
    --text:#f8fafc;--muted:#94a3b8;--green:#10b981;--cyan:#06b6d4;--purple:#a855f7}
  *{box-sizing:border-box}
  body{margin:0;min-height:100vh;background:
    radial-gradient(ellipse 80% 50% at 50% -20%,rgba(168,85,247,.15),transparent 60%),
    radial-gradient(ellipse 60% 40% at 50% 110%,rgba(6,182,212,.1),transparent 60%),
    var(--bg);color:var(--text);font-family:'Cairo',sans-serif;direction:rtl;
    display:grid;place-items:center;padding:24px;-webkit-font-smoothing:antialiased}
  .lat{font-family:'JetBrains Mono','SF Mono',monospace;direction:ltr;unicode-bidi:embed}

  .card{width:100%;max-width:440px;background:var(--card);border:1px solid var(--border);
    border-radius:20px;padding:40px 32px;backdrop-filter:blur(20px);text-align:center}

  .pulse{margin:0 auto 14px;width:60px;height:60px;border-radius:50%;
    background:linear-gradient(135deg,var(--purple),var(--cyan));
    display:grid;place-items:center;font-size:32px;animation:pulse 2.5s infinite}
  @keyframes pulse{0%,100%{transform:scale(1);box-shadow:0 0 0 0 rgba(168,85,247,.5)}50%{transform:scale(1.05);box-shadow:0 0 0 20px rgba(168,85,247,0)}}

  .tag{display:inline-block;font-size:11px;font-weight:700;letter-spacing:.12em;
    color:var(--purple);background:rgba(168,85,247,.12);padding:5px 12px;border-radius:999px;margin-bottom:14px;text-transform:uppercase}

  h1{margin:0 0 12px;font-size:24px;font-weight:900;letter-spacing:-.01em;line-height:1.35}
  .lede{color:var(--muted);font-size:14px;line-height:1.7;margin-bottom:24px}

  .reward{background:linear-gradient(135deg,rgba(16,185,129,.12),rgba(6,182,212,.08));
    border:1px solid rgba(16,185,129,.3);border-radius:14px;padding:18px 20px;margin-bottom:20px}
  .reward-h{font-size:11px;color:var(--green);font-weight:800;letter-spacing:.08em;margin-bottom:8px;text-transform:uppercase}
  .reward-list{text-align:right;font-size:14px;line-height:2;color:var(--text)}
  .reward-list .num{display:inline-block;width:24px;height:24px;border-radius:50%;background:rgba(16,185,129,.2);color:var(--green);
    font-family:'JetBrains Mono',monospace;font-weight:800;font-size:13px;text-align:center;line-height:24px;margin-inline-end:8px;vertical-align:middle}

  .cta{display:block;padding:14px;background:linear-gradient(135deg,var(--green),#059669);
    color:#fff;text-decoration:none;border-radius:10px;font-size:15px;font-weight:800;
    transition:transform .15s}
  .cta:hover{transform:translateY(-1px)}
  .meta{margin-top:12px;font-size:11px;color:var(--muted)}

  .ref-code{display:inline-block;margin-top:14px;padding:4px 10px;background:rgba(255,255,255,.06);
    border-radius:6px;font-family:'JetBrains Mono',monospace;font-size:11px;color:var(--muted);direction:ltr}

  .invalid{color:#f43f5e;font-size:13px;margin-top:14px}
</style>
</head>
<body>
<div class="card">
  <?php if ($valid): ?>
    <div class="pulse">🎁</div>
    <span class="tag">دعوة خاصة</span>
    <h1>صديقك دعاك إلى TKAWEN!</h1>
    <p class="lede">احصل على عرض حصري — 90 يوما من MyStoq + إعداد كامل، مجانا.</p>

    <div class="reward">
      <div class="reward-h">ما تحصل عليه أنت</div>
      <div class="reward-list">
        <div><span class="num">1</span>متجر إلكتروني كامل مجانا 90 يوما</div>
        <div><span class="num">2</span>جلسة إعداد شخصية معنا</div>
        <div><span class="num">3</span>+10 منتجات + قالب جاهز</div>
      </div>
    </div>

    <a href="/try/?p=mystoq&ref=<?= htmlspecialchars($ref, ENT_QUOTES) ?>&utm_source=referral&utm_medium=share&utm_campaign=referral-program" class="cta">
      ابدأ الآن &nbsp;←
    </a>
    <div class="meta">بدون بطاقة بنكية · 3 دقائق</div>
    <div class="ref-code">REF: <?= htmlspecialchars($ref, ENT_QUOTES) ?></div>
  <?php else: ?>
    <div class="pulse">⚠️</div>
    <h1>رمز الدعوة غير صحيح</h1>
    <p class="lede">الرابط الذي اتبعته لا يحتوي على رمز إحالة صالح. لا مشكلة — تستطيع الانضمام مباشرة.</p>
    <a href="/try/" class="cta">جرب MyStoq مجانا ←</a>
  <?php endif; ?>
</div>

<script async src="/intel/track.js" data-source="share-landing"></script>
</body>
</html>
