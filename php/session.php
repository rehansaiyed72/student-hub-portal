<?php
declare(strict_types=1);

const STUDENTHUB_SESSION_TIMEOUT = 900;
const STUDENTHUB_REMEMBER_COOKIE = 'studenthub_remember';
const STUDENTHUB_REMEMBER_LIFETIME = 2592000;

function studentHubIsHttps(): bool
{
    return isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== '' && $_SERVER['HTTPS'] !== 'off';
}

function studentHubStartSession(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => studentHubIsHttps(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    if (!session_start()) {
        throw new RuntimeException('Could not start the session.');
    }
}

function studentHubPdo(): PDO
{
    if (!isset($GLOBALS['pdo'])) {
        require __DIR__ . '/db.php';
        if (!isset($pdo) || !$pdo instanceof PDO) {
            throw new RuntimeException('Database connection is unavailable.');
        }
        $GLOBALS['pdo'] = $pdo;
    }

    if (!$GLOBALS['pdo'] instanceof PDO) {
        throw new RuntimeException('Database connection is unavailable.');
    }

    return $GLOBALS['pdo'];
}

function studentHubSetRememberCookie(string $token): bool
{
    return setcookie(STUDENTHUB_REMEMBER_COOKIE, $token, [
        'expires' => time() + STUDENTHUB_REMEMBER_LIFETIME,
        'path' => '/',
        'secure' => studentHubIsHttps(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function studentHubExpireRememberCookie(): bool
{
    return setcookie(STUDENTHUB_REMEMBER_COOKIE, '', [
        'expires' => time() - 3600,
        'path' => '/',
        'secure' => studentHubIsHttps(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
}

function studentHubIssueRememberToken(PDO $pdo, int $studentId): void
{
    $token = bin2hex(random_bytes(32));
    $tokenHash = hash('sha256', $token);

    $insert = $pdo->prepare(
        'INSERT INTO remember_tokens (student_id, token_hash, expires_at)
         VALUES (?, ?, DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 30 DAY))'
    );
    $insert->execute([$studentId, $tokenHash]);

    if (!studentHubSetRememberCookie($token)) {
        $delete = $pdo->prepare('DELETE FROM remember_tokens WHERE token_hash = ?');
        $delete->execute([$tokenHash]);
        throw new RuntimeException('Could not set the remember-me cookie.');
    }
}

function studentHubRevokeRememberCookie(PDO $pdo): void
{
    $token = $_COOKIE[STUDENTHUB_REMEMBER_COOKIE] ?? '';

    if (is_string($token) && preg_match('/\A[a-f0-9]{64}\z/', $token) === 1) {
        $delete = $pdo->prepare('DELETE FROM remember_tokens WHERE token_hash = ?');
        $delete->execute([hash('sha256', $token)]);
    }

    if (!studentHubExpireRememberCookie()) {
        throw new RuntimeException('Could not expire the remember-me cookie.');
    }

    unset($_COOKIE[STUDENTHUB_REMEMBER_COOKIE]);
}

function studentHubRestoreRememberedSession(PDO $pdo): bool
{
    $token = $_COOKIE[STUDENTHUB_REMEMBER_COOKIE] ?? '';
    if (!is_string($token) || preg_match('/\A[a-f0-9]{64}\z/', $token) !== 1) {
        if (!studentHubExpireRememberCookie()) {
            throw new RuntimeException('Could not expire the invalid remember-me cookie.');
        }
        return false;
    }

    $tokenHash = hash('sha256', $token);
    $pdo->beginTransaction();

    try {
        $lookup = $pdo->prepare(
            'SELECT s.student_id, s.name, s.email, s.role
             FROM remember_tokens AS t
             INNER JOIN students AS s ON s.student_id = t.student_id
             WHERE t.token_hash = ? AND t.expires_at > CURRENT_TIMESTAMP
             LIMIT 1
             FOR UPDATE'
        );
        $lookup->execute([$tokenHash]);
        $student = $lookup->fetch();

        if (!$student || !in_array($student['role'], ['student', 'admin'], true)) {
            $delete = $pdo->prepare('DELETE FROM remember_tokens WHERE token_hash = ?');
            $delete->execute([$tokenHash]);
            $pdo->commit();
            if (!studentHubExpireRememberCookie()) {
                throw new RuntimeException('Could not expire the invalid remember-me cookie.');
            }
            unset($_COOKIE[STUDENTHUB_REMEMBER_COOKIE]);
            return false;
        }

        $delete = $pdo->prepare('DELETE FROM remember_tokens WHERE token_hash = ?');
        $delete->execute([$tokenHash]);

        $replacement = bin2hex(random_bytes(32));
        $replacementHash = hash('sha256', $replacement);
        $insert = $pdo->prepare(
            'INSERT INTO remember_tokens (student_id, token_hash, expires_at)
             VALUES (?, ?, DATE_ADD(CURRENT_TIMESTAMP, INTERVAL 30 DAY))'
        );
        $insert->execute([(int) $student['student_id'], $replacementHash]);

        $update = $pdo->prepare(
            'UPDATE students SET last_login = CURRENT_TIMESTAMP WHERE student_id = ?'
        );
        $update->execute([(int) $student['student_id']]);

        if (!session_regenerate_id(true)) {
            throw new RuntimeException('Could not renew the session ID.');
        }
        $pdo->commit();

        if (!studentHubSetRememberCookie($replacement)) {
            $removeReplacement = $pdo->prepare(
                'DELETE FROM remember_tokens WHERE token_hash = ?'
            );
            $removeReplacement->execute([$replacementHash]);
            throw new RuntimeException('Could not rotate the remember-me cookie.');
        }

        $_SESSION['student_id'] = (int) $student['student_id'];
        $_SESSION['student_name'] = (string) $student['name'];
        $_SESSION['role'] = (string) $student['role'];
        $_SESSION['last_activity'] = time();
        return true;
    } catch (Throwable $exception) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        throw $exception;
    }
}

function studentHubDestroySession(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return;
    }

    $cookie = session_get_cookie_params();
    $_SESSION = [];
    if (!session_destroy()) {
        throw new RuntimeException('Could not destroy the session.');
    }

    if (!setcookie(session_name(), '', [
        'expires' => time() - 3600,
        'path' => $cookie['path'],
        'domain' => $cookie['domain'],
        'secure' => $cookie['secure'],
        'httponly' => $cookie['httponly'],
        'samesite' => $cookie['samesite'] ?? 'Lax',
    ])) {
        throw new RuntimeException('Could not expire the session cookie.');
    }
}

function studentHubCsrfToken(): string
{
    if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function studentHubHasValidCsrfToken(mixed $token): bool
{
    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && is_string($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

function studentHubRequireRole(string $requiredRole): void
{
    if (!in_array($requiredRole, ['student', 'admin'], true)) {
        throw new InvalidArgumentException('Unsupported StudentHub role.');
    }

    $studentId = (int) $_SESSION['student_id'];
    $query = studentHubPdo()->prepare(
        'SELECT role FROM students WHERE student_id = ? LIMIT 1'
    );
    $query->execute([$studentId]);
    $role = $query->fetchColumn();

    if (!in_array($role, ['student', 'admin'], true)) {
        studentHubDestroySession();
        http_response_code(401);
        echo 'Your account is no longer available. Please log in again.';
        exit;
    }

    $_SESSION['role'] = (string) $role;
    if ($role !== $requiredRole) {
        http_response_code(403);
        echo 'You do not have permission to access this page.';
        exit;
    }
}
