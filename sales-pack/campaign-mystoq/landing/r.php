<?php
/**
 * r.php — click-tracking redirect.
 *
 * Email/WA links point HERE instead of the form. We log the click then
 * 302 straight to mystoq.com/dashboard/register with email + promo + UTMs
 * pre-filled. Skips the friction of filling a form twice.
 *
 * Inputs (all optional except u + e):
 *   ?u = user_id
 *   ?n = first_name
 *   ?e = email (passed to mystoq for pre-fill)
 *   ?y = registered_year
 *   ?t = token (anti-spoofing — derived in send.py)
 *   ?v = variant (A/B/C/FU1/FU2/FU3 — for A/B attribution)
 */

declare(strict_types=1);
header_remove('X-Powered-By');

$user_id    = preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['u'] ?? '');
$first_name = htmlspecialchars(substr($_GET['n'] ?? '', 0, 50), ENT_QUOTES, 'UTF-8');
$email      = filter_var($_GET['e'] ?? '', FILTER_VALIDATE_EMAIL) ?: '';
$year       = preg_replace('/[^0-9]/', '', substr($_GET['y'] ?? '', 0, 4));
$token      = preg_replace('/[^a-zA-Z0-9]/', '', substr($_GET['t'] ?? '', 0, 32));
$variant    = preg_replace('/[^A-Z0-9]/', '', strtoupper(substr($_GET['v'] ?? '', 0, 4)));

// Log the click
@file_put_contents(
    __DIR__ . '/visits.log',
    sprintf("%s\t%s\t%s\t%s\t%s\t%s\n", date('c'), $user_id, $email, $variant, $token, $_SERVER['HTTP_USER_AGENT'] ?? ''),
    FILE_APPEND | LOCK_EX
);

// Build the target URL with full attribution + auto-fill
$target = 'https://mystoq.com/dashboard/register?' . http_build_query([
    'email'         => $email,
    'promo'         => 'TKAWEN90',
    'promo_code'    => 'TKAWEN90',  // also pass as form field name for backend
    'name'          => $first_name,  // pre-fill if mystoq supports
    'utm_source'    => 'tkawen.online',
    'utm_medium'    => 'email',
    'utm_campaign'  => 'tkawen-to-mystoq-2026q2',
    'utm_content'   => $variant,
    'ref'           => $user_id,
]);

// Anti-cache (these are personalised redirects)
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Location: ' . $target, true, 302);
exit;
