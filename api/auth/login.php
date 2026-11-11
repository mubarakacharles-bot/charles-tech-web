<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
auth_require_method('POST');
auth_start_session();
auth_require_csrf();
$data = auth_read_json();

$email = strtolower(trim((string) ($data['email'] ?? '')));
$password = (string) ($data['password'] ?? '');
if (strlen($email) > 254 || (!filter_var($email, FILTER_VALIDATE_EMAIL) && !preg_match('/^[a-z0-9_.]{3,30}$/', $email)) || $password === '' || strlen($password) > 1024) {
    api_json(['error' => 'Incorrect email or password.'], 401);
}

$remoteAddress = (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
$attemptKey = hash('sha256', $email . "\0" . $remoteAddress);

try {
    $pdo = api_db();
    $attempt = $pdo->prepare(
        'SELECT locked_until
         FROM auth_login_attempts
         WHERE attempt_key = :attempt_key
           AND locked_until > NOW()
         LIMIT 1'
    );
    $attempt->execute(['attempt_key' => $attemptKey]);
    if ($attempt->fetchColumn()) {
        api_json(['error' => 'Too many failed attempts. Wait 30 seconds and try again.'], 429);
    }

    $statement = $pdo->prepare(
        'SELECT id, name, email, phone, password_hash, role
         FROM users
         WHERE (email = :email OR username = :username) AND is_active = 1
         LIMIT 1'
    );
    $statement->execute(['email' => $email, 'username' => $email]);
    $user = $statement->fetch();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        $recordFailure = $pdo->prepare(
            'INSERT INTO auth_login_attempts (attempt_key, failed_attempts)
             VALUES (:attempt_key, 1)
             ON DUPLICATE KEY UPDATE
               failed_attempts = IF(locked_until IS NOT NULL AND locked_until <= NOW(), 1, failed_attempts + 1),
               locked_until = IF(
                 locked_until IS NOT NULL AND locked_until > NOW(),
                 locked_until,
                 IF(failed_attempts >= 4, DATE_ADD(NOW(), INTERVAL 30 SECOND), NULL)
               ),
               updated_at = NOW()'
        );
        $recordFailure->execute(['attempt_key' => $attemptKey]);
        $attempt->execute(['attempt_key' => $attemptKey]);
        if ($attempt->fetchColumn()) {
            api_json(['error' => 'Too many failed attempts. Wait 30 seconds and try again.'], 429);
        }
        api_json(['error' => 'Incorrect email or password.'], 401);
    }

    $clearAttempts = $pdo->prepare('DELETE FROM auth_login_attempts WHERE attempt_key = :attempt_key');
    $clearAttempts->execute(['attempt_key' => $attemptKey]);
    if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
        $rehash = $pdo->prepare('UPDATE users SET password_hash = :password_hash WHERE id = :id');
        $rehash->execute([
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'id' => (int) $user['id'],
        ]);
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    api_json([
        'user' => auth_user_payload($user),
        'csrfToken' => $_SESSION['csrf_token'],
    ]);
} catch (Throwable $error) {
    auth_api_fail($error);
}
