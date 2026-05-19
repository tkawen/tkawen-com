<?php
// One-click unsubscribe — appends user_id to opt-outs.log.
// Sender script will filter against this before each send.

declare(strict_types=1);
header_remove('X-Powered-By');

$user_id = isset($_GET['u']) ? preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['u']) : '';
$token   = isset($_GET['t']) ? preg_replace('/[^a-zA-Z0-9]/', '', substr($_GET['t'], 0, 32)) : '';

if ($user_id) {
    @file_put_contents(
        __DIR__ . '/opt-outs.log',
        sprintf("%s\t%s\t%s\n", date('c'), $user_id, $token),
        FILE_APPEND | LOCK_EX
    );
}
?><!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>تمّ إلغاء الاشتراك</title>
<style>
body{font-family:'Cairo',system-ui,sans-serif;background:#020617;color:#f8fafc;margin:0;padding:80px 24px;text-align:center;line-height:1.6}
.wrap{max-width:480px;margin:0 auto}
.icon{width:64px;height:64px;border-radius:50%;background:rgba(100,116,139,.2);color:#94a3b8;display:grid;place-items:center;margin:0 auto 24px;font-size:32px}
h1{font-size:24px;font-weight:800;margin-bottom:12px}
p{color:#94a3b8;font-size:15px}
</style>
</head>
<body>
<div class="wrap">
  <div class="icon">✓</div>
  <h1>تمّ إلغاء اشتراكك</h1>
  <p>لن تتلقّى منّا رسائل تسويقيّة بعد الآن. شكراً على وقتك.</p>
  <p style="margin-top:24px;font-size:12px;color:#64748b">TKAWEN · Annaba, Algeria</p>
</div>
</body>
</html>
