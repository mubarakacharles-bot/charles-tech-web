<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('Allow: GET');
    api_json(['error' => 'Method not allowed.'], 405);
}

try {
    api_db()->query('SELECT 1')->fetchColumn();
    api_json(['status' => 'ok']);
} catch (Throwable $error) {
    api_fail($error);
}
