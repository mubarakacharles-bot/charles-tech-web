<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
auth_require_method('POST');
auth_start_session();
auth_require_csrf();

$_SESSION = [];
$params = session_get_cookie_params();
if (ini_get('session.use_cookies')) {
    setcookie(session_name(), '', [
        'expires' => time() - 42000,
        'path' => $params['path'],
        'domain' => $params['domain'],
        'secure' => $params['secure'],
        'httponly' => $params['httponly'],
        'samesite' => $params['samesite'] ?? 'Lax',
    ]);
}
session_destroy();
api_json(['ok' => true]);
