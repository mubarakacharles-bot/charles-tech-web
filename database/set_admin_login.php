<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
$email = strtolower(trim($argv[1] ?? ''));
$username = strtolower(trim($argv[2] ?? ''));
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/^[a-z0-9_.]{3,30}$/', $username)) {
    fwrite(STDERR, "Usage: php database/set_admin_login.php existing-email username\n"); exit(2);
}
fwrite(STDOUT, "Enter the new password on stdin:\n");
$password = rtrim((string) fgets(STDIN), "\r\n");
if (strlen($password) < 8 || strlen($password) > 72) { fwrite(STDERR, "Password must be 8-72 bytes.\n"); exit(2); }
try {
    $config = require (getenv('CHTS_DB_CONFIG') ?: 'C:/xampp/private/charles-tech-db.php');
    $pdo = new PDO(sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $config['host'], $config['database']), 'root', getenv('CHTS_DB_ROOT_PASSWORD') ?: '', [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
    $find=$pdo->prepare('SELECT id FROM users WHERE email=:email AND is_active=1');
    $find->execute(['email'=>$email]); $id=$find->fetchColumn();
    if (!$id) throw new RuntimeException('No active account exists for this email.');
    if (!$pdo->query("SHOW COLUMNS FROM users LIKE 'username'")->fetch()) {
        $pdo->exec('ALTER TABLE users ADD COLUMN username VARCHAR(30) NULL, ADD UNIQUE KEY uq_users_username (username)');
    }
    $update=$pdo->prepare("UPDATE users SET username=:username, password_hash=:hash, role='admin' WHERE id=:id");
    $update->execute(['username'=>$username,'hash'=>password_hash($password,PASSWORD_DEFAULT),'id'=>$id]);
    $check=$pdo->prepare('SELECT username,password_hash,role FROM users WHERE id=:id');
    $check->execute(['id'=>$id]); $account=$check->fetch(PDO::FETCH_ASSOC);
    if ($account['role']!=='admin' || $account['username']!==$username || !password_verify($password,$account['password_hash'])) throw new RuntimeException('Account verification failed.');
    fwrite(STDOUT, "Administrator login updated and verified. Email sign-in still works.\n");
} catch (Throwable $error) { fwrite(STDERR, 'Account update failed: '.$error->getMessage()."\n"); exit(1); }
