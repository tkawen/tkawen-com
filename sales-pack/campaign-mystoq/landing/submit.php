<?php
// tkawen-invite — lead capture + redirect to MyStoq with pre-filled data.
// AGPL-3.0-or-later

declare(strict_types=1);
header_remove('X-Powered-By');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// ─── Capture ───────────────────────────────────────────────────
$lead = [
    'ts'         => date('c'),
    'ip'         => $_SERVER['REMOTE_ADDR'] ?? '',
    'user_id'    => trim((string)($_POST['user_id'] ?? '')),
    'src'        => trim((string)($_POST['src'] ?? '')),
    'name'       => trim((string)($_POST['name'] ?? '')),
    'email'      => filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL),
    'phone'      => trim((string)($_POST['phone'] ?? '')),
    'business'   => trim((string)($_POST['business'] ?? '')),
    'ua'         => $_SERVER['HTTP_USER_AGENT'] ?? '',
];

if (!filter_var($lead['email'], FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo 'Invalid email';
    exit;
}

// ─── Persist (JSON-lines, atomic append) ───────────────────────
$leads_file = __DIR__ . '/leads.jsonl';
@file_put_contents(
    $leads_file,
    json_encode($lead, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "\n",
    FILE_APPEND | LOCK_EX
);

// ─── Optional: notify the founder via simple webhook ───────────
// Configure: replace WEBHOOK_URL with your Slack/Discord/Telegram webhook
$WEBHOOK_URL = ''; // set me, e.g. https://hooks.slack.com/...
if ($WEBHOOK_URL) {
    $msg = sprintf(
        "🎉 New MyStoq lead from TKAWEN campaign\n• %s\n• %s\n• %s\n• Wants to sell: %s",
        $lead['name'],
        $lead['email'],
        $lead['phone'],
        $lead['business'] ?: '(unspecified)'
    );
    @file_get_contents(
        $WEBHOOK_URL,
        false,
        stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => "Content-Type: application/json\r\n",
                'content' => json_encode(['text' => $msg], JSON_UNESCAPED_UNICODE),
                'timeout' => 3,
            ],
        ])
    );
}

// ─── Redirect to MyStoq with pre-filled signup ─────────────────
$params = http_build_query([
    'email'   => $lead['email'],
    'name'    => $lead['name'],
    'phone'   => $lead['phone'],
    'plan'    => 'builder',
    'promo'   => 'TKAWEN90',
    'ref'     => 'tkawen-email-invite',
    'utm_source'   => 'tkawen.online',
    'utm_medium'   => 'email',
    'utm_campaign' => 'tkawen-to-mystoq-2026q2',
    'utm_content'  => $lead['user_id'],
]);

// MyStoq dashboard register URL — verified 2026-05-19
header('Location: https://mystoq.com/dashboard/register?' . $params, true, 302);
exit;
