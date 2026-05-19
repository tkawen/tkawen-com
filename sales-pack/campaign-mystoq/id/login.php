<?php
/**
 * id.tkawen.online — magic-link SSO entry page.
 *
 * Flow:
 *   1. User enters email
 *   2. Server signs a token (HMAC) tying email + expiry + nonce
 *   3. Server emails the link: https://tkawen.online/id/verify.php?t=<token>
 *   4. User clicks link
 *   5. verify.php validates, sets HttpOnly cookie scoped .tkawen.online
 *   6. Other apps read the cookie via /id/me.php (JSON: who are you?)
 *
 * The token has structure:  base64url(email).expiry.nonce.hmac
 * Signed by SSO_SECRET from .secret file. No DB needed — stateless tokens.
 */
declare(strict_types=1);
header_remove('X-Powered-By');
session_start();

const SECRET_FILE = __DIR__ . '/../mystoq-invite/.secret';
function load_cfg(): array {
    if (!file_exists(SECRET_FILE)) return [];
    $cfg = [];
    foreach (explode("\n", trim((string)file_get_contents(SECRET_FILE))) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) continue;
        [$k, $v] = explode('=', $line, 2);
        $cfg[trim($k)] = trim($v);
    }
    return $cfg;
}

$cfg = load_cfg();
$SSO_SECRET = $cfg['SSO_SECRET'] ?? ($cfg['SECRET'] ?? 'INSECURE-CHANGE-ME');

function b64url(string $s): string { return rtrim(strtr(base64_encode($s), '+/', '-_'), '='); }
function b64url_decode(string $s): string|false { return base64_decode(strtr($s, '-_', '+/')); }

function sign_token(string $email, string $secret, int $ttl = 600): string {
    $exp = time() + $ttl;
    $nonce = bin2hex(random_bytes(8));
    $payload = b64url($email) . '.' . $exp . '.' . $nonce;
    $sig = b64url(hash_hmac('sha256', $payload, $secret, true));
    return $payload . '.' . $sig;
}

$msg = '';
$msg_kind = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim((string)($_POST['email'] ?? '')));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = 'بريد إلكتروني غير صحيح';
        $msg_kind = 'err';
    } else {
        // Rate limit: 3 magic links per email per hour
        $rate_file = __DIR__ . '/.rate-' . md5($email);
        $stamps = [];
        if (file_exists($rate_file)) {
            $stamps = array_filter(
                array_map('intval', explode("\n", (string)file_get_contents($rate_file))),
                fn($t) => $t > time() - 3600
            );
        }
        if (count($stamps) >= 3) {
            $msg = 'انتظر دقيقة وحاول مجددا (تم إرسال 3 روابط في الساعة الأخيرة)';
            $msg_kind = 'err';
        } else {
            $token = sign_token($email, $SSO_SECRET);
            $link = 'https://tkawen.online/id/verify.php?t=' . $token;
            $name = explode('@', $email)[0];

            // Send the magic link
            $subject_enc = '=?UTF-8?B?' . base64_encode('🔐 رابط الدخول إلى TKAWEN') . '?=';
            $body = "السلام عليكم،\n\n"
                . "اضغط الرابط التالي لتسجيل الدخول إلى TKAWEN. الرابط يعمل لـ 10 دقائق فقط:\n\n"
                . $link . "\n\n"
                . "إن لم تكن أنت من طلب هذا الرابط، تجاهل هذه الرسالة.\n\n"
                . "— TKAWEN\n";
            $headers = "From: TKAWEN <noreply@tkawen.online>\r\n"
                . "Reply-To: yaakoub@tkawen.com\r\n"
                . "X-Mailer: TKAWEN-SSO\r\n"
                . "MIME-Version: 1.0\r\n"
                . "Content-Type: text/plain; charset=UTF-8\r\n"
                . "Content-Transfer-Encoding: 8bit\r\n";
            @mail($email, $subject_enc, $body, $headers, '-fnoreply@tkawen.online');

            $stamps[] = time();
            @file_put_contents($rate_file, implode("\n", $stamps), LOCK_EX);

            // Fire event to intel
            @file_put_contents(__DIR__ . '/sso.log',
                date('c') . "\t" . 'magic_link_sent' . "\t" . $email . "\n",
                FILE_APPEND | LOCK_EX
            );

            $msg = 'تم إرسال رابط الدخول إلى ' . htmlspecialchars($email) . '. تحقق من بريدك (والـ Promotions/Spam).';
            $msg_kind = 'ok';
        }
    }
}

// If already logged in, show me
$logged_in = null;
$cookie = $_COOKIE['tkawen_id'] ?? '';
if ($cookie) {
    $parts = explode('.', $cookie);
    if (count($parts) === 4) {
        [$email_b, $exp, $nonce, $sig] = $parts;
        $payload = "$email_b.$exp.$nonce";
        $expected = b64url(hash_hmac('sha256', $payload, $SSO_SECRET, true));
        if (hash_equals($expected, $sig) && (int)$exp > time()) {
            $logged_in = ['email' => b64url_decode($email_b), 'exp' => (int)$exp];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#020617">
<meta name="robots" content="noindex,nofollow">
<title>TKAWEN · دخول موحد</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;800;900&display=swap" rel="stylesheet">
<style>
  :root { --bg:#020617; --card:rgba(15,23,42,.7); --border:rgba(148,163,184,.14); --text:#f8fafc; --muted:#94a3b8; --cyan:#06b6d4; --green:#10b981; --rose:#f43f5e }
  *{box-sizing:border-box}
  body{margin:0;min-height:100vh;background:radial-gradient(ellipse 80% 50% at 50% -20%,rgba(6,182,212,.12),transparent 60%),var(--bg);
       color:var(--text);font-family:'Cairo',-apple-system,sans-serif;direction:rtl;display:grid;place-items:center;padding:24px;-webkit-font-smoothing:antialiased}
  .lat{font-family:'JetBrains Mono','SF Mono',monospace;direction:ltr;unicode-bidi:embed}

  .card{width:100%;max-width:400px;background:var(--card);border:1px solid var(--border);border-radius:16px;padding:36px 28px;backdrop-filter:blur(20px)}
  .brand{text-align:center;margin-bottom:24px}
  .brand-tag{font-size:11px;font-weight:700;letter-spacing:.12em;color:var(--cyan);text-transform:uppercase;display:block;margin-bottom:8px}
  .brand-h{font-size:24px;font-weight:900;letter-spacing:-.01em}
  .brand-sub{color:var(--muted);font-size:13px;margin-top:6px}

  label{display:block;font-size:12px;color:var(--muted);margin-bottom:6px;font-weight:600}
  input[type=email]{width:100%;padding:13px 14px;font-size:16px;background:rgba(2,6,23,.6);
    border:1px solid var(--border);color:var(--text);border-radius:10px;outline:none;
    font-family:'JetBrains Mono',monospace;direction:ltr;text-align:left}
  input:focus{border-color:var(--cyan)}
  button{width:100%;margin-top:14px;padding:13px;background:linear-gradient(135deg,var(--cyan),#0891b2);
    color:#fff;border:0;border-radius:10px;font-size:15px;font-weight:700;cursor:pointer;font-family:'Cairo',sans-serif;transition:transform .15s}
  button:hover{transform:translateY(-1px)}

  .msg{margin-top:14px;padding:11px 14px;border-radius:8px;font-size:13px;line-height:1.6}
  .msg.ok{background:rgba(16,185,129,.12);border:1px solid rgba(16,185,129,.3);color:#6ee7b7}
  .msg.err{background:rgba(244,63,94,.12);border:1px solid rgba(244,63,94,.3);color:#fda4af}

  .logged-in{text-align:center;padding:20px 0}
  .logged-in .who{font-size:15px;font-weight:700;margin-bottom:6px;color:var(--green)}
  .logged-in .email{font-family:'JetBrains Mono',monospace;font-size:13px;color:var(--muted);margin-bottom:18px}
  .logged-in a{display:inline-block;background:rgba(244,63,94,.12);color:#fda4af;padding:8px 16px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600}

  .help{margin-top:18px;font-size:12px;color:var(--muted);text-align:center;line-height:1.7}
  .help span{display:block}

  .apps-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;margin-top:20px;padding-top:20px;border-top:1px solid var(--border)}
  .app-icon{padding:10px 8px;background:rgba(255,255,255,.03);border-radius:8px;text-align:center;font-size:11px;color:var(--muted)}
  .app-icon span{display:block;font-size:18px;margin-bottom:4px}
</style>
</head>
<body>
<div class="card">
  <div class="brand">
    <span class="brand-tag lat">TKAWEN ID</span>
    <div class="brand-h">دخول موحد</div>
    <div class="brand-sub">حساب واحد لكل خدمات TKAWEN</div>
  </div>

  <?php if ($logged_in): ?>
    <div class="logged-in">
      <div class="who">✓ أنت مسجل دخول</div>
      <div class="email"><?= htmlspecialchars($logged_in['email']) ?></div>
      <a href="logout.php">تسجيل خروج</a>
    </div>
  <?php else: ?>
    <form method="post" autocomplete="on">
      <label for="email">بريدك الإلكتروني</label>
      <input type="email" id="email" name="email" required autofocus autocomplete="email" placeholder="you@example.com">
      <button type="submit">أرسل رابط الدخول ←</button>
      <?php if ($msg): ?>
        <div class="msg <?= $msg_kind ?>"><?= $msg ?></div>
      <?php endif; ?>
    </form>
    <div class="help">
      <span>لا حاجة لكلمة سر — سنرسل رابط دخول مباشر إلى بريدك</span>
      <span>الرابط يعمل لـ 10 دقائق</span>
    </div>
  <?php endif; ?>

  <div class="apps-grid">
    <div class="app-icon"><span>🛍️</span>MyStoq</div>
    <div class="app-icon"><span>🎥</span>LIQAA</div>
    <div class="app-icon"><span>📜</span>Certify</div>
    <div class="app-icon"><span>📊</span>Intel</div>
  </div>
</div>

<script async src="/intel/track.js" data-source="id-login"></script>
</body>
</html>
