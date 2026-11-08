<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');

function api_json(array $payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function api_db(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $documentRoot = $_SERVER['DOCUMENT_ROOT'] ?? '';
    if ($documentRoot === '') {
        throw new RuntimeException('Apache document root is not configured.');
    }

    $configPath = dirname($documentRoot) . DIRECTORY_SEPARATOR
        . 'private' . DIRECTORY_SEPARATOR . 'charles-tech-db.php';
    if (!is_file($configPath)) {
        throw new RuntimeException('Database configuration is missing.');
    }

    $config = require $configPath;
    if (!is_array($config) || !isset($config['host'], $config['database'], $config['username'], $config['password'])) {
        throw new RuntimeException('Database configuration is invalid.');
    }

    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=utf8mb4',
        $config['host'],
        $config['database']
    );
    $pdo = new PDO($dsn, $config['username'], $config['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    return $pdo;
}

function api_fail(Throwable $error): never
{
    error_log('[Charles Tech API] ' . $error->getMessage());
    api_json(['error' => 'The service is temporarily unavailable.'], 500);
}
