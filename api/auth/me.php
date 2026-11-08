<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
auth_require_method('GET');
auth_start_session();

try {
    $pdo = api_db();
    api_json([
        'user' => auth_current_user($pdo),
        'csrfToken' => $_SESSION['csrf_token'],
    ]);
} catch (Throwable $error) {
    auth_api_fail($error);
}
