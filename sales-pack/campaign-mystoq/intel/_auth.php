<?php
/** Shared auth guard — require in every protected page. */
declare(strict_types=1);
header_remove('X-Powered-By');
session_start();

const _AUTH_SECRET_FILE = __DIR__ . '/../mystoq-invite/.secret';

function _load_cfg(): array {
    if (!file_exists(_AUTH_SECRET_FILE)) return [];
    $cfg = [];
    foreach (explode("\n", trim((string)file_get_contents(_AUTH_SECRET_FILE))) as $line) {
        $line = trim($line);
        if ($line === '' || $line[0] === '#' || strpos($line, '=') === false) continue;
        [$k, $v] = explode('=', $line, 2);
        $cfg[trim($k)] = trim($v);
    }
    return $cfg;
}

$_GCFG = _load_cfg();
$_EXPECTED = hash('sha256', ($_GCFG['DASHBOARD_PASS'] ?? '__none__') . ($_SERVER['HTTP_USER_AGENT'] ?? ''));

if (!isset($_SESSION['intel_auth']) || !hash_equals($_EXPECTED, (string)$_SESSION['intel_auth'])) {
    header('Location: /intel/login.php');
    exit;
}
// session timeout — 7 days
if (!isset($_SESSION['intel_since']) || (time() - (int)$_SESSION['intel_since']) > 86400 * 7) {
    session_destroy();
    header('Location: /intel/login.php');
    exit;
}
