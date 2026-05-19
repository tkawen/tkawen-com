<?php
/**
 * id/verify.php — validates the magic link, sets HttpOnly SSO cookie scoped
 * to .tkawen.online so every subpath/subdomain can read it.
 *
 * Token format: base64url(email).expiry.nonce.hmac
 * Validates: signature with HMAC-SHA256 + expiry not in the past.
 */
declare(strict_types=1);
header_remove('X-Powered-By');

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

function b64url_decode(string $s): string|false { return base64_decode(strtr($s, '-_', '+/')); }
function b64url(string $s): string { return rtrim(strtr(base64_encode($s), '+/', '-_'), '='); }

$token = $_GET['t'] ?? '';
$ok = false;
$email = '';
$reason = '';

if ($token) {
    $parts = explode('.', $token);
    if (count($parts) === 4) {
        [$email_b, $exp, $nonce, $sig] = $parts;
        $payload = "$email_b.$exp.$nonce";
        $expected = b64url(hash_hmac('sha256', $payload, $SSO_SECRET, true));

        if (!hash_equals($expected, $sig)) {
            $reason = 'التوقيع غير صحيح';
        } elseif ((int)$exp < time()) {
            $reason = 'الرابط انتهت صلاحيته (10 دقائق فقط)';
        } else {
            $decoded_email = b64url_decode($email_b);
            if (!$decoded_email || !filter_var($decoded_email, FILTER_VALIDATE_EMAIL)) {
                $reason = 'البريد في الرابط غير صحيح';
            } else {
                $ok = true;
                $email = $decoded_email;

                // Issue 30-day session cookie — same signing scheme but 30-day TTL
                $session_exp = time() + 86400 * 30;
                $session_nonce = bin2hex(random_bytes(8));
                $session_payload = b64url($email) . '.' . $session_exp . '.' . $session_nonce;
                $session_sig = b64url(hash_hmac('sha256', $session_payload, $SSO_SECRET, true));
                $session_token = $session_payload . '.' . $session_sig;

                setcookie('tkawen_id', $session_token, [
                    'expires'  => $session_exp,
                    'path'     => '/',
                    'domain'   => '.tkawen.online',  // works across subpaths
                    'secure'   => true,
                    'httponly' => true,
                    'samesite' => 'Lax',
                ]);

                @file_put_contents(__DIR__ . '/sso.log',
                    date('c') . "\t" . 'login' . "\t" . $email . "\t" . ($_SERVER['REMOTE_ADDR'] ?? '') . "\n",
                    FILE_APPEND | LOCK_EX
                );
            }
        }
    } else {
        $reason = 'تنسيق الرابط غير صحيح';
    }
} else {
    $reason = 'لا يوجد رمز في الرابط';
}

// Redirect destination (optional ?next= param, default /)
$next = $_GET['next'] ?? '/';
if (!preg_match('#^/[a-zA-Z0-9_\-/]*$#', $next)) $next = '/';
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#020617">
<meta name="robots" content="noindex,nofollow">
<title><?= $ok ? '✓ تم الدخول' : '✗ فشل الدخول' ?> · TKAWEN ID</title>
<?php if ($ok): ?>
<meta http-equiv="refresh" content="2;url=<?= htmlspecialchars($next, ENT_QUOTES, 'UTF-8') ?>">
<?php endif; ?>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;700;800&display=swap" rel="stylesheet">
<style>
  *{box-sizing:border-box}body{margin:0;min-height:100vh;background:#020617;color:#f8fafc;
    font-family:'Cairo',sans-serif;direction:rtl;display:grid;place-items:center;padding:24px;-webkit-font-smoothing:antialiased}
  .card{width:100%;max-width:380px;text-align:center;padding:36px 28px;background:rgba(15,23,42,.7);border:1px solid rgba(148,163,184,.14);border-radius:16px;backdrop-filter:blur(20px)}
  .icon{width:64px;height:64px;border-radius:50%;display:grid;place-items:center;margin:0 auto 18px;font-size:30px}
  .icon.ok{background:rgba(16,185,129,.15);color:#10b981}
  .icon.err{background:rgba(244,63,94,.15);color:#f43f5e}
  h1{margin:0 0 8px;font-size:22px;font-weight:800}
  p{color:#94a3b8;font-size:14px;line-height:1.7;margin:0 0 18px}
  a{color:#06b6d4;text-decoration:none;font-weight:700;font-size:14px;display:inline-block;padding:10px 20px;background:rgba(6,182,212,.12);border-radius:8px}
  a:hover{background:rgba(6,182,212,.2)}
  .email{font-family:'JetBrains Mono',monospace;direction:ltr;color:#10b981;background:rgba(16,185,129,.08);padding:6px 12px;border-radius:6px;display:inline-block;font-size:13px;margin-bottom:14px}
</style>
</head>
<body>
<div class="card">
  <?php if ($ok): ?>
    <div class="icon ok">✓</div>
    <h1>تم الدخول</h1>
    <div class="email"><?= htmlspecialchars($email) ?></div>
    <p>سيتم تحويلك خلال ثانيتين…<br>أو اضغط للمتابعة</p>
    <a href="<?= htmlspecialchars($next, ENT_QUOTES, 'UTF-8') ?>">المتابعة ←</a>
  <?php else: ?>
    <div class="icon err">✗</div>
    <h1>فشل الدخول</h1>
    <p><?= htmlspecialchars($reason) ?></p>
    <a href="/id/login.php">طلب رابط جديد</a>
  <?php endif; ?>
</div>
</body>
</html>
