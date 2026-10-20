<?php
declare(strict_types=1);

require_once __DIR__ . '/session-check.php';
studentHubRequireRole('admin');

$adminName = htmlspecialchars(
    (string) $_SESSION['student_name'],
    ENT_QUOTES | ENT_SUBSTITUTE,
    'UTF-8'
);
$csrfToken = htmlspecialchars(studentHubCsrfToken(), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard - StudentHub</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header>
        <h1>Student Hub Portal</h1>
    </header>
    <nav>
        <a href="admin-dashboard.php">Admin Home</a>
        <form action="logout.php" method="post" style="display:inline">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            <button type="submit">Logout</button>
        </form>
    </nav>
    <main>
        <section>
            <h2>Welcome, <?= $adminName ?></h2>
            <p>You are signed in to the administrator dashboard.</p>
        </section>
        <section>
            <h3>Administrator Access</h3>
            <p>This page is available only to accounts with the admin role.</p>
        </section>
    </main>
</body>
</html>
