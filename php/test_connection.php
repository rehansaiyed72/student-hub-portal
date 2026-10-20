<?php

$databaseName = null;
$connectionError = false;

try {
    require_once __DIR__ . "/db.php";
    $databaseName = $pdo->query("SELECT DATABASE()")->fetchColumn();

    if (!is_string($databaseName) || $databaseName === "") {
        throw new RuntimeException("No database is selected.");
    }
} catch (PDOException | RuntimeException $exception) {
    if ($exception instanceof PDOException) {
        error_log("StudentHub connection test failed: " . $exception->getMessage());
    }
    $connectionError = true;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Hub Database Connection Test</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <main class="registration-container">
        <h1>Database Connection Test</h1>
        <?php if ($connectionError): ?>
            <p>Database connection failed. Check the local database configuration.</p>
        <?php else: ?>
            <p>Database connection successful.</p>
            <p>Connected database: <?= htmlspecialchars($databaseName, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8") ?></p>
        <?php endif; ?>
    </main>
</body>
</html>
