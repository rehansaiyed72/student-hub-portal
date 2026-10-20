<?php
declare(strict_types=1);

require_once __DIR__ . '/session-check.php';
studentHubRequireRole('student');

$studentName = htmlspecialchars(
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
    <title>Student Dashboard - StudentHub</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header>
        <h1>Student Hub Portal</h1>
    </header>
    <nav>
        <a href="student-dashboard.php">Home</a>
        <form action="logout.php" method="post" style="display:inline">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            <button type="submit">Logout</button>
        </form>
    </nav>
    <main>
        <section>
            <h2>Welcome, <?= $studentName ?></h2>
            <p>You are signed in to the student dashboard.</p>
        </section>
        <section>
            <h3>Student Information</h3>
            <p>Student ID: <?= (int) $_SESSION['student_id'] ?></p>
            <p>Role: Student</p>
        </section>
        <aside>
            <h3>Notice Board</h3>
            <p>Check the portal for your latest academic notices.</p>
        </aside>
    </main>
</body>
</html>
