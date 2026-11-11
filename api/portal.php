<?php
declare(strict_types=1);

require __DIR__ . '/auth/bootstrap.php';
require_once __DIR__ . '/portal-validation.php';
auth_start_session();

function portal_require_admin(PDO $pdo): array
{
    $user = auth_current_user($pdo);
    if (!$user || !$user['isAdmin']) {
        api_json(['error' => 'Administrator access is required.'], 403);
    }

    return $user;
}

$method = $_SERVER['REQUEST_METHOD'] ?? '';
if (!in_array($method, ['GET', 'PUT', 'POST'], true)) {
    header('Allow: GET, POST, PUT');
    api_json(['error' => 'Method not allowed.'], 405);
}

try {
    $pdo = api_db();
    $user = portal_require_admin($pdo);

    if ($method === 'GET') {
        api_json([
            'state' => $pdo->query(
                'SELECT state_json, revision, updated_at
                 FROM student_portal_state
                 WHERE state_id = 1'
            )->fetch() ?: null,
            'user' => $user,
            'csrfToken' => $_SESSION['csrf_token'],
        ]);
    }

    auth_require_csrf();
    if ($method === 'POST') {
        $data = auth_read_json();
        $accountId = (string) ($data['accountId'] ?? '');
        if ($accountId === '' || strlen($accountId) > 48) {
            api_json(['error' => 'The student account was not found.'], 404);
        }
        if (($data['action'] ?? '') === 'reset-password') {
            $password = (string) ($data['password'] ?? '');
            if (strlen($password) < 8 || strlen($password) > 72) {
                api_json(['error' => 'Temporary password must be between 8 and 72 characters.'], 400);
            }
            $statement = $pdo->prepare(
                'UPDATE student_portal_accounts
                 SET password_hash = :hash, legacy_salt = NULL, legacy_hash = NULL,
                     must_change_password = 1
                 WHERE account_id = :id AND is_active = 1'
            );
            $statement->execute(['hash' => password_hash($password, PASSWORD_DEFAULT), 'id' => $accountId]);
            if ($statement->rowCount() !== 1) {
                api_json(['error' => 'The student account was not found in the database. Import the portal data first.'], 404);
            }
            api_json(['updated' => true]);
        }
        if (($data['action'] ?? '') === 'delete-student') {
            $statement = $pdo->prepare(
                'UPDATE student_portal_accounts SET is_active = 0 WHERE account_id = :id AND is_active = 1'
            );
            $statement->execute(['id' => $accountId]);
            if ($statement->rowCount() !== 1) {
                api_json(['error' => 'The student account was not found in the database.'], 404);
            }
            api_json(['updated' => true]);
        }
        if (($data['action'] ?? '') === 'delete-student-record') {
            $statement = $pdo->prepare(
                'UPDATE student_portal_accounts SET is_active = 0 WHERE student_id = :id AND is_active = 1'
            );
            $statement->execute(['id' => $accountId]);
            api_json(['updated' => true]);
        }
        api_json(['error' => 'Unknown portal action.'], 400);
    }

    $contentType = strtolower(trim(explode(';', $_SERVER['CONTENT_TYPE'] ?? '')[0]));
    if ($contentType !== 'application/json') {
        api_json(['error' => 'Send the request as JSON.'], 415);
    }

    $raw = file_get_contents('php://input');
    if ($raw === false || strlen($raw) > 8 * 1024 * 1024) {
        api_json(['error' => 'The portal backup is invalid or exceeds the 8 MB limit.'], 413);
    }

    try {
        $payload = json_decode($raw, true, 64, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        api_json(['error' => 'The portal backup must contain valid JSON.'], 400);
    }
    if (!is_array($payload) || !is_array($payload['state'] ?? null)) {
        api_json(['error' => 'The request must contain a portal state object.'], 400);
    }

    $state = $payload['state'];
    foreach (['users', 'students', 'items', 'moves', 'tasks', 'subs', 'posts', 'log'] as $key) {
        if (!isset($state[$key]) || !is_array($state[$key])) {
            api_json(['error' => 'The portal backup is missing required data.'], 400);
        }
    }
    foreach (['attendance', 'disc', 'cfg'] as $key) {
        if (!isset($state[$key]) || !is_array($state[$key])) {
            api_json(['error' => 'The portal backup is missing required data.'], 400);
        }
    }
    if (count($state['users']) > 5000 || count($state['students']) > 5000
        || count($state['items']) > 5000 || count($state['tasks']) > 5000) {
        api_json(['error' => 'The portal backup contains too many records.'], 400);
    }

    $validationError = portal_validate_records($state);
    if ($validationError !== null) {
        api_json(['error' => $validationError], 400);
    }

    $stateJson = json_encode($state, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    $expectedRevision = $payload['revision'] ?? null;
    if (!is_int($expectedRevision) || $expectedRevision < 0) {
        api_json(['error' => 'The database revision is missing. Reload the portal and try again.'], 400);
    }

    $pdo->beginTransaction();
    $createSnapshot = $pdo->prepare(
        "INSERT IGNORE INTO student_portal_state (state_id, state_json, revision)
         VALUES (1, '{}', 0)"
    );
    $createSnapshot->execute();
    $current = $pdo->query(
        'SELECT revision FROM student_portal_state WHERE state_id = 1 FOR UPDATE'
    )->fetchColumn();
    if ($current === false || (int) $current !== $expectedRevision) {
        $pdo->rollBack();
        api_json(['error' => 'The database copy changed in another session. Load it again before saving.'], 409);
    }

    $studentsById = [];
    foreach ($state['students'] as $student) {
        if (is_array($student) && isset($student['id'])) {
            $studentsById[(string) $student['id']] = $student;
        }
    }
    $importAccount = $pdo->prepare(
        'INSERT INTO student_portal_accounts
           (account_id, student_id, username, registration_number, full_name,
            class_name, phone, legacy_salt, legacy_hash, must_change_password)
         VALUES
           (:id, :sid, :username, :registration, :name, :class, :phone, :salt, :legacy_hash, :must_change)
         ON DUPLICATE KEY UPDATE
           username = VALUES(username),
           registration_number = VALUES(registration_number),
           full_name = VALUES(full_name),
           class_name = VALUES(class_name),
           phone = VALUES(phone),
           legacy_salt = IF(password_hash IS NULL AND legacy_salt IS NULL, VALUES(legacy_salt), legacy_salt),
           legacy_hash = IF(password_hash IS NULL AND legacy_hash IS NULL, VALUES(legacy_hash), legacy_hash)'
    );
    foreach ($state['users'] as $portalUser) {
        if (!is_array($portalUser)) {
            continue;
        }
        if (($portalUser['role'] ?? '') !== 'student' || empty($portalUser['id']) || empty($portalUser['sid'])
            || !isset($studentsById[(string) $portalUser['sid']])) {
            continue;
        }
        $student = $studentsById[(string) $portalUser['sid']];
        $username = strtolower(trim((string) ($portalUser['u'] ?? '')));
        $registration = trim((string) ($student['reg'] ?? ''));
        $name = trim((string) ($student['name'] ?? $portalUser['name'] ?? ''));
        $class = trim((string) ($student['cls'] ?? ''));
        $phone = preg_replace('/[\s\-().]/', '', (string) ($student['phone'] ?? ''));
        if (!preg_match('/^[a-z0-9_.]{3,30}$/', $username)
            || strlen((string) $portalUser['id']) > 48 || strlen((string) $portalUser['sid']) > 48
            || $registration === '' || strlen($registration) > 30
            || $name === '' || strlen($name) > 60 || $class === '' || strlen($class) > 30
            || !is_string($phone) || !preg_match('/^\+?[0-9]{9,16}$/', $phone)) {
            continue;
        }
        $salt = (string) ($portalUser['salt'] ?? '');
        $legacyHash = (string) ($portalUser['h'] ?? '');
        $importAccount->execute([
            'id' => (string) $portalUser['id'],
            'sid' => (string) $portalUser['sid'],
            'username' => $username,
            'registration' => $registration,
            'name' => $name,
            'class' => $class,
            'phone' => $phone,
            'salt' => $salt !== '' ? $salt : null,
            'legacy_hash' => $legacyHash !== '' ? $legacyHash : null,
            'must_change' => !empty($portalUser['mc']) ? 1 : 0,
        ]);
    }

    $statement = $pdo->prepare(
        'UPDATE student_portal_state
         SET state_json = :state_json, revision = revision + 1, updated_by = :updated_by
         WHERE state_id = 1'
    );
    $statement->execute(['state_json' => $stateJson, 'updated_by' => $user['id']]);
    $revision = $expectedRevision + 1;
    $pdo->commit();
    api_json(['saved' => true, 'revision' => $revision]);
} catch (Throwable $error) {
    auth_api_fail($error);
}
