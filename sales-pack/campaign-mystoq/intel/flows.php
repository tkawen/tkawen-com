<?php
/**
 * intel/flows.php — automation engine that ACTS (not just suggests).
 *
 * Reads alerts.jsonl + leads.jsonl, applies action rules per alert kind,
 * fires real outbound (email via tkawen.online SMTP). Tracks every fire
 * in flow-fires.jsonl so we never double-act on the same lead-rule pair.
 *
 * Rules are evaluated server-side. Each flow has:
 *   - match:   which alert rule + severity to match
 *   - cooldown: minimum seconds between fires for same lead
 *   - max_fires: hard cap of fires per lead lifetime
 *   - action:  what to do (send_email, log_only, ...)
 *
 * Trigger:
 *   POST /intel/flows.php?key=SECRET&fire=1  → execute all matching rules
 *   GET  /intel/flows.php?key=SECRET         → dry-run, show what would fire
 *
 * Schedule on VPS cron:
 *   *\/15 * * * * curl -X POST "https://tkawen.online/intel/flows.php?key=...&fire=1"
 */
declare(strict_types=1);
header_remove('X-Powered-By');
session_start();

const SECRET_FILE = __DIR__ . '/../mystoq-invite/.secret';
$cfg = [];
if (file_exists(SECRET_FILE)) {
    foreach (explode("\n", trim((string)file_get_contents(SECRET_FILE))) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) continue;
        [$k, $v] = explode('=', $line, 2);
        $cfg[trim($k)] = trim($v);
    }
}

// Auth: dashboard session OR ?key=SECRET
$expected = hash('sha256', ($cfg['DASHBOARD_PASS'] ?? '__none__') . ($_SERVER['HTTP_USER_AGENT'] ?? ''));
$is_session = isset($_SESSION['intel_auth']) && hash_equals($expected, (string)$_SESSION['intel_auth']);
$is_cron = isset($_GET['key']) && hash_equals(($cfg['SECRET'] ?? '__none__'), $_GET['key']);
if (!$is_session && !$is_cron) { http_response_code(403); exit('Forbidden'); }

header('Content-Type: application/json; charset=utf-8');

const I_DIR = __DIR__ . '/data';
const ALERTS_FILE = I_DIR . '/alerts.jsonl';
const LEADS_FILE = I_DIR . '/leads.jsonl';
const FIRES_FILE = I_DIR . '/flow-fires.jsonl';
if (!is_dir(I_DIR)) @mkdir(I_DIR, 0750, true);

$fire = isset($_GET['fire']) && $_GET['fire'] === '1';

// ─── Flow definitions ─────────────────────────────────────────
$FLOWS = [
    'yalidine-hot-outreach' => [
        'match_rule'    => 'yalidine-lead',
        'match_severity'=> 'hot',
        'cooldown_sec'  => 86400 * 3,  // 3 days between fires for same lead
        'max_fires'     => 1,           // only once ever
        'action'        => 'send_email',
        'template'      => 'yalidine-outreach',
    ],
    'tool-user-nudge' => [
        'match_rule'    => 'tool-user-no-signup',
        'match_severity'=> 'warm',
        'cooldown_sec'  => 86400 * 5,
        'max_fires'     => 1,
        'action'        => 'send_email',
        'template'      => 'tool-nudge',
    ],
    'iban-lead-followup' => [
        'match_rule'    => 'iban-lead',
        'match_severity'=> 'warm',
        'cooldown_sec'  => 86400 * 5,
        'max_fires'     => 1,
        'action'        => 'send_email',
        'template'      => 'iban-followup',
    ],
];

// ─── Email templates (built inline, fully personalized) ───────
function render_email_body(string $template, array $lead, array $alert): array {
    $name = $lead['name'] ?: explode('@', $lead['email'] ?? '')[0] ?: 'صديقي';
    $email = $lead['email'] ?? '';
    $lead_id = $lead['lead_id'] ?? '';
    $ref = urlencode($lead_id);

    switch ($template) {
        case 'yalidine-outreach':
            $ctx = $alert['context']['fields'] ?? [];
            $wilaya = $ctx['to_wilaya'] ?? '';
            $total = $ctx['total'] ?? '';
            $hint = ($wilaya && $total) ? " (التوصيل إلى ولاية $wilaya بسعر $total دج)" : "";

            $subject = "$name، رأيتُكَ استعملتَ حاسبة Yalidine";
            $body_html = <<<HTML
<!DOCTYPE html><html lang="ar" dir="rtl"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>
<body style="margin:0;padding:0;background:#f1f5f9;font-family:-apple-system,'Segoe UI',Tahoma,Arial,sans-serif;color:#0f172a;direction:rtl;text-align:right;">
<table cellpadding="0" cellspacing="0" border="0" width="100%" style="background:#f1f5f9;"><tr><td align="center" style="padding:24px 16px;">
<table cellpadding="0" cellspacing="0" border="0" width="520" style="max-width:520px;background:#ffffff;border-radius:12px;">
<tr><td style="padding:20px 24px;border-bottom:1px solid #e2e8f0;">
<span style="font-weight:700;font-size:15px;color:#0f172a;">TKAWEN</span> <span style="color:#cbd5e1;margin:0 6px;">×</span> <span style="font-weight:700;font-size:15px;color:#1d4ed8;">MyStoq</span>
</td></tr>
<tr><td style="padding:24px;">
<p style="margin:0 0 14px;font-size:15px;line-height:1.7;">السلام عليكم <strong>{$name}</strong>،</p>
<p style="margin:0 0 14px;font-size:15px;line-height:1.7;color:#334155;">
لاحظتُ أنّكَ استعملتَ حاسبة Yalidine على tkawen.online{$hint}.
</p>
<p style="margin:0 0 14px;font-size:15px;line-height:1.7;color:#334155;">
أظنّ أنّكَ تبيع أونلاين أو تفكّر في ذلك. عندنا منصة جزائرية اسمها <strong style="color:#1d4ed8;">MyStoq</strong> تربط Yalidine + CTM + Edahabia مباشرة — كل طلب يتمّ تلقائيا بدون Excel.
</p>
<p style="margin:0 0 18px;font-size:15px;line-height:1.7;color:#334155;">
خصصتُ لكَ <strong>90 يوم استخدام كامل مجاناً</strong> + جلسة إعداد شخصية معي.
</p>
<table cellpadding="0" cellspacing="0" border="0" width="100%" style="margin:0 0 16px;"><tr><td align="center">
<a href="https://tkawen.online/try/?p=mystoq&utm_source=flow-yalidine&ref={$ref}" style="display:inline-block;padding:14px 32px;background:#1d4ed8;color:#ffffff !important;font-weight:700;font-size:15px;text-decoration:none;border-radius:8px;">جرب MyStoq الآن &nbsp;←</a>
</td></tr></table>
<p style="margin:0 0 14px;font-size:13px;line-height:1.7;color:#64748b;">
إن لم تكن مهتماً، ردّ بكلمة «لا» وأزيلكَ من قائمتي. صراحة.
</p>
<p style="margin:0;font-size:14px;line-height:1.6;">— <strong>يعقوب حرتام</strong><br><span style="font-size:12px;color:#94a3b8;">مؤسس TKAWEN · عنابة</span></p>
</td></tr>
<tr><td style="padding:14px 24px;background:#f8fafc;border-top:1px solid #e2e8f0;text-align:center;">
<p style="margin:0;font-size:11px;color:#94a3b8;">تتلقّى هذا لأنّكَ تركتَ بريدكَ على tkawen.online</p>
</td></tr>
</table>
</td></tr></table>
</body></html>
HTML;
            return ['subject' => $subject, 'body' => $body_html];

        case 'tool-nudge':
            $tool = $alert['context']['tool'] ?? 'أداة';
            $tool_ar = match ($tool) {
                'tools-yalidine' => 'حاسبة Yalidine',
                'tools-iban' => 'تحقق من IBAN',
                'tools-wilaya' => 'البحث عن الولاية',
                'tools-tva' => 'حاسبة TVA',
                default => 'أداة TKAWEN',
            };
            $subject = "$name، شكراً لاستخدامك $tool_ar";
            $body_html = <<<HTML
<!DOCTYPE html><html lang="ar" dir="rtl"><head><meta charset="utf-8"></head>
<body style="margin:0;padding:24px 16px;background:#f1f5f9;font-family:-apple-system,'Segoe UI',Tahoma,Arial,sans-serif;color:#0f172a;direction:rtl;text-align:right;">
<table cellpadding="0" cellspacing="0" border="0" width="480" style="max-width:480px;margin:0 auto;background:#fff;border-radius:12px;padding:24px;">
<tr><td>
<p style="margin:0 0 14px;font-size:15px;line-height:1.7;">السلام عليكم <strong>{$name}</strong>،</p>
<p style="margin:0 0 14px;font-size:15px;line-height:1.7;color:#334155;">
شكراً لاستخدامكَ {$tool_ar} على tkawen.online.
</p>
<p style="margin:0 0 14px;font-size:15px;line-height:1.7;color:#334155;">
بنينا هذه الأداة لأنّنا احتجناها في MyStoq — منصّتنا للتجارة الإلكترونية الجزائرية. إن كنتَ تبيع أو تفكر في ذلك، MyStoq يدمج هذه الأداة وعشرات أخرى تلقائياً.
</p>
<p style="margin:0 0 18px;font-size:15px;line-height:1.7;color:#334155;">
<strong>عرض حصري لك:</strong> 90 يوم استخدام مجاني كامل.
</p>
<p style="text-align:center;margin:0 0 16px;">
<a href="https://tkawen.online/try/?p=mystoq&utm_source=flow-tool-nudge&ref={$ref}" style="display:inline-block;padding:13px 28px;background:#1d4ed8;color:#ffffff !important;font-weight:700;font-size:14px;text-decoration:none;border-radius:8px;">جرب MyStoq مجانا &nbsp;←</a>
</p>
<p style="margin:14px 0 0;font-size:13px;color:#64748b;">— يعقوب من TKAWEN</p>
</td></tr></table>
</body></html>
HTML;
            return ['subject' => $subject, 'body' => $body_html];

        case 'iban-followup':
            $subject = "$name، MyStoq يتكامل مع البنوك الجزائرية";
            $body_html = <<<HTML
<!DOCTYPE html><html lang="ar" dir="rtl"><head><meta charset="utf-8"></head>
<body style="margin:0;padding:24px 16px;background:#f1f5f9;font-family:-apple-system,sans-serif;color:#0f172a;direction:rtl;text-align:right;">
<table cellpadding="0" cellspacing="0" border="0" width="480" style="max-width:480px;margin:0 auto;background:#fff;border-radius:12px;padding:24px;">
<tr><td>
<p style="margin:0 0 14px;font-size:15px;">السلام عليكم <strong>{$name}</strong>،</p>
<p style="margin:0 0 14px;font-size:15px;line-height:1.7;color:#334155;">
شكراً لاستخدامكَ أداة التحقق من IBAN. لاحظت أنّك تتعامل مع حسابات بنكية جزائرية.
</p>
<p style="margin:0 0 14px;font-size:15px;line-height:1.7;color:#334155;">
إن كنت تدير متجراً أو تفكر في ذلك، MyStoq يتكامل مباشرة مع <strong>Edahabia + CIB + CCP</strong> — الزبون يدفع، أنت تستلم تلقائياً في حسابك.
</p>
<p style="text-align:center;margin:18px 0;">
<a href="https://tkawen.online/try/?p=mystoq&utm_source=flow-iban&ref={$ref}" style="display:inline-block;padding:13px 28px;background:#1d4ed8;color:#fff !important;font-weight:700;text-decoration:none;border-radius:8px;font-size:14px;">90 يوم مجاناً &nbsp;←</a>
</p>
<p style="margin:14px 0 0;font-size:13px;color:#64748b;">— يعقوب من TKAWEN</p>
</td></tr></table>
</body></html>
HTML;
            return ['subject' => $subject, 'body' => $body_html];
    }
    return ['subject' => '', 'body' => ''];
}

// ─── Send email via tkawen.online SMTP ────────────────────────
function send_email(string $to, string $subject, string $html): bool {
    $boundary = 'tkawen-' . md5(uniqid('', true));
    $subject_enc = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $headers = implode("\r\n", [
        'From: Yaakoub Hartem from TKAWEN <noreply@tkawen.online>',
        'Reply-To: yaakoub@tkawen.com',
        'X-Mailer: TKAWEN-Flow',
        'X-Campaign: tkawen-flows-auto',
        'List-Unsubscribe: <mailto:yaakoub@tkawen.com?subject=Unsubscribe>',
        'MIME-Version: 1.0',
        'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
    ]);
    $body = "--{$boundary}\r\n"
          . "Content-Type: text/plain; charset=UTF-8\r\n\r\n"
          . "افتح هذا الإيميل في عميل بريد يدعم HTML.\r\n\r\n"
          . "--{$boundary}\r\n"
          . "Content-Type: text/html; charset=UTF-8\r\n"
          . "Content-Transfer-Encoding: 8bit\r\n\r\n"
          . $html . "\r\n"
          . "--{$boundary}--";
    return @mail($to, $subject_enc, $body, $headers, '-fnoreply@tkawen.online');
}

// ─── Load data ────────────────────────────────────────────────
function read_jsonl(string $path): array {
    if (!file_exists($path)) return [];
    $out = [];
    $fh = @fopen($path, 'r');
    if (!$fh) return [];
    while (($line = fgets($fh)) !== false) {
        $o = json_decode($line, true);
        if (is_array($o)) $out[] = $o;
    }
    fclose($fh);
    return $out;
}

$alerts = read_jsonl(ALERTS_FILE);
$leads_raw = read_jsonl(LEADS_FILE);
$fires_raw = read_jsonl(FIRES_FILE);

// Dedupe alerts by id (latest wins)
$alerts_by_id = [];
foreach ($alerts as $a) $alerts_by_id[$a['id'] ?? ''] = $a;
unset($alerts_by_id['']);

// Dedupe leads (latest snapshot)
$leads_by_id = [];
foreach ($leads_raw as $l) $leads_by_id[$l['lead_id'] ?? ''] = $l;
unset($leads_by_id['']);

// Index fires: count per (flow_id, lead_id) + last fire timestamp
$fire_count = [];
$fire_last = [];
foreach ($fires_raw as $f) {
    $key = ($f['flow_id'] ?? '') . '|' . ($f['lead_id'] ?? '');
    $fire_count[$key] = ($fire_count[$key] ?? 0) + 1;
    $ts = (int)strtotime($f['ts'] ?? '');
    if (!isset($fire_last[$key]) || $ts > $fire_last[$key]) $fire_last[$key] = $ts;
}

// ─── Evaluate each alert against each flow ────────────────────
$results = [];
$now = time();
$new_fires = 0;
$skipped = 0;

foreach ($alerts_by_id as $alert) {
    foreach ($FLOWS as $flow_id => $flow) {
        if (($alert['rule'] ?? '') !== $flow['match_rule']) continue;
        if (($alert['severity'] ?? '') !== $flow['match_severity']) continue;

        $lead_id = $alert['lead_id'] ?? '';
        if (!$lead_id || substr($lead_id, 0, 1) !== 'l') continue;  // must be email-derived
        $lead = $leads_by_id[$lead_id] ?? null;
        if (!$lead || empty($lead['email'])) continue;

        $key = "$flow_id|$lead_id";

        // Max fires check
        if (($fire_count[$key] ?? 0) >= $flow['max_fires']) {
            $skipped++;
            continue;
        }

        // Cooldown check
        if (isset($fire_last[$key]) && ($now - $fire_last[$key]) < $flow['cooldown_sec']) {
            $skipped++;
            continue;
        }

        // Action: send email (or dry-run)
        $rendered = render_email_body($flow['template'], $lead, $alert);
        $would_send_to = $lead['email'];

        if ($fire) {
            $ok = send_email($would_send_to, $rendered['subject'], $rendered['body']);
            @file_put_contents(FIRES_FILE,
                json_encode([
                    'ts' => gmdate('c'),
                    'flow_id' => $flow_id,
                    'lead_id' => $lead_id,
                    'alert_id' => $alert['id'],
                    'email' => $would_send_to,
                    'subject' => $rendered['subject'],
                    'success' => $ok,
                ], JSON_UNESCAPED_UNICODE) . "\n",
                FILE_APPEND | LOCK_EX
            );
            $new_fires++;
            $results[] = ['flow' => $flow_id, 'lead' => $lead_id, 'email' => $would_send_to,
                          'subject' => $rendered['subject'], 'sent' => $ok];
        } else {
            $results[] = ['flow' => $flow_id, 'lead' => $lead_id, 'email' => $would_send_to,
                          'subject' => $rendered['subject'], 'would_send' => true];
        }
    }
}

echo json_encode([
    'ok' => true,
    'mode' => $fire ? 'fire' : 'dry-run',
    'flows_defined' => count($FLOWS),
    'alerts_scanned' => count($alerts_by_id),
    'leads_known' => count($leads_by_id),
    'fires_total_lifetime' => count($fires_raw),
    'fires_now' => $new_fires,
    'skipped' => $skipped,
    'results' => $results,
    'tip' => $fire ? 'Flows executed. Check flow-fires.jsonl + recipient inboxes.' : 'Dry-run. Add &fire=1 to actually send.',
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
