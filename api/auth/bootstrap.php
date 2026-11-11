<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/bootstrap.php';

function auth_start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    $scriptPath = $_SERVER['SCRIPT_NAME'] ?? '/api/auth/index.php';
    $apiPosition = strpos($scriptPath, '/api/');
    $appPath = $apiPosition === false ? '' : substr($scriptPath, 0, $apiPosition);
    if ($appPath === DIRECTORY_SEPARATOR || $appPath === '.') {
        $appPath = '';
    }
    session_name('chts_session');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => rtrim($appPath, '/') . '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();

    if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
}

function auth_user_payload(array $user): array
{
    return [
        'id' => (int) $user['id'],
        'name' => $user['name'],
        'email' => $user['email'],
        'phone' => $user['phone'],
        'role' => $user['role'],
        'isAdmin' => $user['role'] === 'admin',
    ];
}

function auth_current_user(PDO $pdo): ?array
{
    $userId = $_SESSION['user_id'] ?? null;
    if (!is_int($userId) && !ctype_digit((string) $userId)) {
        return null;
    }

    $statement = $pdo->prepare(
        'SELECT id, name, email, phone, role
         FROM users
         WHERE id = :id AND is_active = 1
         LIMIT 1'
    );
    $statement->execute(['id' => (int) $userId]);
    $user = $statement->fetch();
    if (!$user) {
        unset($_SESSION['user_id']);
        return null;
    }

    return auth_user_payload($user);
}

function auth_require_csrf(): void
{
    $provided = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    $expected = $_SESSION['csrf_token'] ?? '';
    if (!is_string($provided) || !is_string($expected) || $expected === '' || !hash_equals($expected, $provided)) {
        api_json(['error' => 'Session expired. Refresh the page and try again.'], 403);
    }
}

function auth_read_json(): array
{
    $contentType = strtolower(trim(explode(';', $_SERVER['CONTENT_TYPE'] ?? '')[0]));
    if ($contentType !== 'application/json') {
        api_json(['error' => 'Send the request as JSON.'], 415);
    }

    $raw = file_get_contents('php://input');
    if ($raw === false || strlen($raw) > 8192) {
        api_json(['error' => 'Request body is invalid or too large.'], 413);
    }
    try {
        $data = json_decode($raw, true, 16, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        api_json(['error' => 'Request body must contain valid JSON.'], 400);
    }
    if (!is_array($data)) {
        api_json(['error' => 'Request body must be a JSON object.'], 400);
    }

    return $data;
}

function auth_require_method(string $method): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== $method) {
        header('Allow: ' . $method);
        api_json(['error' => 'Method not allowed.'], 405);
    }
}

function auth_api_fail(Throwable $error): never
{
    error_log('[Charles Tech Auth API] ' . $error->getMessage());
    api_json(['error' => 'The authentication service is temporarily unavailable.'], 500);
}
