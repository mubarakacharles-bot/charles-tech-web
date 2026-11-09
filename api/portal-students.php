<?php
declare(strict_types=1);

require __DIR__ . '/auth/bootstrap.php';
auth_start_session();

function portal_student_payload(array $account): array
{
    return [
        'id' => $account['account_id'],
        'sid' => $account['student_id'],
        'u' => $account['username'],
        'name' => $account['full_name'],
        'role' => 'student',
        'mc' => (bool) $account['must_change_password'],
        'seenT' => 0,
        'seenB' => 0,
    ];
}

function portal_student_current(PDO $pdo): ?array
{
    $accountId = $_SESSION['portal_student_id'] ?? '';
    if (!is_string($accountId) || $accountId === '') {
        return null;
    }

    $statement = $pdo->prepare(
        'SELECT account_id, student_id, username, registration_number, full_name,
                class_name, phone, password_hash, legacy_salt, legacy_hash,
                must_change_password
         FROM student_portal_accounts
         WHERE account_id = :id AND is_active = 1
         LIMIT 1'
    );
    $statement->execute(['id' => $accountId]);
    return $statement->fetch() ?: null;
}

function portal_student_read_json(): array
{
    $contentType = strtolower(trim(explode(';', $_SERVER['CONTENT_TYPE'] ?? '')[0]));
    if ($contentType !== 'application/json') {
        api_json(['error' => 'Send the request as JSON.'], 415);
    }
    $raw = file_get_contents('php://input');
    if ($raw === false || strlen($raw) > 8 * 1024 * 1024) {
        api_json(['error' => 'The request is invalid or exceeds the 8 MB limit.'], 413);
    }
    try {
        $data = json_decode($raw, true, 64, JSON_THROW_ON_ERROR);
    } catch (JsonException) {
        api_json(['error' => 'The request must contain valid JSON.'], 400);
    }
    if (!is_array($data)) {
        api_json(['error' => 'The request must contain a JSON object.'], 400);
    }
    return $data;
}

function portal_student_state(PDO $pdo): array
{
    $value = $pdo->query(
        'SELECT state_json, revision FROM student_portal_state WHERE state_id = 1'
    )->fetch();
    if (!$value) {
        return [
            'state' => json_decode(
                '{"users":[],"students":[],"attendance":{},"items":[],"moves":[],"log":[],"cfg":{"start":"","hist":[]},"disc":{},"tasks":[],"subs":[],"posts":[],"sent":{},"seal":""}',
                true,
                64,
                JSON_THROW_ON_ERROR
            ),
            'revision' => 0,
        ];
    }
    $state = json_decode($value['state_json'], true, 64, JSON_THROW_ON_ERROR);
    if (!is_array($state)) {
        throw new RuntimeException('Stored Student Portal data is invalid.');
    }
    return ['state' => $state, 'revision' => (int) $value['revision']];
}

function portal_student_projection(array $state, array $account): array
{
    $sid = $account['student_id'];
    $uid = $account['account_id'];
    $student = [
        'id' => $sid,
        'name' => $account['full_name'],
        'cls' => $account['class_name'],
        'reg' => $account['registration_number'],
        'phone' => $account['phone'],
    ];

    foreach (($state['students'] ?? []) as $candidate) {
        if (($candidate['id'] ?? null) === $sid) {
            $student = array_merge($candidate, $student);
            break;
        }
    }

    $tasks = array_values(array_filter(
        $state['tasks'] ?? [],
        static fn (array $task): bool => empty($task['cls']) || ($task['cls'] ?? '') === $account['class_name']
    ));
    $taskIds = array_fill_keys(array_column($tasks, 'id'), true);

    $attendance = [];
    foreach (($state['attendance'] ?? []) as $key => $record) {
        if (($record['cls'] ?? '') !== $account['class_name'] || !isset($record['marks'][$sid])) {
            continue;
        }
        $record['marks'] = [$sid => $record['marks'][$sid]];
        $record['times'] = isset($record['times'][$sid]) ? [$sid => $record['times'][$sid]] : [];
        $attendance[$key] = $record;
    }

    $posts = array_values(array_filter(
        $state['posts'] ?? [],
        static fn (array $post): bool => empty($post['tid']) || isset($taskIds[$post['tid']])
    ));
    $disc = [];
    foreach (($state['disc'] ?? []) as $key => $score) {
        if (str_ends_with((string) $key, '|' . $sid)) {
            $disc[$key] = $score;
        }
    }
    $users = [];
    foreach (($state['users'] ?? []) as $candidate) {
        if (($candidate['id'] ?? null) === $uid || (($candidate['sid'] ?? null) === $sid
            && strtolower((string) ($candidate['u'] ?? '')) === strtolower($account['username']))) {
            $users[] = array_merge(portal_student_payload($account), [
                'seenT' => (int) ($candidate['seenT'] ?? 0),
                'seenB' => (int) ($candidate['seenB'] ?? 0),
            ]);
            break;
        }
    }
    if (!$users) {
        $users[] = portal_student_payload($account);
    }

    return [
        'users' => $users,
        'students' => [$student],
        'attendance' => $attendance,
        'items' => [],
        'moves' => [],
        'log' => [],
        'cfg' => $state['cfg'] ?? ['start' => '', 'hist' => []],
        'disc' => $disc,
        'tasks' => $tasks,
        'subs' => array_values(array_filter(
            $state['subs'] ?? [],
            static fn (array $submission): bool => ($submission['sid'] ?? null) === $sid
                && isset($taskIds[$submission['tid'] ?? ''])
        )),
        'posts' => $posts,
        'sent' => [],
        'seal' => '',
    ];
}

function portal_legacy_hash(string $password, string $salt): string
{
    $first = 0xdeadbeef;
    $second = 0x41c6ce57;
    $text = $salt . $password;
    $utf16 = iconv('UTF-8', 'UTF-16LE//IGNORE', $text);
    if ($utf16 === false) {
        return '';
    }
    foreach (unpack('v*', $utf16) ?: [] as $codeUnit) {
        $first = (($first ^ $codeUnit) * 2654435761) & 0xffffffff;
        $second = (($second ^ $codeUnit) * 1597334677) & 0xffffffff;
    }
    return dechex($first) . dechex($second);
}

function portal_student_password_matches(array $account, string $password): bool
{
    if (!empty($account['password_hash'])) {
        return password_verify($password, $account['password_hash']);
    }
    $salt = (string) ($account['legacy_salt'] ?? '');
    $expected = (string) ($account['legacy_hash'] ?? '');
    if ($salt === '' || $expected === '') {
        return false;
    }
    $actual = strlen($expected) === 64
        ? hash_pbkdf2('sha256', $password, $salt, 150000, 64)
        : portal_legacy_hash($password, $salt);
    return $actual !== '' && hash_equals($expected, $actual);
}

function portal_student_csrf(): void
{
    auth_require_csrf();
}

function portal_student_method(string $method): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== $method) {
        header('Allow: ' . $method);
        api_json(['error' => 'Method not allowed.'], 405);
    }
}

function portal_student_merge(PDO $pdo, array $state, array $account): array
{
    $sid = $account['student_id'];
    $uid = $account['account_id'];
    $student = [
        'id' => $sid,
        'name' => $account['full_name'],
        'cls' => $account['class_name'],
        'reg' => $account['registration_number'],
        'phone' => $account['phone'],
    ];
    $state['students'] = array_values(array_filter(
        $state['students'] ?? [],
        static fn (array $candidate): bool => ($candidate['id'] ?? null) !== $sid
    ));
    $state['students'][] = $student;

    $incomingUser = null;
    foreach (($state['users'] ?? []) as $candidate) {
        if (($candidate['id'] ?? null) === $uid) {
            $incomingUser = $candidate;
            break;
        }
    }
    $users = array_values(array_filter(
        $state['users'] ?? [],
        static fn (array $candidate): bool => ($candidate['id'] ?? null) !== $uid
            && ($candidate['sid'] ?? null) !== $sid
    ));
    $user = portal_student_payload($account);
    if ($incomingUser) {
        $user['seenT'] = max(0, (int) ($incomingUser['seenT'] ?? 0));
        $user['seenB'] = max(0, (int) ($incomingUser['seenB'] ?? 0));
    }
    $users[] = $user;
    $state['users'] = $users;

    $state['subs'] = array_values(array_filter(
        $state['subs'] ?? [],
        static fn (array $submission): bool => ($submission['sid'] ?? null) !== $sid
    ));
    $tasks = [];
    foreach (($state['tasks'] ?? []) as $task) {
        if (empty($task['cls']) || ($task['cls'] ?? '') === $account['class_name']) {
            $tasks[$task['id']] = true;
        }
    }
    foreach (($state['_student_subs'] ?? []) as $submission) {
        if (!isset($tasks[$submission['tid'] ?? ''])) {
            continue;
        }
        $old = null;
        foreach (($state['_existing_subs'] ?? []) as $existing) {
            if (($existing['sid'] ?? null) === $sid && ($existing['tid'] ?? null) === $submission['tid']) {
                $old = $existing;
                break;
            }
        }
        if ($old && ($old['status'] ?? '') === 'approved') {
            $state['subs'][] = $old;
            continue;
        }
        $state['subs'][] = [
            'id' => (string) ($submission['id'] ?? bin2hex(random_bytes(12))),
            'tid' => (string) $submission['tid'],
            'sid' => $sid,
            'note' => substr((string) ($submission['note'] ?? ''), 0, 800),
            'code' => substr((string) ($submission['code'] ?? ''), 0, 4000),
            'imgs' => portal_student_images($submission['imgs'] ?? []),
            'status' => 'submitted',
            'at' => time() * 1000,
            'fb' => $old['fb'] ?? '',
            'score' => $old['score'] ?? null,
            'rat' => $old['rat'] ?? 0,
        ];
    }

    $allowedPosts = [];
    foreach (($state['tasks'] ?? []) as $task) {
        if (empty($task['cls']) || ($task['cls'] ?? '') === $account['class_name']) {
            $allowedPosts[$task['id']] = true;
        }
    }
    $state['posts'] = array_values(array_filter(
        $state['posts'] ?? [],
        static fn (array $post): bool => ($post['uid'] ?? null) !== $uid
    ));
    foreach (($state['_student_posts'] ?? []) as $post) {
        $taskId = (string) ($post['tid'] ?? '');
        if ($taskId !== '' && !isset($allowedPosts[$taskId])) {
            continue;
        }
        $parent = (string) ($post['re'] ?? '');
        if ($parent !== '') {
            $parentExists = false;
            foreach ($state['posts'] as $candidate) {
                if (($candidate['id'] ?? null) === $parent && ($candidate['tid'] ?? '') === $taskId) {
                    $parentExists = true;
                    break;
                }
            }
            if (!$parentExists) {
                continue;
            }
        }
        $postId = (string) ($post['id'] ?? '');
        foreach ($state['posts'] as $candidate) {
            if (($candidate['id'] ?? null) === $postId) {
                $postId = bin2hex(random_bytes(12));
                break;
            }
        }
        $state['posts'][] = [
            'id' => $postId !== '' ? $postId : bin2hex(random_bytes(12)),
            'tid' => $taskId,
            're' => $parent,
            'uid' => $uid,
            'name' => $account['full_name'],
            'role' => 'student',
            'text' => substr((string) ($post['text'] ?? ''), 0, 600),
            'code' => substr((string) ($post['code'] ?? ''), 0, 4000),
            'imgs' => portal_student_images($post['imgs'] ?? []),
            'at' => time() * 1000,
            'pin' => false,
        ];
    }
    unset($state['_student_subs'], $state['_existing_subs'], $state['_student_posts']);
    $statement = $pdo->prepare(
        'UPDATE student_portal_accounts SET phone = :phone WHERE account_id = :id'
    );
    $statement->execute(['phone' => $student['phone'], 'id' => $uid]);
    return $state;
}

function portal_student_images(mixed $images): array
{
    if (!is_array($images)) {
        return [];
    }
    $safe = [];
    foreach (array_slice($images, 0, 4) as $image) {
        if (is_string($image) && strlen($image) <= 180000
            && preg_match('~^data:image/(?:webp|jpeg|png);base64,[A-Za-z0-9+/]+={0,2}$~', $image)) {
            $safe[] = $image;
        }
    }
    return $safe;
}

$method = $_SERVER['REQUEST_METHOD'] ?? '';
if (!in_array($method, ['GET', 'POST', 'PUT'], true)) {
    header('Allow: GET, POST, PUT');
    api_json(['error' => 'Method not allowed.'], 405);
}

try {
    $pdo = api_db();
    if ($method === 'GET') {
        $account = portal_student_current($pdo);
        if (!$account) {
            api_json(['user' => null, 'csrfToken' => $_SESSION['csrf_token']]);
        }
        $snapshot = portal_student_state($pdo);
        api_json([
            'user' => portal_student_payload($account),
            'state' => portal_student_projection($snapshot['state'], $account),
            'revision' => $snapshot['revision'],
            'csrfToken' => $_SESSION['csrf_token'],
        ]);
    }

    portal_student_csrf();
    $data = portal_student_read_json();

    if ($method === 'POST') {
        $action = (string) ($data['action'] ?? '');
        if ($action === 'logout') {
            unset($_SESSION['portal_student_id']);
            api_json(['loggedOut' => true]);
        }
        if ($action === 'register') {
            $username = strtolower(trim((string) ($data['username'] ?? '')));
            $name = trim((string) ($data['name'] ?? ''));
            $registration = trim((string) ($data['registrationNumber'] ?? ''));
            $class = trim((string) ($data['className'] ?? ''));
            $phone = preg_replace('/[\s\-().]/', '', trim((string) ($data['phone'] ?? '')));
            $password = (string) ($data['password'] ?? '');
            $nameLength = preg_match_all('/./us', $name);
            if (!preg_match('/^[a-z0-9_.]{3,30}$/', $username)
                || $name === '' || $nameLength === false || $nameLength > 60
                || strlen($registration) < 2 || strlen($registration) > 30
                || $class === '' || strlen($class) > 30
                || !is_string($phone) || !preg_match('/^\+?[0-9]{9,16}$/', $phone)
                || strlen($password) < 8 || strlen($password) > 72) {
                api_json(['error' => 'Check your username, name, registration number, class, phone and password.'], 400);
            }
            $accountId = 'portal-' . bin2hex(random_bytes(16));
            $pdo->beginTransaction();
            try {
                $placeholder = $pdo->prepare(
                    "INSERT IGNORE INTO student_portal_state (state_id, state_json, revision)
                     VALUES (1, '{}', 0)"
                );
                $placeholder->execute();
                $lockedState = $pdo->query(
                    'SELECT state_json, revision FROM student_portal_state WHERE state_id = 1 FOR UPDATE'
                )->fetch();
                $state = json_decode($lockedState['state_json'], true, 64, JSON_THROW_ON_ERROR);
                $matchedStudent = null;
                foreach (($state['students'] ?? []) as $candidate) {
                    if (is_array($candidate) && strcasecmp((string) ($candidate['reg'] ?? ''), $registration) === 0) {
                        $matchedStudent = $candidate;
                    }
                }
                if (!$matchedStudent || empty($matchedStudent['id'])) {
                    throw new DomainException('Your student record was not found. Ask the administrator to add you before registering.');
                }
                foreach (($state['students'] ?? []) as $candidate) {
                    if (!is_array($candidate) || ($candidate['id'] ?? null) === $matchedStudent['id']) {
                        continue;
                    }
                    $candidatePhone = preg_replace('/[\s\-().]/', '', (string) ($candidate['phone'] ?? ''));
                    if (is_string($candidatePhone) && $candidatePhone !== '' && $candidatePhone === $phone) {
                        throw new DomainException('That phone number is already registered to another student.');
                    }
                }
                $studentId = (string) $matchedStudent['id'];
                if (isset($matchedStudent['id']) && strlen((string) $matchedStudent['id']) > 48) {
                    throw new DomainException('The existing student record cannot be linked. Ask the administrator for help.');
                }
                foreach (($state['users'] ?? []) as $oldUser) {
                    if (is_array($oldUser) && ($oldUser['sid'] ?? null) === $studentId
                        && ($oldUser['role'] ?? '') === 'student') {
                        throw new DomainException('This registration number already has an account. Sign in or ask the administrator.');
                    }
                }
                $name = trim((string) ($matchedStudent['name'] ?? $name));
                $class = trim((string) ($matchedStudent['cls'] ?? $class));
                $statement = $pdo->prepare(
                    'INSERT INTO student_portal_accounts
                       (account_id, student_id, username, registration_number, full_name,
                        class_name, phone, password_hash)
                     VALUES (:id, :sid, :username, :registration, :name, :class, :phone, :hash)'
                );
                $statement->execute([
                    'id' => $accountId, 'sid' => $studentId, 'username' => $username,
                    'registration' => $registration, 'name' => $name, 'class' => $class,
                    'phone' => $phone, 'hash' => password_hash($password, PASSWORD_DEFAULT),
                ]);
                $state['students'] = array_values(array_filter(
                    $state['students'] ?? [],
                    static fn (array $candidate): bool => ($candidate['id'] ?? null) !== $studentId
                ));
                $state['students'][] = ['id' => $studentId, 'name' => $name, 'cls' => $class, 'reg' => $registration, 'phone' => $phone];
                $state['users'] = $state['users'] ?? [];
                $state['users'][] = portal_student_payload([
                    'account_id' => $accountId, 'student_id' => $studentId, 'username' => $username,
                    'full_name' => $name, 'must_change_password' => 0,
                ]);
                $json = json_encode($state, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
                $save = $pdo->prepare(
                    'UPDATE student_portal_state
                     SET state_json = :json, revision = revision + 1
                     WHERE state_id = 1'
                );
                $save->execute(['json' => $json]);
                $pdo->commit();
            } catch (PDOException $error) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                if ($error->getCode() === '23000') {
                    api_json(['error' => 'That username, registration number or phone is already registered.'], 409);
                }
                throw $error;
            } catch (DomainException $error) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                api_json(['error' => $error->getMessage()], 409);
            }
            session_regenerate_id(true);
            $_SESSION['portal_student_id'] = $accountId;
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $statement = $pdo->prepare(
                'SELECT account_id, student_id, username, registration_number, full_name,
                        class_name, phone, password_hash, legacy_salt, legacy_hash,
                        must_change_password
                 FROM student_portal_accounts WHERE account_id = :id'
            );
            $statement->execute(['id' => $accountId]);
            $account = $statement->fetch();
            $snapshot = portal_student_state($pdo);
            api_json([
                'user' => portal_student_payload($account),
                'state' => portal_student_projection($snapshot['state'], $account),
                'revision' => $snapshot['revision'],
                'csrfToken' => $_SESSION['csrf_token'],
            ], 201);
        }
        if ($action === 'login') {
            $identifier = strtolower(trim((string) ($data['identifier'] ?? '')));
            $password = (string) ($data['password'] ?? '');
            if ($identifier === '' || strlen($identifier) > 80 || $password === '' || strlen($password) > 1024) {
                api_json(['error' => 'Incorrect username, phone or password.'], 401);
            }
            $phoneIdentifier = preg_replace('/[\s\-().]/', '', $identifier);
            if (is_string($phoneIdentifier) && preg_match('/^0(7[2389][0-9]{7})$/', $phoneIdentifier, $matches)) {
                $phoneIdentifier = '+250' . $matches[1];
            }
            $attemptKey = hash('sha256', "portal\0" . $identifier . "\0" . (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
            $attempt = $pdo->prepare(
                'SELECT locked_until FROM auth_login_attempts
                 WHERE attempt_key = :key AND locked_until > NOW() LIMIT 1'
            );
            $attempt->execute(['key' => $attemptKey]);
            if ($attempt->fetchColumn()) {
                api_json(['error' => 'Too many failed sign-in attempts. Wait 30 seconds and try again.'], 429);
            }
            $statement = $pdo->prepare(
                'SELECT account_id, student_id, username, registration_number, full_name,
                        class_name, phone, password_hash, legacy_salt, legacy_hash,
                        must_change_password
                 FROM student_portal_accounts
                 WHERE (username = :username OR phone = :phone) AND is_active = 1
                 LIMIT 1'
            );
            $statement->execute(['username' => $identifier, 'phone' => $phoneIdentifier]);
            $account = $statement->fetch();
            if (!$account || !portal_student_password_matches($account, $password)) {
                $failure = $pdo->prepare(
                    'INSERT INTO auth_login_attempts (attempt_key, failed_attempts)
                     VALUES (:key, 1)
                     ON DUPLICATE KEY UPDATE
                       failed_attempts = IF(locked_until IS NOT NULL AND locked_until <= NOW(), 1, failed_attempts + 1),
                       locked_until = IF(
                         locked_until IS NOT NULL AND locked_until > NOW(),
                         locked_until,
                         IF(failed_attempts >= 4, DATE_ADD(NOW(), INTERVAL 30 SECOND), NULL)
                       ),
                       updated_at = NOW()'
                );
                $failure->execute(['key' => $attemptKey]);
                api_json(['error' => 'Incorrect username, phone or password.'], 401);
            }
            $clear = $pdo->prepare('DELETE FROM auth_login_attempts WHERE attempt_key = :key');
            $clear->execute(['key' => $attemptKey]);
            if (empty($account['password_hash']) || password_needs_rehash($account['password_hash'], PASSWORD_DEFAULT)) {
                $update = $pdo->prepare(
                    'UPDATE student_portal_accounts
                     SET password_hash = :hash, legacy_salt = NULL, legacy_hash = NULL
                     WHERE account_id = :id'
                );
                $update->execute(['hash' => password_hash($password, PASSWORD_DEFAULT), 'id' => $account['account_id']]);
            }
            session_regenerate_id(true);
            $_SESSION['portal_student_id'] = $account['account_id'];
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $snapshot = portal_student_state($pdo);
            api_json([
                'user' => portal_student_payload($account),
                'state' => portal_student_projection($snapshot['state'], $account),
                'revision' => $snapshot['revision'],
                'csrfToken' => $_SESSION['csrf_token'],
            ]);
        }
        if ($action === 'password') {
            $account = portal_student_current($pdo);
            if (!$account) {
                api_json(['error' => 'Sign in again to change your password.'], 401);
            }
            $old = (string) ($data['currentPassword'] ?? '');
            $new = (string) ($data['newPassword'] ?? '');
            if (strlen($new) < 8 || strlen($new) > 72 || !portal_student_password_matches($account, $old)) {
                api_json(['error' => 'Current password is incorrect or the new password is invalid.'], 400);
            }
            $update = $pdo->prepare(
                'UPDATE student_portal_accounts
                 SET password_hash = :hash, legacy_salt = NULL, legacy_hash = NULL,
                     must_change_password = 0
                 WHERE account_id = :id'
            );
            $update->execute(['hash' => password_hash($new, PASSWORD_DEFAULT), 'id' => $account['account_id']]);
            api_json(['changed' => true]);
        }
        api_json(['error' => 'Unknown portal action.'], 400);
    }

    portal_student_csrf();
    $account = portal_student_current($pdo);
    if (!$account) {
        api_json(['error' => 'Sign in again to continue.'], 401);
    }
    $payload = portal_student_read_json();
    if (!is_array($payload['state'] ?? null) || !is_int($payload['revision'] ?? null)) {
        api_json(['error' => 'The portal update is invalid. Reload and try again.'], 400);
    }

    $pdo->beginTransaction();
    $placeholder = $pdo->prepare(
        "INSERT IGNORE INTO student_portal_state (state_id, state_json, revision)
         VALUES (1, '{}', 0)"
    );
    $placeholder->execute();
    $row = $pdo->query(
        'SELECT state_json, revision FROM student_portal_state WHERE state_id = 1 FOR UPDATE'
    )->fetch();
    if (!$row || (int) $row['revision'] !== $payload['revision']) {
        $pdo->rollBack();
        api_json(['error' => 'Portal data changed on another device. Reload the page and try again.'], 409);
    }
    $state = json_decode($row['state_json'], true, 64, JSON_THROW_ON_ERROR);
    if (!is_array($state)) {
        throw new RuntimeException('Stored Student Portal data is invalid.');
    }
    $incoming = $payload['state'];
    $state['_existing_subs'] = $state['subs'] ?? [];
    foreach (['subs', 'posts', 'students', 'users'] as $key) {
        if (isset($incoming[$key]) && (!is_array($incoming[$key]) || count($incoming[$key]) > 5000)) {
            $pdo->rollBack();
            api_json(['error' => 'The portal update contains invalid data.'], 400);
        }
        foreach (($incoming[$key] ?? []) as $record) {
            if (!is_array($record)) {
                $pdo->rollBack();
                api_json(['error' => 'The portal update contains invalid records.'], 400);
            }
        }
    }
    $incomingSubs = array_values(array_filter(
        $incoming['subs'] ?? [],
        static fn (array $submission): bool => ($submission['sid'] ?? null) === $account['student_id']
    ));
    $incomingPosts = array_values(array_filter(
        $incoming['posts'] ?? [],
        static fn (array $post): bool => ($post['uid'] ?? null) === $account['account_id']
    ));
    if (count($incomingSubs) > 500 || count($incomingPosts) > 2000) {
        $pdo->rollBack();
        api_json(['error' => 'The portal update contains too many student records.'], 400);
    }
    $state['_student_subs'] = $incomingSubs;
    $state['_student_posts'] = $incomingPosts;
    $state['attendance'] = $state['attendance'] ?? [];
    $state['tasks'] = $state['tasks'] ?? [];
    $state['users'] = $state['users'] ?? [];
    $state['students'] = $state['students'] ?? [];
    $state['subs'] = $state['subs'] ?? [];
    $state['posts'] = $state['posts'] ?? [];
    $state['_existing_subs'] = array_values(array_filter(
        $state['_existing_subs'],
        static fn (array $submission): bool => ($submission['sid'] ?? null) === $account['student_id']
    ));
    foreach (($incoming['users'] ?? []) as $incomingUser) {
        if (($incomingUser['id'] ?? null) !== $account['account_id']) {
            continue;
        }
        foreach ($state['users'] as &$storedUser) {
            if (($storedUser['id'] ?? null) === $account['account_id']) {
                $storedUser['seenT'] = max(0, (int) ($incomingUser['seenT'] ?? 0));
                $storedUser['seenB'] = max(0, (int) ($incomingUser['seenB'] ?? 0));
                break;
            }
        }
        unset($storedUser);
        break;
    }

    $student = [];
    foreach ($incoming['students'] ?? [] as $candidate) {
        if (($candidate['id'] ?? null) === $account['student_id']) {
            $student = $candidate;
            break;
        }
    }
    $phone = preg_replace('/[\s\-().]/', '', (string) ($student['phone'] ?? $account['phone']));
    if (!is_string($phone) || !preg_match('/^\+?[0-9]{9,16}$/', $phone)) {
        $pdo->rollBack();
        api_json(['error' => 'Enter a valid phone number.'], 400);
    }
    $updatePhone = $pdo->prepare(
        'UPDATE student_portal_accounts SET phone = :phone WHERE account_id = :id'
    );
    $updatePhone->execute(['phone' => $phone, 'id' => $account['account_id']]);
    $account['phone'] = $phone;

    $state = portal_student_merge($pdo, $state, $account);
    $json = json_encode($state, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
    $save = $pdo->prepare(
        'UPDATE student_portal_state SET state_json = :json, revision = revision + 1 WHERE state_id = 1'
    );
    $save->execute(['json' => $json]);
    $revision = (int) $row['revision'] + 1;
    $pdo->commit();
    api_json(['saved' => true, 'revision' => $revision]);
} catch (PDOException $error) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    if ($error->getCode() === '23000') {
        api_json(['error' => 'That phone number is already used by another student account.'], 409);
    }
    auth_api_fail($error);
} catch (Throwable $error) {
    if (isset($pdo) && $pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    auth_api_fail($error);
}
