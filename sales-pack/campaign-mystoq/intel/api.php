<?php
/**
 * intel/api.php — JSON data endpoints for the dashboard.
 *
 * Aggregates from MULTIPLE sources:
 *   - mystoq-invite/send.jsonl       (every email sent)
 *   - mystoq-invite/opens.log        (every email open via pixel)
 *   - mystoq-invite/visits.log       (every click via r.php)
 *   - mystoq-invite/leads.jsonl      (form submissions)
 *   - mystoq-invite/opt-outs.log     (unsubscribes)
 *   - mystoq-invite/stories-visits.log (case-study page visits)
 *   - intel/data/leads.jsonl         (harvested from across sites)
 *   - intel/data/events.jsonl        (universal event stream)
 *
 * Endpoints (?q=...):
 *   summary      — top counters + funnel
 *   activity     — live event feed (last 50)
 *   hot_leads    — top 10 most-engaged
 *   per_variant  — open/click rate per A/B/C/FU1/FU2/FU3
 *   geo          — opens by country (IP→geo)
 *   timeseries   — hourly buckets for 24h
 *   ai_suggest   — recommended next actions
 */
require __DIR__ . '/_auth.php';
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

const C_DIR = __DIR__ . '/../mystoq-invite';
const I_DIR = __DIR__ . '/data';
if (!is_dir(I_DIR)) @mkdir(I_DIR, 0755, true);

$q = $_GET['q'] ?? 'summary';

// ─── helpers ──────────────────────────────────────────────────
function read_jsonl(string $path, int $tail_lines = 0): array {
    if (!file_exists($path)) return [];
    $out = [];
    $fh = @fopen($path, 'r');
    if (!$fh) return [];
    while (($line = fgets($fh)) !== false) {
        $obj = json_decode($line, true);
        if (is_array($obj)) $out[] = $obj;
    }
    fclose($fh);
    if ($tail_lines > 0 && count($out) > $tail_lines) return array_slice($out, -$tail_lines);
    return $out;
}

function read_tsv(string $path, int $tail_lines = 0): array {
    if (!file_exists($path)) return [];
    $out = [];
    $fh = @fopen($path, 'r');
    if (!$fh) return [];
    while (($line = fgets($fh)) !== false) {
        $parts = explode("\t", rtrim($line, "\n"));
        if (count($parts) >= 2) $out[] = $parts;
    }
    fclose($fh);
    if ($tail_lines > 0 && count($out) > $tail_lines) return array_slice($out, -$tail_lines);
    return $out;
}

function now_minus(int $hours): int { return time() - $hours * 3600; }

function ts_of(string $iso): int {
    return $iso ? (int)strtotime($iso) : 0;
}

// ─── summary: top counters + funnel ───────────────────────────
if ($q === 'summary') {
    $sends = read_jsonl(C_DIR . '/send.jsonl');
    $opens = read_tsv(C_DIR . '/opens.log');
    $visits = read_tsv(C_DIR . '/visits.log');
    $leads = read_jsonl(C_DIR . '/leads.jsonl');
    $opt_outs = read_tsv(C_DIR . '/opt-outs.log');
    $stories = read_tsv(C_DIR . '/stories-visits.log');

    $cutoff_24h = now_minus(24);
    $cutoff_1h = now_minus(1);

    $n_sent_all = count(array_filter($sends, fn($s) => ($s['success'] ?? false) === true));
    $n_sent_24h = count(array_filter($sends, fn($s) => ($s['success'] ?? false) && ts_of($s['ts'] ?? '') >= $cutoff_24h));
    $n_sent_1h = count(array_filter($sends, fn($s) => ($s['success'] ?? false) && ts_of($s['ts'] ?? '') >= $cutoff_1h));

    $opens_24h = array_filter($opens, fn($r) => ts_of($r[0] ?? '') >= $cutoff_24h);
    $opens_1h = array_filter($opens, fn($r) => ts_of($r[0] ?? '') >= $cutoff_1h);

    $visits_24h = array_filter($visits, fn($r) => ts_of($r[0] ?? '') >= $cutoff_24h);
    $visits_1h = array_filter($visits, fn($r) => ts_of($r[0] ?? '') >= $cutoff_1h);

    // Unique counts (dedupe by user_id)
    $uniq_opens = count(array_unique(array_column($opens, 1)));
    $uniq_visits = count(array_unique(array_column($visits, 1)));
    $uniq_recipients = count(array_unique(array_column($sends, 'email')));

    echo json_encode([
        'now' => date('c'),
        'counters' => [
            'sent_total' => $n_sent_all,
            'sent_24h' => $n_sent_24h,
            'sent_1h' => $n_sent_1h,
            'opens_total' => count($opens),
            'opens_24h' => count($opens_24h),
            'opens_1h' => count($opens_1h),
            'clicks_total' => count($visits),
            'clicks_24h' => count($visits_24h),
            'clicks_1h' => count($visits_1h),
            'signups' => count($leads),
            'opt_outs' => count($opt_outs),
            'stories_visits' => count($stories),
        ],
        'funnel' => [
            ['stage' => 'recipients_unique', 'count' => $uniq_recipients],
            ['stage' => 'opened_unique',     'count' => $uniq_opens],
            ['stage' => 'clicked_unique',    'count' => $uniq_visits],
            ['stage' => 'signups',           'count' => count($leads)],
        ],
        'rates' => [
            'open_rate' => $uniq_recipients > 0 ? round(100 * $uniq_opens / $uniq_recipients, 1) : 0,
            'click_rate' => $uniq_recipients > 0 ? round(100 * $uniq_visits / $uniq_recipients, 1) : 0,
            'signup_rate' => $uniq_recipients > 0 ? round(100 * count($leads) / $uniq_recipients, 1) : 0,
            'click_through_of_open' => $uniq_opens > 0 ? round(100 * $uniq_visits / $uniq_opens, 1) : 0,
        ],
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ─── activity: live feed (newest first, last 50 events) ───────
if ($q === 'activity') {
    $feed = [];

    foreach (read_jsonl(C_DIR . '/send.jsonl', 200) as $s) {
        if (($s['success'] ?? false) !== true) continue;
        $feed[] = [
            'ts' => $s['ts'] ?? '',
            'kind' => 'send',
            'label' => 'إرسال',
            'icon' => 'send',
            'who' => $s['email'] ?? '',
            'meta' => $s['variant'] ?? '',
        ];
    }
    foreach (read_tsv(C_DIR . '/opens.log', 200) as $r) {
        $feed[] = [
            'ts' => $r[0] ?? '', 'kind' => 'open', 'label' => 'فتح',
            'icon' => 'open', 'who' => $r[1] ?? '', 'meta' => $r[2] ?? '',
        ];
    }
    foreach (read_tsv(C_DIR . '/visits.log', 200) as $r) {
        $feed[] = [
            'ts' => $r[0] ?? '', 'kind' => 'click', 'label' => 'نقر',
            'icon' => 'click', 'who' => $r[2] ?? '', 'meta' => $r[3] ?? '',
        ];
    }
    foreach (read_jsonl(C_DIR . '/leads.jsonl', 50) as $l) {
        $feed[] = [
            'ts' => $l['ts'] ?? '', 'kind' => 'signup', 'label' => 'تسجيل',
            'icon' => 'signup', 'who' => $l['email'] ?? '', 'meta' => $l['business'] ?? '',
        ];
    }
    foreach (read_tsv(C_DIR . '/opt-outs.log', 50) as $r) {
        $feed[] = [
            'ts' => $r[0] ?? '', 'kind' => 'unsub', 'label' => 'إلغاء',
            'icon' => 'unsub', 'who' => $r[1] ?? '', 'meta' => '',
        ];
    }

    usort($feed, fn($a, $b) => strcmp($b['ts'], $a['ts']));
    echo json_encode(['events' => array_slice($feed, 0, 50)], JSON_UNESCAPED_UNICODE);
    exit;
}

// ─── hot_leads: ranked by recent engagement ───────────────────
if ($q === 'hot_leads') {
    $scores = [];

    foreach (read_jsonl(C_DIR . '/send.jsonl') as $s) {
        if (($s['success'] ?? false) !== true) continue;
        $u = $s['user_id'] ?? ''; if (!$u) continue;
        $scores[$u] ??= ['user_id' => $u, 'email' => $s['email'] ?? '', 'opens' => 0, 'clicks' => 0, 'score' => 0, 'last' => ''];
        $scores[$u]['email'] = $s['email'] ?? $scores[$u]['email'];
    }
    foreach (read_tsv(C_DIR . '/opens.log') as $r) {
        $u = $r[1] ?? ''; if (!$u || !isset($scores[$u])) continue;
        $scores[$u]['opens']++;
        $scores[$u]['score'] += 10;
        if (strcmp($r[0], $scores[$u]['last']) > 0) $scores[$u]['last'] = $r[0];
    }
    foreach (read_tsv(C_DIR . '/visits.log') as $r) {
        $u = $r[1] ?? ''; if (!$u || !isset($scores[$u])) continue;
        $scores[$u]['clicks']++;
        $scores[$u]['score'] += 50;  // click = 5x more valuable than open
        $scores[$u]['email'] = $r[2] ?? $scores[$u]['email'];
        if (strcmp($r[0], $scores[$u]['last']) > 0) $scores[$u]['last'] = $r[0];
    }

    // Recency bonus: events in last 24h get 2x score
    foreach ($scores as &$s) {
        if (ts_of($s['last']) >= now_minus(24)) $s['score'] = (int)($s['score'] * 2);
    }
    unset($s);

    $list = array_values($scores);
    usort($list, fn($a, $b) => $b['score'] - $a['score']);
    echo json_encode(['hot' => array_slice($list, 0, 10)], JSON_UNESCAPED_UNICODE);
    exit;
}

// ─── per_variant: A/B/C rates ─────────────────────────────────
if ($q === 'per_variant') {
    $sends_by_v = [];
    $opens_by_v = [];
    $clicks_by_v = [];

    foreach (read_jsonl(C_DIR . '/send.jsonl') as $s) {
        if (($s['success'] ?? false) !== true) continue;
        $v = $s['variant'] ?? '?';
        $sends_by_v[$v] = ($sends_by_v[$v] ?? 0) + 1;
    }
    foreach (read_tsv(C_DIR . '/opens.log') as $r) {
        $v = $r[2] ?? '?';
        $opens_by_v[$v] = ($opens_by_v[$v] ?? 0) + 1;
    }
    foreach (read_tsv(C_DIR . '/visits.log') as $r) {
        $v = $r[3] ?? '?';
        $clicks_by_v[$v] = ($clicks_by_v[$v] ?? 0) + 1;
    }

    $variants = [];
    foreach ($sends_by_v as $v => $sent) {
        $opens = $opens_by_v[$v] ?? 0;
        $clicks = $clicks_by_v[$v] ?? 0;
        $variants[] = [
            'variant' => $v,
            'sent' => $sent,
            'opens' => $opens,
            'clicks' => $clicks,
            'open_rate' => $sent > 0 ? round(100 * $opens / $sent, 1) : 0,
            'click_rate' => $sent > 0 ? round(100 * $clicks / $sent, 1) : 0,
        ];
    }
    usort($variants, fn($a, $b) => $b['click_rate'] <=> $a['click_rate']);
    echo json_encode(['variants' => $variants], JSON_UNESCAPED_UNICODE);
    exit;
}

// ─── timeseries: hourly buckets for last 24h ──────────────────
if ($q === 'timeseries') {
    $buckets = [];
    $start = now_minus(24);
    for ($h = 0; $h < 24; $h++) {
        $buckets[$h] = ['hour' => $h, 'sends' => 0, 'opens' => 0, 'clicks' => 0];
    }

    $bucket_of = function (int $ts) use ($start): int {
        if ($ts < $start) return -1;
        return min(23, (int)floor(($ts - $start) / 3600));
    };

    foreach (read_jsonl(C_DIR . '/send.jsonl') as $s) {
        if (($s['success'] ?? false) !== true) continue;
        $b = $bucket_of(ts_of($s['ts'] ?? ''));
        if ($b >= 0) $buckets[$b]['sends']++;
    }
    foreach (read_tsv(C_DIR . '/opens.log') as $r) {
        $b = $bucket_of(ts_of($r[0] ?? ''));
        if ($b >= 0) $buckets[$b]['opens']++;
    }
    foreach (read_tsv(C_DIR . '/visits.log') as $r) {
        $b = $bucket_of(ts_of($r[0] ?? ''));
        if ($b >= 0) $buckets[$b]['clicks']++;
    }
    echo json_encode(['buckets' => array_values($buckets)], JSON_UNESCAPED_UNICODE);
    exit;
}

// ─── ai_suggest: heuristic-based recommendations ──────────────
if ($q === 'ai_suggest') {
    $suggestions = [];
    $sends = read_jsonl(C_DIR . '/send.jsonl');
    $opens = read_tsv(C_DIR . '/opens.log');
    $visits = read_tsv(C_DIR . '/visits.log');
    $leads = read_jsonl(C_DIR . '/leads.jsonl');

    $n_sent = count(array_filter($sends, fn($s) => ($s['success'] ?? false) === true));
    $n_opens = count(array_unique(array_column($opens, 1)));
    $n_clicks = count(array_unique(array_column($visits, 1)));
    $n_signups = count($leads);

    if ($n_sent < 50 && $n_sent > 0) {
        $suggestions[] = ['icon' => '🚀', 'level' => 'info', 'text' => 'الحملة في مرحلة التسخين — بعد 6 ساعات جرب إرسال 200 إيميل إضافي'];
    } elseif ($n_sent >= 50 && $n_sent < 500) {
        $suggestions[] = ['icon' => '📈', 'level' => 'good', 'text' => 'الإحصاءات أولية. انتظر 24 ساعة قبل التوسع'];
    }

    if ($n_clicks > 0 && $n_signups === 0 && $n_clicks >= 3) {
        $suggestions[] = ['icon' => '⚠️', 'level' => 'warn', 'text' => $n_clicks . ' نقروا لكن لم يسجلوا — راجع صفحة التسجيل في MyStoq'];
    }

    if ($n_opens > 0 && ($n_clicks / max(1, $n_opens)) < 0.05) {
        $suggestions[] = ['icon' => '🎯', 'level' => 'warn', 'text' => 'معدل النقر منخفض — جرب CTA مختلف أو نص أكثر إلحاحا'];
    }

    if ($n_signups >= 3) {
        $suggestions[] = ['icon' => '📞', 'level' => 'good', 'text' => $n_signups . ' تسجيلات جديدة — اتصل بهم اليوم عبر WhatsApp'];
    }

    if (empty($suggestions)) {
        $suggestions[] = ['icon' => '💡', 'level' => 'info', 'text' => 'لا توصيات حاليا — تابع المؤشرات للساعة القادمة'];
    }

    echo json_encode(['suggestions' => $suggestions], JSON_UNESCAPED_UNICODE);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'unknown q', 'valid' => ['summary', 'activity', 'hot_leads', 'per_variant', 'timeseries', 'ai_suggest']]);
