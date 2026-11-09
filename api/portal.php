<?php
declare(strict_types=1);

require __DIR__ . '/auth/bootstrap.php';
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
if (!in_array($method, ['GET', 'PUT'], true)) {
    header('Allow: GET, PUT');
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
