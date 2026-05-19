<?php
/**
 * intel/login.php — single password gate for the intelligence dashboard.
 * Reads same .secret file the campaign sender uses (DASHBOARD_PASS=...).
 * Sets HttpOnly session cookie on success. No bcrypt — it's a single-user system.
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
        if ($line === '' || $line[0] === '#') continue;
        if (strpos($line, '=') !== false) {
            [$k, $v] = explode('=', $line, 2);
            $cfg[trim($k)] = trim($v);
        }
    }
    return $cfg;
}

$cfg = load_cfg();
$DASHBOARD_PASS = $cfg['DASHBOARD_PASS'] ?? '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pass = $_POST['pass'] ?? '';
    if ($DASHBOARD_PASS && hash_equals($DASHBOARD_PASS, $pass)) {
        $_SESSION['intel_auth'] = hash('sha256', $DASHBOARD_PASS . $_SERVER['HTTP_USER_AGENT'] ?? '');
        $_SESSION['intel_since'] = time();
        // Re-bind session cookie with strict params
        $params = session_get_cookie_params();
        setcookie(session_name(), session_id(), [
            'expires'  => time() + 86400 * 7,
            'path'     => '/intel/',
            'secure'   => true,
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
        header('Location: /intel/');
        exit;
    }
    $error = 'كلمة سر غير صحيحة';
    usleep(500000);
}
?><!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex,nofollow">
<title>TKAWEN Intel · دخول</title>
<style>
  * { box-sizing: border-box }
  body {
    margin: 0; min-height: 100vh;
    background: radial-gradient(ellipse at top, #1e293b 0%, #020617 100%);
    color: #e2e8f0; font-family: -apple-system, 'Segoe UI', system-ui, sans-serif;
    display: grid; place-items: center; padding: 24px;
  }
  .card {
    width: 100%; max-width: 380px;
    background: rgba(15, 23, 42, .8);
    border: 1px solid rgba(148, 163, 184, .15);
    border-radius: 16px; padding: 32px 28px;
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
  }
  .brand { text-align: center; margin-bottom: 24px }
  .brand-title {
    font-size: 13px; font-weight: 700; letter-spacing: .12em;
    color: #06b6d4; text-transform: uppercase;
  }
  .brand-sub { font-size: 22px; font-weight: 800; margin-top: 4px; color: #f8fafc }
  label { display: block; font-size: 12px; color: #94a3b8; margin-bottom: 6px }
  input {
    width: 100%; padding: 12px 14px; font-size: 15px;
    background: rgba(2, 6, 23, .6);
    border: 1px solid rgba(148, 163, 184, .2);
    color: #f8fafc; border-radius: 8px;
    outline: none; transition: border-color .15s;
  }
  input:focus { border-color: #06b6d4 }
  button {
    width: 100%; margin-top: 16px; padding: 13px;
    background: linear-gradient(135deg, #06b6d4, #0891b2);
    color: #fff; border: 0; border-radius: 8px;
    font-size: 15px; font-weight: 700; cursor: pointer;
    transition: transform .12s;
  }
  button:hover { transform: translateY(-1px) }
  .err {
    margin-top: 14px; padding: 10px 12px; border-radius: 6px;
    background: rgba(220, 38, 38, .12); border: 1px solid rgba(220, 38, 38, .3);
    color: #fca5a5; font-size: 13px; text-align: center;
  }
  .foot { margin-top: 18px; text-align: center; font-size: 11px; color: #475569 }
</style>
</head>
<body>
<div class="card">
  <div class="brand">
    <div class="brand-title">TKAWEN INTEL</div>
    <div class="brand-sub">قيادة المخابرات</div>
  </div>
  <form method="post">
    <label for="pass">كلمة السر</label>
    <input type="password" id="pass" name="pass" required autofocus autocomplete="current-password">
    <button type="submit">دخول</button>
    <?php if ($error): ?>
      <div class="err"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>
  </form>
  <div class="foot">جلسة آمنة لمدة 7 أيام</div>
</div>
</body>
</html>
