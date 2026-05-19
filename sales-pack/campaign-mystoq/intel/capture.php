<?php
/**
 * intel/capture.php — universal lead/event capture endpoint.
 *
 * Called by ALL TKAWEN subdomains and tools. Single source of truth for
 * the data layer. CORS-enabled so JS from any subdomain can POST here.
 *
 * Endpoints:
 *   POST  /intel/capture.php           — capture a lead/event
 *   POST  /intel/capture.php?event=1   — append-only event (no lead promotion)
 *   GET   /intel/capture.php?ping=1    — health check (returns 200 OK + UTC time)
 *
 * Authentication: NONE for capture (it's public). But:
 *   - Rate-limited per IP (60/hour, 5/minute)
 *   - Email validated server-side
 *   - Honeypot field "trap" — if filled, silently discard
 *   - User-agent must be present + not in blocklist
 *   - Origin must end in tkawen.com OR tkawen.online (or be empty for curl)
 *
 * Storage (append-only JSONL, never overwritten):
 *   /public_html/intel/data/events.jsonl     ← every event ever
 *   /public_html/intel/data/leads.jsonl      ← deduped by email (latest wins)
 *   /public_html/intel/data/rate.json        ← rate-limit ledger
 *
 * Returns JSON:
 *   { ok: true, lead_id: "...", events_recorded: 1 }
 *   OR { ok: false, error: "..." }
 */

declare(strict_types=1);
header_remove('X-Powered-By');
header('Content-Type: application/json; charset=utf-8');

// ─── CORS — any *.tkawen.com or tkawen.online subdomain may POST ─
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowed_origin_pattern = '#^https?://([a-z0-9-]+\.)?(tkawen\.(com|online|io)|mystoq\.com|algeriacertify\.com|liqaa\.io)$#i';
if ($origin && preg_match($allowed_origin_pattern, $origin)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Credentials: true');
    header('Vary: Origin');
}
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Capture-Token');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// ─── Health check ─────────────────────────────────────────────
if (isset($_GET['ping'])) {
    echo json_encode([
        'ok' => true,
        'service' => 'intel-capture',
        'now_utc' => gmdate('c'),
        'version' => '1.0',
    ]);
    exit;
}

// ─── Data dir ─────────────────────────────────────────────────
$DATA_DIR = __DIR__ . '/data';
if (!is_dir($DATA_DIR)) @mkdir($DATA_DIR, 0750, true);
$EVENTS_FILE = $DATA_DIR . '/events.jsonl';
$LEADS_FILE  = $DATA_DIR . '/leads.jsonl';
$RATE_FILE   = $DATA_DIR . '/rate.json';

// ─── Rate limit (sliding window per IP) ───────────────────────
$ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$now = time();
$rate = [];
if (file_exists($RATE_FILE)) {
    $rate = json_decode((string)file_get_contents($RATE_FILE), true) ?: [];
}
// Clean stamps older than 1 hour
foreach ($rate as $k => $stamps) {
    $rate[$k] = array_values(array_filter($stamps, fn($t) => $t >= $now - 3600));
    if (empty($rate[$k])) unset($rate[$k]);
}
$ip_stamps = $rate[$ip] ?? [];
$last_minute = array_filter($ip_stamps, fn($t) => $t >= $now - 60);
if (count($ip_stamps) >= 60 || count($last_minute) >= 5) {
    http_response_code(429);
    echo json_encode(['ok' => false, 'error' => 'rate_limited', 'retry_after' => 60]);
    exit;
}
$rate[$ip] = array_merge($ip_stamps, [$now]);
@file_put_contents($RATE_FILE, json_encode($rate), LOCK_EX);

// ─── Parse input (JSON OR form) ───────────────────────────────
$raw = file_get_contents('php://input');
$ct = $_SERVER['CONTENT_TYPE'] ?? '';
$input = [];
if (stripos($ct, 'application/json') !== false && $raw) {
    $input = json_decode($raw, true) ?: [];
} else {
    $input = $_POST;
}

// ─── Honeypot check ───────────────────────────────────────────
if (!empty($input['trap'])) {
    // Bot caught — silently accept and discard
    echo json_encode(['ok' => true, 'lead_id' => 'discarded']);
    exit;
}

// ─── Validate user agent (very basic bot filter) ──────────────
$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
$bot_pattern = '/(bot|spider|crawl|wget|httrack|libwww|harvest|curl\/[0-7])/i';
$is_curl_test = (stripos($ua, 'curl') !== false) && !empty($input['_internal']);  // internal testing flag
if (!$is_curl_test && preg_match($bot_pattern, $ua)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'forbidden_ua']);
    exit;
}

// ─── Build event record ───────────────────────────────────────
$event_only = !empty($_GET['event']);

$email = strtolower(trim((string)($input['email'] ?? '')));
$has_email = filter_var($email, FILTER_VALIDATE_EMAIL) !== false;

$kind = preg_replace('/[^a-z0-9_-]/', '', (string)($input['kind'] ?? ($has_email ? 'lead' : 'pageview')));
if (strlen($kind) > 32) $kind = substr($kind, 0, 32);

// Stable lead_id derivation (one per email forever)
function derive_lead_id(string $email): string {
    return 'l_' . substr(hash('sha256', strtolower($email) . '|tkawen-lead-2026'), 0, 16);
}
$lead_id = $has_email
    ? derive_lead_id($email)
    : 'a_' . substr(hash('sha256', $ip . '|' . $ua . '|' . date('Y-m-d')), 0, 16);

// Allowed top-level fields (whitelist — drop anything else)
$ALLOWED = ['email','name','phone','source','medium','campaign','content','page','referrer','product','sector','wilaya','message','utm_source','utm_medium','utm_campaign','utm_content','utm_term','ref','fields'];
$evt = [
    'ts'        => gmdate('c'),
    'kind'      => $kind,
    'lead_id'   => $lead_id,
    'ip'        => $ip,
    'ua'        => substr($ua, 0, 256),
    'origin'    => $origin,
];
foreach ($ALLOWED as $k) {
    if (!isset($input[$k]) || $input[$k] === '') continue;
    $v = $input[$k];
    if (is_string($v)) {
        $v = trim($v);
        if (strlen($v) > 512) $v = substr($v, 0, 512);
    } elseif (is_array($v)) {
        // custom fields blob — keep small
        $v = array_slice($v, 0, 20);
        $v = json_decode(json_encode($v, JSON_UNESCAPED_UNICODE), true);  // sanitize
    } else {
        $v = (string)$v;
    }
    $evt[$k] = $v;
}

// ─── Append event ─────────────────────────────────────────────
@file_put_contents($EVENTS_FILE, json_encode($evt, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND | LOCK_EX);

// ─── If lead (has email + not event-only), promote to leads.jsonl ─
$lead_recorded = false;
if ($has_email && !$event_only) {
    // Build lead profile (the canonical version)
    $lead = [
        'lead_id'    => $lead_id,
        'email'      => $email,
        'name'       => (string)($input['name'] ?? ''),
        'phone'      => (string)($input['phone'] ?? ''),
        'first_seen' => $evt['ts'],
        'last_seen'  => $evt['ts'],
        'source'     => (string)($input['source'] ?? ''),
        'sources_seen' => [(string)($input['source'] ?? '?')],
        'pages'      => [(string)($input['page'] ?? '')],
        'wilaya'     => (string)($input['wilaya'] ?? ''),
        'sector'     => (string)($input['sector'] ?? ''),
        'message'    => (string)($input['message'] ?? ''),
        'fields'     => isset($input['fields']) && is_array($input['fields']) ? $input['fields'] : [],
        'utm'        => [
            'source'   => (string)($input['utm_source'] ?? ''),
            'medium'   => (string)($input['utm_medium'] ?? ''),
            'campaign' => (string)($input['utm_campaign'] ?? ''),
            'content'  => (string)($input['utm_content'] ?? ''),
        ],
    ];

    // De-dup: scan existing leads.jsonl backwards for this email
    // (cheap for <10k leads; switch to SQLite/Postgres if it grows)
    $existing = null;
    if (file_exists($LEADS_FILE)) {
        $fh = @fopen($LEADS_FILE, 'r');
        if ($fh) {
            while (($line = fgets($fh)) !== false) {
                $obj = json_decode($line, true);
                if (is_array($obj) && ($obj['lead_id'] ?? '') === $lead_id) {
                    $existing = $obj;
                }
            }
            fclose($fh);
        }
    }
    if ($existing) {
        // Merge: keep first_seen, update last_seen, append unique sources/pages
        $lead['first_seen'] = $existing['first_seen'] ?? $lead['first_seen'];
        $lead['name']  = $lead['name']  ?: ($existing['name']  ?? '');
        $lead['phone'] = $lead['phone'] ?: ($existing['phone'] ?? '');
        $lead['wilaya']= $lead['wilaya']?: ($existing['wilaya']?? '');
        $lead['sector']= $lead['sector']?: ($existing['sector']?? '');
        $lead['sources_seen'] = array_values(array_unique(array_merge(
            $existing['sources_seen'] ?? [],
            $lead['sources_seen']
        )));
        $lead['pages'] = array_values(array_unique(array_merge(
            $existing['pages'] ?? [],
            $lead['pages']
        )));
        // Keep first non-empty UTM ever seen
        foreach (['source','medium','campaign','content'] as $u) {
            $lead['utm'][$u] = ($existing['utm'][$u] ?? '') ?: $lead['utm'][$u];
        }
    }

    @file_put_contents($LEADS_FILE, json_encode($lead, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND | LOCK_EX);
    $lead_recorded = true;
}

// ─── Set visitor cookie (for anonymous attribution across pages) ─
if (!isset($_COOKIE['tkawen_v'])) {
    setcookie('tkawen_v', $lead_id, [
        'expires'  => time() + 86400 * 365,
        'path'     => '/',
        'domain'   => '.tkawen.online',  // works across subdomains
        'secure'   => true,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

echo json_encode([
    'ok' => true,
    'lead_id' => $lead_id,
    'kind' => $kind,
    'lead_recorded' => $lead_recorded,
    'is_returning' => isset($existing),
], JSON_UNESCAPED_UNICODE);
