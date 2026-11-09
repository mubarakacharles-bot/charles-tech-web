<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$configPath = getenv('CHTS_DB_CONFIG') ?: 'C:\\xampp\\private\\charles-tech-db.php';
if (!is_file($configPath)) {
    fwrite(STDERR, "Private database configuration was not found.\n");
    exit(1);
}

try {
    $config = require $configPath;
    $dsn = sprintf(
        'mysql:host=%s;dbname=%s;charset=utf8mb4',
        $config['host'],
        $config['database']
    );
    $options = [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION];
    $app = new PDO($dsn, $config['username'], $config['password'], $options);
    $identity = $app->query('SELECT CURRENT_USER()')->fetchColumn();
    [$username, $host] = explode('@', (string) $identity, 2);

    $rootPassword = getenv('CHTS_DB_ROOT_PASSWORD');
    $root = new PDO($dsn, 'root', $rootPassword === false ? '' : $rootPassword, $options);
    $account = $root->quote($username) . '@' . $root->quote($host);
    $root->exec(
        'CREATE TABLE IF NOT EXISTS auth_login_attempts (
            attempt_key CHAR(64) NOT NULL,
            failed_attempts TINYINT UNSIGNED NOT NULL DEFAULT 0,
            locked_until DATETIME NULL,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (attempt_key),
            KEY idx_auth_login_attempts_updated (updated_at)
        ) ENGINE=InnoDB'
    );
    $root->exec(
        'CREATE TABLE IF NOT EXISTS student_portal_state (
            state_id TINYINT UNSIGNED NOT NULL,
            state_json LONGTEXT NOT NULL,
            revision BIGINT UNSIGNED NOT NULL DEFAULT 1,
            updated_by BIGINT UNSIGNED NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (state_id),
            CONSTRAINT chk_student_portal_state_singleton CHECK (state_id = 1),
            CONSTRAINT fk_student_portal_state_user
              FOREIGN KEY (updated_by) REFERENCES users (id)
              ON UPDATE CASCADE ON DELETE SET NULL
        ) ENGINE=InnoDB'
    );
    $root->exec(
        'CREATE TABLE IF NOT EXISTS student_portal_accounts (
            account_id VARCHAR(48) NOT NULL,
            student_id VARCHAR(48) NOT NULL,
            username VARCHAR(30) NOT NULL,
            registration_number VARCHAR(30) NOT NULL,
            full_name VARCHAR(60) NOT NULL,
            class_name VARCHAR(30) NOT NULL,
            phone VARCHAR(40) NOT NULL,
            password_hash VARCHAR(255) NULL,
            legacy_salt VARCHAR(128) NULL,
            legacy_hash VARCHAR(128) NULL,
            must_change_password TINYINT(1) NOT NULL DEFAULT 0,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (account_id),
            UNIQUE KEY uq_portal_username (username),
            UNIQUE KEY uq_portal_registration (registration_number),
            UNIQUE KEY uq_portal_phone (phone),
            UNIQUE KEY uq_portal_student (student_id),
            KEY idx_portal_active_class (is_active, class_name)
        ) ENGINE=InnoDB'
    );
    $root->exec('GRANT SELECT, INSERT ON `charles_tech`.`users` TO ' . $account);
    $root->exec('GRANT UPDATE (`password_hash`) ON `charles_tech`.`users` TO ' . $account);
    $root->exec('GRANT SELECT, INSERT, UPDATE, DELETE ON `charles_tech`.`auth_login_attempts` TO ' . $account);
    $root->exec('GRANT SELECT, INSERT, UPDATE ON `charles_tech`.`student_portal_state` TO ' . $account);
    $root->exec('GRANT SELECT, INSERT, UPDATE, DELETE ON `charles_tech`.`student_portal_accounts` TO ' . $account);
    fwrite(STDOUT, "Auth and Student Portal tables and minimum required database privileges are ready.\n");
} catch (Throwable $error) {
    fwrite(STDERR, "Could not configure authentication database access: " . $error->getMessage() . "\n");
    exit(1);
} finally {
    $rootPassword = null;
}
