<?php
declare(strict_types=1);

require_once __DIR__ . '/session.php';

studentHubStartSession();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../html/login.html');
    exit;
}

$email = $_POST['email'] ?? null;
$password = $_POST['password'] ?? null;
$remember = $_POST['remember'] ?? null;

if (
    !is_string($email)
    || !is_string($password)
    || !filter_var(trim($email), FILTER_VALIDATE_EMAIL)
    || strlen(trim($email)) > 254
    || $password === ''
    || strlen($password) > 4096
    || ($remember !== null && $remember !== '1')
) {
    header('Location: ../html/login.html?error=1');
    exit;
}

try {
    $pdo = studentHubPdo();
    $lookup = $pdo->prepare(
        'SELECT student_id, name, email, password_hash, role
         FROM students
         WHERE email = ?
         LIMIT 1'
    );
    $lookup->execute([trim($email)]);
    $student = $lookup->fetch();

    if (
        !$student
        || !is_string($student['password_hash'])
        || !password_verify($password, $student['password_hash'])
        || !in_array($student['role'], ['student', 'admin'], true)
    ) {
        header('Location: ../html/login.html?error=1');
        exit;
    }

    if (isset($_COOKIE[STUDENTHUB_REMEMBER_COOKIE])) {
        studentHubRevokeRememberCookie($pdo);
    }

    $update = $pdo->prepare(
        'UPDATE students SET last_login = CURRENT_TIMESTAMP WHERE student_id = ?'
    );
    $update->execute([(int) $student['student_id']]);

    if (!session_regenerate_id(true)) {
        throw new RuntimeException('Could not renew the session ID.');
    }

    if ($remember === '1') {
        studentHubIssueRememberToken($pdo, (int) $student['student_id']);
    }

    $_SESSION['student_id'] = (int) $student['student_id'];
    $_SESSION['student_name'] = (string) $student['name'];
    $_SESSION['role'] = (string) $student['role'];
    $_SESSION['last_activity'] = time();
    studentHubCsrfToken();

    if ($student['role'] === 'admin') {
        header('Location: admin-dashboard.php');
    } else {
        header('Location: student-dashboard.php');
    }
    exit;
} catch (Throwable $exception) {
    error_log('StudentHub login failed (' . get_class($exception) . ').');
    http_response_code(500);
    echo 'A server error occurred. Please try again later.';
}
