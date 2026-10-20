<?php
declare(strict_types=1);

require_once __DIR__ . '/session.php';
studentHubStartSession();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    exit('Use the logout form to end your session.');
}

if (!studentHubHasValidCsrfToken($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    exit('The logout request could not be verified.');
}

if (isset($_COOKIE[STUDENTHUB_REMEMBER_COOKIE])) {
    studentHubRevokeRememberCookie(studentHubPdo());
} elseif (!studentHubExpireRememberCookie()) {
    throw new RuntimeException('Could not expire the remember-me cookie.');
}

studentHubDestroySession();
header('Location: ../html/login.html?logged_out=1');
exit;
