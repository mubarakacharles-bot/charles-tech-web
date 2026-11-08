<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$email = strtolower(trim($argv[1] ?? ''));
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fwrite(STDERR, "Usage: php database/promote_admin.php email@example.com\n");
    exit(2);
}

$configPath = getenv('CHTS_DB_CONFIG') ?: 'C:\\xampp\\private\\charles-tech-db.php';
if (!is_file($configPath)) {
    fwrite(STDERR, "Private database configuration was not found.\n");
    exit(1);
}

$config = require $configPath;
$rootPassword = getenv('CHTS_DB_ROOT_PASSWORD');
if ($rootPassword === false) {
    $rootPassword = '';
}

try {
    $pdo = new PDO(
        sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $config['host'], $config['database']),
        'root',
        $rootPassword,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_EMULATE_PREPARES => false]
    );
    $statement = $pdo->prepare("UPDATE users SET role = 'admin' WHERE email = :email AND is_active = 1");
    $statement->execute(['email' => $email]);
    if ($statement->rowCount() !== 1) {
        $check = $pdo->prepare('SELECT role FROM users WHERE email = :email AND is_active = 1');
        $check->execute(['email' => $email]);
        if ($check->fetchColumn() !== 'admin') {
            fwrite(STDERR, "No active account was promoted. Register that account on the website first.\n");
            exit(1);
        }
    }

    fwrite(STDOUT, "Administrator role granted to {$email}. Sign out and sign in again to refresh the session.\n");
} catch (Throwable $error) {
    fwrite(STDERR, "Could not promote the account: " . $error->getMessage() . "\n");
    exit(1);
} finally {
    $rootPassword = null;
}
