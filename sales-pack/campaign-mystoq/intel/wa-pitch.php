<?php
/**
 * intel/wa-pitch.php — generates a personalized WhatsApp pitch URL for a lead.
 *
 * Called from the alerts panel. Looks up the lead's data (name, email,
 * what tool they used, etc.) and crafts a context-aware WA message.
 *
 * GET ?lead_id=l_xxx → returns JSON { wa_url, message, phone_required }
 * Auth: dashboard session.
 *
 * The dashboard then shows a "📱 WhatsApp" button that opens wa.me with
 * the pre-typed message. Founder just clicks Send.
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
$expected = hash('sha256', ($cfg['DASHBOARD_PASS'] ?? '__none__') . ($_SERVER['HTTP_USER_AGENT'] ?? ''));
if (!isset($_SESSION['intel_auth']) || !hash_equals($expected, (string)$_SESSION['intel_auth'])) {
    http_response_code(403);
    header('Content-Type: application/json');
    exit(json_encode(['error' => 'forbidden']));
}

header('Content-Type: application/json; charset=utf-8');

const I_DIR = __DIR__ . '/data';
const LEADS_FILE = I_DIR . '/leads.jsonl';
const EVENTS_FILE = I_DIR . '/events.jsonl';

$lead_id = preg_replace('/[^a-zA-Z0-9_]/', '', $_GET['lead_id'] ?? '');
if (!$lead_id) {
    http_response_code(400);
    exit(json_encode(['error' => 'lead_id required']));
}

// Find lead
$lead = null;
if (file_exists(LEADS_FILE)) {
    $fh = fopen(LEADS_FILE, 'r');
    while (($line = fgets($fh)) !== false) {
        $obj = json_decode($line, true);
        if (is_array($obj) && ($obj['lead_id'] ?? '') === $lead_id) $lead = $obj;
    }
    fclose($fh);
}
if (!$lead) {
    exit(json_encode(['error' => 'lead not found']));
}

// Find what tool they used (most recent tool event)
$tool_event = null;
if (file_exists(EVENTS_FILE)) {
    $fh = fopen(EVENTS_FILE, 'r');
    while (($line = fgets($fh)) !== false) {
        $obj = json_decode($line, true);
        if (is_array($obj) && ($obj['lead_id'] ?? '') === $lead_id
            && ($obj['kind'] ?? '') === 'tool_result_request') $tool_event = $obj;
    }
    fclose($fh);
}

// ─── Craft message by context ────────────────────────────────
$name = $lead['name'] ?: explode('@', $lead['email'] ?? '')[0] ?: 'صديقي';
$source = $lead['source'] ?? '';

$msg = "السلام عليكم $name،\n\n";

if ($source === 'tools-yalidine' && $tool_event) {
    $fields = $tool_event['fields'] ?? [];
    $to_wilaya = $fields['to_wilaya'] ?? '';
    $total = $fields['total'] ?? '';
    $msg .= "لاحظت أنك استعملت حاسبة Yalidine على tkawen.online";
    if ($to_wilaya && $total) {
        $msg .= " (التوصيل إلى ولاية $to_wilaya بسعر $total دج).";
    } else $msg .= ".";
    $msg .= "\n\nأظن أنك تبيع أونلاين أو تفكر في ذلك. عندنا منصة جزائرية اسمها MyStoq تربط Yalidine + CTM + Edahabia مباشرة — كل طلب يتم تلقائيا بدون Excel.\n\n";
    $msg .= "خصصت لك 90 يوم استخدام مجاني كامل + جلسة إعداد شخصية معي.\n\n";
    $msg .= "الرابط:\nhttps://tkawen.online/try/?p=mystoq&utm_source=wa-hot-yalidine&ref=" . urlencode($lead_id) . "\n\n";
    $msg .= "إن لم تكن مهتما، لا تقلق — لن أزعجك مجددا. ردك مهم لي.";
} elseif ($source === 'tools-iban') {
    $msg .= "لاحظت أنك استعملت أداة التحقق من IBAN على tkawen.online.\n\n";
    $msg .= "إن كنت تدير حسابات بنكية لمتجر إلكتروني، عندنا MyStoq — منصة جزائرية تتكامل مع Edahabia + CIB + CCP مباشرة. الزبون يدفع، أنت تستلم تلقائيا في حسابك.\n\n";
    $msg .= "90 يوم تجربة مجانية:\nhttps://tkawen.online/try/?p=mystoq&utm_source=wa-iban&ref=" . urlencode($lead_id);
} elseif ($source === 'tools-tva') {
    $msg .= "لاحظت أنك استعملت حاسبة TVA. إن كنت تبيع منتجات وتحتاج فواتير رسمية بـ TVA + NIS + NIF، MyStoq يولدها تلقائيا.\n\n";
    $msg .= "90 يوم تجربة مجانية:\nhttps://tkawen.online/try/?p=mystoq&utm_source=wa-tva&ref=" . urlencode($lead_id);
} elseif ($source === 'tools-wilaya') {
    $msg .= "لاحظت أنك استعملت أداة البحث عن الولاية. إن كنت تخطط لمتجر إلكتروني، MyStoq يدير 48 ولاية بأسعار توصيل دقيقة.\n\n";
    $msg .= "90 يوم تجربة مجانية:\nhttps://tkawen.online/try/?p=mystoq&utm_source=wa-wilaya&ref=" . urlencode($lead_id);
} elseif (strpos($source, 'blog') !== false) {
    $msg .= "شكرا لقراءة مقالنا على tkawen.online. عندك أي سؤال عن MyStoq؟\n\n";
    $msg .= "إن أحببت تجربتها:\nhttps://tkawen.online/try/?p=mystoq&utm_source=wa-blog&ref=" . urlencode($lead_id);
} else {
    $msg .= "نتواصل من TKAWEN. اتركت لنا بريدك على tkawen.online — هل تريد أن أساعدك في فتح متجرك الإلكتروني؟\n\n";
    $msg .= "90 يوم MyStoq مجانا:\nhttps://tkawen.online/try/?p=mystoq&utm_source=wa-cold&ref=" . urlencode($lead_id);
}

$msg .= "\n\n— يعقوب من TKAWEN";

// Build wa.me URL
$phone = $lead['phone'] ?? '';
$phone_clean = preg_replace('/[^0-9]/', '', $phone);
if ($phone_clean && substr($phone_clean, 0, 1) === '0') {
    $phone_clean = '213' . substr($phone_clean, 1);
} elseif ($phone_clean && substr($phone_clean, 0, 3) !== '213') {
    $phone_clean = '213' . $phone_clean;
}
$has_phone = $phone_clean && strlen($phone_clean) >= 11;
$wa_url = $has_phone
    ? "https://wa.me/$phone_clean?text=" . rawurlencode($msg)
    : "https://wa.me/?text=" . rawurlencode($msg);

echo json_encode([
    'lead_id' => $lead_id,
    'name' => $name,
    'email' => $lead['email'] ?? '',
    'phone' => $phone,
    'has_phone' => $has_phone,
    'source' => $source,
    'message_preview' => mb_substr($msg, 0, 200) . '…',
    'message_full' => $msg,
    'wa_url' => $wa_url,
    'instruction' => $has_phone
        ? 'افتح الرابط — WhatsApp يفتح مباشرة مع الزبون والرسالة جاهزة. اضغط Send.'
        : 'لا يوجد رقم للزبون. الرابط يفتح WhatsApp مع الرسالة جاهزة — اختر جهة الاتصال يدويا.',
], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
