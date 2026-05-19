<?php
/**
 * campaign-send.php — server-side bulk sender using tkawen.online's SMTP.
 *
 * Why server-side: tkawen.online has 120k+ emails of warmed-up sender
 * reputation with the exact same audience we're targeting (they're already
 * registered users). Sending from here = inbox placement instead of spam.
 *
 * Actions (all require ?key=SECRET):
 *   verify   — sends 1 email to FOUNDER_EMAIL, confirms templates render
 *   batch    — sends N emails from a wave CSV, throttled, resumable
 *   status   — JSON: per-wave progress counts
 *
 * Wave CSV must be uploaded to /public_html/mystoq-invite/lists/<wave>.csv
 *   columns: user_id,email,first_name,last_name,registered_year[,has_active_session]
 *
 * Templates: variant filenames already on the server (uploaded earlier):
 *   template-A-personal.html, template-B-benefit.html, template-C-question.html
 *   followup-1-day3-bump.html, followup-2-day7-proof.html, followup-3-day14-final.html
 *
 * Safety:
 *   - Secret key required for every call
 *   - Founder email is whitelisted; any other "verify" recipient is REJECTED
 *   - Throttles to 1 email per N seconds (configurable, default 2s)
 *   - Suppresses anyone in opt-outs.log
 *   - Tracks progress in a per-wave .progress file (resumable)
 *   - Hourly cap enforced (300/hour by default — under cPanel limits)
 *   - Every send logged to send.jsonl with timestamp + result
 */

declare(strict_types=1);
error_reporting(E_ALL);
ini_set('display_errors', '1');
@set_time_limit(0);
@ignore_user_abort(true);

// ─── CONFIG ───────────────────────────────────────────────────────
// Secrets live in /public_html/mystoq-invite/.secret (KEY=VALUE per line).
// File never commits — gitignored. Rotate by overwriting on server.
$secret_file = __DIR__ . '/.secret';
if (!file_exists($secret_file)) {
    http_response_code(500);
    exit('Sender disabled: .secret file missing on server.');
}
$cfg = [];
foreach (explode("\n", trim((string)file_get_contents($secret_file))) as $line) {
    $line = trim($line);
    if ($line === '' || $line[0] === '#') continue;
    if (strpos($line, '=') === false) {
        // Backward compat: single-line file is the secret
        $cfg['SECRET'] = $line;
        continue;
    }
    [$k, $v] = explode('=', $line, 2);
    $cfg[trim($k)] = trim($v);
}
if (empty($cfg['SECRET'])) { http_response_code(500); exit('Sender disabled: SECRET missing.'); }
define('SECRET', $cfg['SECRET']);
define('FOUNDER_EMAIL', $cfg['FOUNDER_EMAIL'] ?? '');  // verify-only target
const SENDER_FROM = 'Yaakoub Hartem from TKAWEN <noreply@tkawen.online>';
const SENDER_ENVELOPE = 'noreply@tkawen.online';
const REPLY_TO = 'yaakoub@tkawen.com';
const HOURLY_CAP = 300;      // emails per rolling hour
const DEFAULT_DELAY_SECONDS = 2;

const ROOT_DIR = __DIR__;
const LISTS_DIR = __DIR__ . '/lists';
const LOG_FILE = __DIR__ . '/send.jsonl';
const OPT_OUTS_FILE = __DIR__ . '/opt-outs.log';
const RATE_LIMIT_FILE = __DIR__ . '/.rate-limit';

const VARIANT_MAP = [
    'A'   => ['file' => 'template-A-personal.html',   'subject' => '{name}، طريقة لتفتح مشروعك التجاريّ خلال 5 دقائق'],
    'B'   => ['file' => 'template-B-benefit.html',    'subject' => '90 يوم متجر إلكترونيّ مجاناً — لك فقط كعضو TKAWEN'],
    'C'   => ['file' => 'template-C-question.html',   'subject' => '{name}، هل فكّرتَ يوماً في بيع منتجات أونلاين؟'],
    'FU1' => ['file' => 'followup-1-day3-bump.html',  'subject' => '{name}، هل وصلتك رسالتي يوم الأحد؟'],
    'FU2' => ['file' => 'followup-2-day7-proof.html', 'subject' => '{name}، شاهد كيف فتحت سارة متجرها في 8 دقائق'],
    'FU3' => ['file' => 'followup-3-day14-final.html','subject' => '{name}، آخر يوم — العرض ينتهي اليوم منتصف الليل'],
];

// ─── AUTH ─────────────────────────────────────────────────────────
if (($_GET['key'] ?? '') !== SECRET) {
    http_response_code(403);
    exit('Forbidden');
}
header('Content-Type: application/json; charset=utf-8');
header_remove('X-Powered-By');

$action = $_GET['action'] ?? 'status';

// ─── HELPERS ──────────────────────────────────────────────────────
function jsonOut(array $data, int $code = 200): never {
    http_response_code($code);
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

function log_send(array $entry): void {
    $entry['ts'] = date('c');
    @file_put_contents(LOG_FILE, json_encode($entry, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND | LOCK_EX);
}

function load_opt_outs(): array {
    $ids = [];
    if (!file_exists(OPT_OUTS_FILE)) return $ids;
    $fh = @fopen(OPT_OUTS_FILE, 'r');
    if (!$fh) return $ids;
    while (($line = fgets($fh)) !== false) {
        $parts = explode("\t", trim($line));
        if (count($parts) >= 2) $ids[$parts[1]] = true;
    }
    fclose($fh);
    return $ids;
}

function rate_limit_check_and_record(int $count): bool {
    // Returns true if under cap, false if hourly cap reached.
    $now = time();
    $window_start = $now - 3600;
    $stamps = [];
    if (file_exists(RATE_LIMIT_FILE)) {
        $raw = @file_get_contents(RATE_LIMIT_FILE) ?: '';
        foreach (explode("\n", trim($raw)) as $ts) {
            $ts = (int) $ts;
            if ($ts >= $window_start) $stamps[] = $ts;
        }
    }
    if (count($stamps) + $count > HOURLY_CAP) return false;
    for ($i = 0; $i < $count; $i++) $stamps[] = $now;
    @file_put_contents(RATE_LIMIT_FILE, implode("\n", $stamps), LOCK_EX);
    return true;
}

function rate_limit_current(): int {
    if (!file_exists(RATE_LIMIT_FILE)) return 0;
    $raw = @file_get_contents(RATE_LIMIT_FILE) ?: '';
    $window_start = time() - 3600;
    $n = 0;
    foreach (explode("\n", trim($raw)) as $ts) {
        if ((int)$ts >= $window_start) $n++;
    }
    return $n;
}

function make_token(string $user_id, string $email): string {
    return substr(hash('sha256', $user_id . '|' . $email . '|tkawen-mystoq-2026q2'), 0, 24);
}

function render_template(string $variant, array $ctx): ?string {
    $cfg = VARIANT_MAP[$variant] ?? null;
    if (!$cfg) return null;
    $path = ROOT_DIR . '/' . $cfg['file'];
    if (!file_exists($path)) return null;
    $html = file_get_contents($path);
    foreach ($ctx as $k => $v) {
        $html = str_replace('{{' . $k . '}}', (string)$v, $html);
    }
    return $html;
}

function send_one(string $to, string $subject, string $html): bool {
    $boundary = 'tkawen-' . md5(uniqid('', true));
    $headers = implode("\r\n", [
        'From: ' . SENDER_FROM,
        'Reply-To: ' . REPLY_TO,
        'X-Mailer: TKAWEN-Campaign',
        'X-Campaign: tkawen-to-mystoq-2026q2',
        'List-Unsubscribe: <mailto:' . REPLY_TO . '?subject=Unsubscribe>',
        'Precedence: bulk',
        'MIME-Version: 1.0',
        'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
    ]);

    $subject_enc = '=?UTF-8?B?' . base64_encode($subject) . '?=';

    $body = "--{$boundary}\r\n"
          . "Content-Type: text/plain; charset=UTF-8\r\n"
          . "Content-Transfer-Encoding: 8bit\r\n\r\n"
          . "افتح هذا الإيميل في عميل بريد يدعم HTML لرؤية المحتوى الكامل.\r\n\r\n"
          . "--{$boundary}\r\n"
          . "Content-Type: text/html; charset=UTF-8\r\n"
          . "Content-Transfer-Encoding: 8bit\r\n\r\n"
          . $html . "\r\n\r\n"
          . "--{$boundary}--";

    return @mail($to, $subject_enc, $body, $headers, '-f' . SENDER_ENVELOPE);
}

function build_context(array $row, string $variant): array {
    $user_id = (string)($row['user_id'] ?? '');
    $email = (string)($row['email'] ?? '');
    $first_name = trim((string)($row['first_name'] ?? '')) ?: 'صديقي';
    $reg_year = (string)($row['registered_year'] ?? date('Y'));
    $token = make_token($user_id, $email);

    $base_params = http_build_query([
        'u' => $user_id, 'n' => $first_name, 'e' => $email,
        'y' => $reg_year, 't' => $token, 'v' => $variant,
    ]);

    return [
        'FIRST_NAME'   => $first_name,
        'REG_YEAR'     => $reg_year,
        // Direct click-to-signup via r.php (logs click, redirects to mystoq.com/dashboard/register
        // with email + promo + utm prefilled — no form friction).
        'LANDING_URL'  => 'https://tkawen.online/mystoq-invite/r.php?' . $base_params,
        'STORIES_URL'  => 'https://tkawen.online/mystoq-invite/stories/?' . $base_params,
        'UNSUB_URL'    => 'https://tkawen.online/mystoq-invite/unsubscribe.php?' . http_build_query(['u' => $user_id, 't' => $token]),
        'TRACK_PIXEL'  => 'https://tkawen.online/mystoq-invite/pixel.php?' . http_build_query(['u' => $user_id, 't' => $token, 'v' => $variant]),
        'EXPIRES_DATE' => date('d/m/Y', strtotime('+14 days')),
    ];
}

// ─── ACTION: verify ───────────────────────────────────────────────
if ($action === 'verify') {
    $variant = strtoupper($_GET['variant'] ?? 'A');
    if (!isset(VARIANT_MAP[$variant])) jsonOut(['error' => 'unknown variant', 'available' => array_keys(VARIANT_MAP)], 400);

    $to = $_GET['to'] ?? FOUNDER_EMAIL;
    if ($to !== FOUNDER_EMAIL) {
        jsonOut(['error' => 'verify only sends to founder email — refusing untrusted target', 'allowed' => FOUNDER_EMAIL], 403);
    }

    $name = $_GET['name'] ?? 'يعقوب';
    $ctx = build_context([
        'user_id' => 'verify-' . substr(md5($to), 0, 8),
        'email' => $to,
        'first_name' => $name,
        'registered_year' => '2024',
    ], $variant);

    $html = render_template($variant, $ctx);
    if ($html === null) jsonOut(['error' => 'template not found on server', 'variant' => $variant], 500);

    $subject = str_replace('{name}', $name, VARIANT_MAP[$variant]['subject']);
    $ok = send_one($to, $subject, $html);

    log_send([
        'action' => 'verify', 'variant' => $variant, 'to' => $to, 'subject' => $subject, 'success' => $ok,
    ]);

    jsonOut([
        'action' => 'verify',
        'variant' => $variant,
        'to' => $to,
        'subject' => $subject,
        'success' => $ok,
        'note' => $ok ? 'Sent. Check inbox + spam folder.' : 'mail() returned false.',
    ]);
}

// ─── ACTION: batch ────────────────────────────────────────────────
if ($action === 'batch') {
    $wave = preg_replace('/[^a-z0-9-]/i', '', $_GET['wave'] ?? '');
    if (!$wave) jsonOut(['error' => 'missing wave param'], 400);

    $variant = strtoupper($_GET['variant'] ?? 'A');
    if (!isset(VARIANT_MAP[$variant])) jsonOut(['error' => 'unknown variant'], 400);

    $limit = max(1, min(200, (int)($_GET['limit'] ?? 50)));
    $delay = max(1, min(10, (int)($_GET['delay'] ?? DEFAULT_DELAY_SECONDS)));
    $dry = isset($_GET['dry']) && $_GET['dry'] === '1';

    $list_file = LISTS_DIR . '/' . $wave . '.csv';
    if (!file_exists($list_file)) {
        jsonOut(['error' => 'wave list not found on server', 'expected_path' => $list_file], 404);
    }

    // Progress file: which row index are we at for this wave + variant
    $progress_key = $wave . '-' . $variant;
    $progress_file = ROOT_DIR . '/.progress-' . $progress_key;
    $start_index = file_exists($progress_file) ? (int)file_get_contents($progress_file) : 0;

    // Hourly cap pre-check
    if (rate_limit_current() >= HOURLY_CAP) {
        jsonOut([
            'error' => 'hourly cap reached',
            'cap' => HOURLY_CAP,
            'current' => rate_limit_current(),
            'retry_in_seconds' => 3600,
        ], 429);
    }

    $opt_outs = load_opt_outs();

    $fh = fopen($list_file, 'r');
    if (!$fh) jsonOut(['error' => 'cannot open wave list'], 500);

    $header = fgetcsv($fh);
    if (!$header) jsonOut(['error' => 'wave list is empty'], 500);
    $col = array_flip($header);

    // Skip to start_index
    $row_idx = 0;
    while ($row_idx < $start_index && fgetcsv($fh) !== false) $row_idx++;

    $sent = 0; $failed = 0; $skipped = 0;
    $results_sample = [];

    while ($sent < $limit && ($row = fgetcsv($fh)) !== false) {
        $row_idx++;

        $assoc = [];
        foreach ($header as $i => $h) $assoc[$h] = $row[$i] ?? '';

        $email = strtolower(trim((string)($assoc['email'] ?? '')));
        $user_id = (string)($assoc['user_id'] ?? '');

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { $skipped++; continue; }
        if (isset($opt_outs[$user_id])) { $skipped++; continue; }

        $ctx = build_context($assoc, $variant);
        $html = render_template($variant, $ctx);
        if ($html === null) { $failed++; continue; }

        $name = $ctx['FIRST_NAME'];
        $subject = str_replace('{name}', $name, VARIANT_MAP[$variant]['subject']);

        // Re-check hourly cap before each send
        if (!rate_limit_check_and_record(1)) {
            // Hit the cap mid-batch — save progress, return
            file_put_contents($progress_file, $row_idx - 1);
            break;
        }

        if ($dry) {
            $ok = true;
        } else {
            $ok = send_one($email, $subject, $html);
        }
        if ($ok) $sent++; else $failed++;

        if (count($results_sample) < 3) {
            $results_sample[] = [
                'to' => $email,
                'name' => $name,
                'subject' => $subject,
                'ok' => $ok,
            ];
        }

        log_send([
            'action' => $dry ? 'batch-dry' : 'batch', 'variant' => $variant, 'wave' => $wave,
            'user_id' => $user_id, 'email' => $email, 'success' => $ok,
        ]);

        if (!$dry && $delay > 0) sleep($delay);
    }
    fclose($fh);

    file_put_contents($progress_file, $row_idx);

    jsonOut([
        'action' => $dry ? 'batch-dry' : 'batch',
        'wave' => $wave,
        'variant' => $variant,
        'limit_requested' => $limit,
        'sent' => $sent,
        'failed' => $failed,
        'skipped' => $skipped,
        'progress_at_row' => $row_idx,
        'hourly_used' => rate_limit_current(),
        'hourly_cap' => HOURLY_CAP,
        'results_sample' => $results_sample,
        'next_call' => 'Re-invoke with same params to continue from row ' . $row_idx,
    ]);
}

// ─── ACTION: status ───────────────────────────────────────────────
if ($action === 'status') {
    $waves = [];
    foreach (glob(LISTS_DIR . '/*.csv') as $f) {
        $name = basename($f, '.csv');
        $lines = 0;
        $fh = fopen($f, 'r');
        if ($fh) { while (fgets($fh) !== false) $lines++; fclose($fh); }
        $waves[$name] = ['rows_in_list' => max(0, $lines - 1)];
    }
    foreach (glob(ROOT_DIR . '/.progress-*') as $f) {
        $key = basename($f);
        $progress = (int)file_get_contents($f);
        $waves[$key] = ['progress_row' => $progress];
    }

    $log_lines = 0; $log_sent = 0;
    if (file_exists(LOG_FILE)) {
        $fh = fopen(LOG_FILE, 'r');
        while (($line = fgets($fh)) !== false) {
            $log_lines++;
            $obj = json_decode($line, true);
            if ($obj && ($obj['success'] ?? false)) $log_sent++;
        }
        fclose($fh);
    }

    jsonOut([
        'waves' => $waves,
        'log_entries' => $log_lines,
        'log_successful_sends' => $log_sent,
        'hourly_used' => rate_limit_current(),
        'hourly_cap' => HOURLY_CAP,
        'opt_outs_count' => count(load_opt_outs()),
    ]);
}

jsonOut(['error' => 'unknown action', 'valid_actions' => ['verify', 'batch', 'status']], 400);
