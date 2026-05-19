<?php
declare(strict_types=1);
header_remove('X-Powered-By');
setcookie('tkawen_id', '', [
    'expires'  => time() - 3600,
    'path'     => '/',
    'domain'   => '.tkawen.online',
    'secure'   => true,
    'httponly' => true,
    'samesite' => 'Lax',
]);
header('Location: /id/login.php');
exit;
