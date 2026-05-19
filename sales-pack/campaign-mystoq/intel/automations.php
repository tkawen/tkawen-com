<?php
/**
 * intel/automations.php — heuristic-based automation rules.
 *
 * Runs as a cron job OR called on-demand from the dashboard.
 * Reads events.jsonl + leads.jsonl, applies rules, writes alerts.jsonl
 * which the dashboard surfaces in the "AI suggestions" panel.
 *
 * Each rule is a closure: array of lead_records => array of alerts.
 * Stateless — re-running is idempotent (alerts have a stable ID per
 * (rule, lead_id) so dups merge).
 *
 * Schedule: add to crontab on VPS:
 *   *\/5 * * * *  curl -s "https://tkawen.online/intel/automations.php?run=1&key=SECRET"
 * Or just hit it from a browser when you want a fresh run.
 */
declare(strict_types=1);
header_remove('X-Powered-By');

// Auth: either logged-in dashboard session OR ?key= matches CRON_KEY
session_start();
$secret_file = __DIR__ . '/../mystoq-invite/.secret';
$cfg = [];
if (file_exists($secret_file)) {
    foreach (explode("\n", trim((string)file_get_contents($secret_file))) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) continue;
        [$k, $v] = explode('=', $line, 2);
        $cfg[trim($k)] = trim($v);
    }
}
$expected_auth = hash('sha256', ($cfg['DASHBOARD_PASS'] ?? '__none__') . ($_SERVER['HTTP_USER_AGENT'] ?? ''));
$is_session = isset($_SESSION['intel_auth']) && hash_equals($expected_auth, (string)$_SESSION['intel_auth']);
$is_cron = isset($_GET['key']) && hash_equals(($cfg['CRON_KEY'] ?? $cfg['SECRET'] ?? '__none__'), $_GET['key']);
if (!$is_session && !$is_cron) {
    http_response_code(403);
    exit('Forbidden');
}

header('Content-Type: application/json; charset=utf-8');

const I_DIR = __DIR__ . '/data';
if (!is_dir(I_DIR)) @mkdir(I_DIR, 0750, true);
const ALERTS_FILE = I_DIR . '/alerts.jsonl';
const EVENTS_FILE = I_DIR . '/events.jsonl';
const LEADS_FILE = I_DIR . '/leads.jsonl';
const C_DIR = __DIR__ . '/../mystoq-invite';

// ─── Load data ────────────────────────────────────────────────
function load_events(string $path): array {
    if (!file_exists($path)) return [];
    $out = [];
    $fh = @fopen($path, 'r');
    if (!$fh) return [];
    while (($line = fgets($fh)) !== false) {
        $obj = json_decode($line, true);
        if (is_array($obj)) $out[] = $obj;
    }
    fclose($fh);
    return $out;
}

$intel_events = load_events(EVENTS_FILE);
$intel_leads = load_events(LEADS_FILE);

// Dedupe leads (latest snapshot wins)
$leads = [];
foreach ($intel_leads as $l) $leads[$l['lead_id'] ?? ''] = $l;
unset($leads['']);

// Index events by lead_id
$events_by_lead = [];
foreach ($intel_events as $e) {
    $u = $e['lead_id'] ?? '';
    if (!$u) continue;
    $events_by_lead[$u][] = $e;
}

// ─── RULE ENGINE ──────────────────────────────────────────────
$alerts = [];
function alert(string $rule_id, string $lead_id, string $severity, string $title, string $action, array $context = []): array {
    return [
        'id' => "$rule_id:$lead_id",
        'ts' => gmdate('c'),
        'rule' => $rule_id,
        'lead_id' => $lead_id,
        'severity' => $severity,  // hot | warm | info
        'title' => $title,
        'action' => $action,
        'context' => $context,
    ];
}

// Helper: count of specific event kinds for a lead
function count_kind(array $events, string $kind): int {
    return count(array_filter($events, fn($e) => ($e['kind'] ?? '') === $kind));
}
function last_kind(array $events, string $kind): ?array {
    $r = null;
    foreach ($events as $e) if (($e['kind'] ?? '') === $kind) $r = $e;
    return $r;
}
function any_kind(array $events, array $kinds): bool {
    foreach ($events as $e) if (in_array($e['kind'] ?? '', $kinds, true)) return true;
    return false;
}
function ts_of(array $e): int { return (int)strtotime($e['ts'] ?? ''); }

// ═══════════════════════════════════════════════════════════════
// RULE 1: "Tool user, no signup" — visited a tool, captured email,
//          but hasn't gone through /try/ portal yet
// ═══════════════════════════════════════════════════════════════
foreach ($leads as $lead_id => $lead) {
    $evs = $events_by_lead[$lead_id] ?? [];
    if (empty($evs)) continue;

    $used_tool = any_kind($evs, ['tool_result_request', 'tool_view']);
    $did_signup = any_kind($evs, ['try_signup', 'signup']);
    if ($used_tool && !$did_signup) {
        $tool_event = last_kind($evs, 'tool_result_request') ?? last_kind($evs, 'tool_view');
        $tool = $tool_event['source'] ?? 'tool';
        $alerts[] = alert(
            'tool-user-no-signup',
            $lead_id,
            'warm',
            'استعمل أداة لكن لم يفتح حساب',
            'أرسل واتساب: «هل تحتاج مساعدة في فتح متجرك؟»',
            ['email' => $lead['email'] ?? '', 'tool' => $tool, 'last_seen' => $lead['last_seen'] ?? '']
        );
    }
}

// ═══════════════════════════════════════════════════════════════
// RULE 2: "Engaged but anonymous" — visited 3+ times in 7 days,
//          no email captured. Show prominent capture form on next visit.
// ═══════════════════════════════════════════════════════════════
$anon_visits = [];
$week_ago = time() - 86400 * 7;
foreach ($intel_events as $e) {
    if (($e['kind'] ?? '') !== 'pageview') continue;
    if (ts_of($e) < $week_ago) continue;
    $lid = $e['lead_id'] ?? '';
    if (!$lid || substr($lid, 0, 1) !== 'a') continue;  // 'a' prefix = anonymous
    $anon_visits[$lid] = ($anon_visits[$lid] ?? 0) + 1;
}
foreach ($anon_visits as $lid => $n) {
    if ($n >= 3) {
        $alerts[] = alert(
            'engaged-anonymous',
            $lid,
            'warm',
            "$n زيارة من زائر مجهول هذا الأسبوع",
            'فعّل popup الاشتراك في الزيارة القادمة',
            ['visits' => $n]
        );
    }
}

// ═══════════════════════════════════════════════════════════════
// RULE 3: "High-value tool" — IBAN/Yalidine result captured email
// ═══════════════════════════════════════════════════════════════
foreach ($leads as $lead_id => $lead) {
    $evs = $events_by_lead[$lead_id] ?? [];
    if (empty($evs)) continue;

    foreach ($evs as $e) {
        if (($e['kind'] ?? '') !== 'tool_result_request') continue;
        $tool = $e['source'] ?? '';
        if ($tool === 'tools-yalidine') {
            $alerts[] = alert(
                'yalidine-lead',
                $lead_id,
                'hot',
                'استعمل حاسبة Yalidine — تاجر يبيع أونلاين فعلا',
                'اتصل اليوم بـ WhatsApp — هذا lead جاهز للبيع',
                ['email' => $lead['email'] ?? '', 'fields' => $e['fields'] ?? []]
            );
            break;
        }
        if ($tool === 'tools-iban') {
            $alerts[] = alert(
                'iban-lead',
                $lead_id,
                'warm',
                'تحقق من IBAN — يتعامل مع بنوك جزائرية',
                'أرسل ايميل: «بنينا تكامل مع البنوك الجزائرية»',
                ['email' => $lead['email'] ?? '']
            );
            break;
        }
    }
}

// ═══════════════════════════════════════════════════════════════
// RULE 4: Wave 1 campaign — opened email but didn't click
// ═══════════════════════════════════════════════════════════════
$opens = [];
if (file_exists(C_DIR . '/opens.log')) {
    foreach (file(C_DIR . '/opens.log', FILE_IGNORE_NEW_LINES) as $line) {
        $p = explode("\t", $line);
        if (count($p) >= 2) $opens[$p[1]] = $p[0];
    }
}
$clicks = [];
if (file_exists(C_DIR . '/visits.log')) {
    foreach (file(C_DIR . '/visits.log', FILE_IGNORE_NEW_LINES) as $line) {
        $p = explode("\t", $line);
        if (count($p) >= 2) $clicks[$p[1]] = true;
    }
}
$opened_no_click = array_diff_key($opens, $clicks);
if (count($opened_no_click) >= 3) {
    $alerts[] = alert(
        'wave1-opened-no-click',
        'wave1',
        'warm',
        count($opened_no_click) . ' فتحوا إيميل الحملة لكن لم ينقروا',
        'انتظر 48 ساعة ثم أرسل follow-up FU1 لهم',
        ['count' => count($opened_no_click)]
    );
}

// ═══════════════════════════════════════════════════════════════
// RULE 5: Hot day — abnormal traffic spike
// ═══════════════════════════════════════════════════════════════
$last_hour = time() - 3600;
$pageviews_1h = 0;
foreach ($intel_events as $e) {
    if (($e['kind'] ?? '') === 'pageview' && ts_of($e) >= $last_hour) $pageviews_1h++;
}
if ($pageviews_1h >= 50) {
    $alerts[] = alert(
        'traffic-spike',
        'system',
        'hot',
        "$pageviews_1h زيارة في الساعة الأخيرة — موجة غير عادية",
        'تحقق من المصدر — قد يكون منشور viral',
        ['count' => $pageviews_1h]
    );
}

// ─── Persist alerts (dedup by id) ─────────────────────────────
$existing = [];
if (file_exists(ALERTS_FILE)) {
    foreach (file(ALERTS_FILE, FILE_IGNORE_NEW_LINES) as $line) {
        $obj = json_decode($line, true);
        if (is_array($obj) && isset($obj['id'])) $existing[$obj['id']] = $obj;
    }
}
$new_alerts = 0;
foreach ($alerts as $a) {
    if (!isset($existing[$a['id']])) {
        $new_alerts++;
        @file_put_contents(ALERTS_FILE, json_encode($a, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND | LOCK_EX);
    }
}

echo json_encode([
    'ok' => true,
    'run_at' => gmdate('c'),
    'total_alerts_now' => count($alerts),
    'new_alerts_added' => $new_alerts,
    'rules_evaluated' => 5,
    'data_scanned' => [
        'events' => count($intel_events),
        'leads' => count($leads),
        'mystoq_opens' => count($opens),
        'mystoq_clicks' => count($clicks),
    ],
    'alerts' => $alerts,
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
