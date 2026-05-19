<?php
// 1x1 transparent GIF tracking pixel.
// Logs the open event with user_id, token, variant, timestamp.

declare(strict_types=1);
header_remove('X-Powered-By');

$user_id = isset($_GET['u']) ? preg_replace('/[^a-zA-Z0-9_-]/', '', $_GET['u']) : '';
$token   = isset($_GET['t']) ? preg_replace('/[^a-zA-Z0-9]/', '', substr($_GET['t'], 0, 32)) : '';
$variant = isset($_GET['v']) ? preg_replace('/[^A-C]/', '', substr($_GET['v'], 0, 1)) : '';

@file_put_contents(
    __DIR__ . '/opens.log',
    sprintf("%s\t%s\t%s\t%s\t%s\n", date('c'), $user_id, $variant, $token, $_SERVER['HTTP_USER_AGENT'] ?? ''),
    FILE_APPEND | LOCK_EX
);

// 1x1 transparent GIF (43 bytes)
header('Content-Type: image/gif');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Content-Length: 43');
echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
