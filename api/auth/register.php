<?php
declare(strict_types=1);

require __DIR__ . '/bootstrap.php';
auth_require_method('POST');
auth_start_session();
auth_require_csrf();
$data = auth_read_json();

$name = trim((string) ($data['name'] ?? ''));
$phone = trim((string) ($data['phone'] ?? ''));
$email = strtolower(trim((string) ($data['email'] ?? '')));
$password = (string) ($data['password'] ?? '');
$nameLength = preg_match_all('/./us', $name);
$phoneDigits = preg_replace('/\D/', '', $phone);

if ($name === '' || $nameLength === false || $nameLength > 150) {
    api_json(['error' => 'Enter a name of 1 to 150 characters.'], 400);
}
if ($phoneDigits === null || strlen($phoneDigits) < 9 || strlen($phone) > 40) {
    api_json(['error' => 'Enter a valid phone number.'], 400);
}
if (strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    api_json(['error' => 'Enter a valid email address.'], 400);
}
if (strlen($password) < 8 || strlen($password) > 72) {
    api_json(['error' => 'Password must be between 8 and 72 bytes.'], 400);
}

try {
    $pdo = api_db();
    $statement = $pdo->prepare(
        'INSERT INTO users (email, name, phone, password_hash, role)
         VALUES (:email, :name, :phone, :password_hash, :role)'
    );
    try {
        $statement->execute([
            'email' => $email,
            'name' => $name,
            'phone' => $phone,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'role' => 'customer',
        ]);
    } catch (PDOException $error) {
        if ($error->getCode() === '23000') {
            api_json(['error' => 'An account with that email already exists.'], 409);
        }
        throw $error;
    }

    session_regenerate_id(true);
    $_SESSION['user_id'] = (int) $pdo->lastInsertId();
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    api_json([
        'user' => [
            'id' => (int) $_SESSION['user_id'],
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'role' => 'customer',
            'isAdmin' => false,
        ],
        'csrfToken' => $_SESSION['csrf_token'],
    ], 201);
} catch (Throwable $error) {
    auth_api_fail($error);
}
