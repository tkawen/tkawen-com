<?php
/**
 * id/me.php — JSON "who am I?" endpoint.
 *
 * Any subapp can call this (CORS allowed for *.tkawen.online and *.tkawen.com)
 * to find out if the current visitor is logged in via TKAWEN ID.
 *
 * Returns:
 *   { logged_in: true, email: "...", expires: 1234567890 }
 *   { logged_in: false }
 */
declare(strict_types=1);
header_remove('X-Powered-By');
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (preg_match('#^https?://([a-z0-9-]+\.)?(tkawen\.(com|online|io))$#i', $origin)) {
    header('Access-Control-Allow-Origin: ' . $origin);
    header('Access-Control-Allow-Credentials: true');
    header('Vary: Origin');
}

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
$SSO_SECRET = $cfg['SSO_SECRET'] ?? ($cfg['SECRET'] ?? 'INSECURE-CHANGE-ME');

function b64url_decode(string $s): string|false { return base64_decode(strtr($s, '-_', '+/')); }
function b64url(string $s): string { return rtrim(strtr(base64_encode($s), '+/', '-_'), '='); }

$cookie = $_COOKIE['tkawen_id'] ?? '';
if (!$cookie) {
    echo json_encode(['logged_in' => false, 'reason' => 'no_cookie']);
    exit;
}

$parts = explode('.', $cookie);
if (count($parts) !== 4) {
    echo json_encode(['logged_in' => false, 'reason' => 'bad_format']);
    exit;
}
[$email_b, $exp, $nonce, $sig] = $parts;
$payload = "$email_b.$exp.$nonce";
$expected = b64url(hash_hmac('sha256', $payload, $SSO_SECRET, true));
if (!hash_equals($expected, $sig)) {
    echo json_encode(['logged_in' => false, 'reason' => 'bad_sig']);
    exit;
}
if ((int)$exp < time()) {
    echo json_encode(['logged_in' => false, 'reason' => 'expired']);
    exit;
}
$email = b64url_decode($email_b);
if (!$email) {
    echo json_encode(['logged_in' => false, 'reason' => 'bad_email']);
    exit;
}

echo json_encode([
    'logged_in' => true,
    'email' => $email,
    'expires' => (int)$exp,
    'expires_iso' => gmdate('c', (int)$exp),
], JSON_UNESCAPED_UNICODE);
