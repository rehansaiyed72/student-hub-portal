<?php
declare(strict_types=1);

require_once __DIR__ . '/session.php';

studentHubStartSession();

if (isset($_SESSION['student_id'])) {
    $lastActivity = $_SESSION['last_activity'] ?? null;
    if (!is_int($lastActivity) || time() - $lastActivity >= STUDENTHUB_SESSION_TIMEOUT) {
        if (isset($_COOKIE[STUDENTHUB_REMEMBER_COOKIE])) {
            studentHubRevokeRememberCookie(studentHubPdo());
        }
        studentHubDestroySession();
        header('Location: ../html/login.html?timeout=1');
        exit;
    }
} else {
    $token = $_COOKIE[STUDENTHUB_REMEMBER_COOKIE] ?? null;
    if ($token === null || !studentHubRestoreRememberedSession(studentHubPdo())) {
        header('Location: ../html/login.html?error=1');
        exit;
    }
}

$_SESSION['last_activity'] = time();
