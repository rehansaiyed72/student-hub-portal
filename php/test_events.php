<?php

$events = [];
$queryError = false;

try {
    require_once __DIR__ . "/db.php";

    $stmt = $pdo->prepare(
        "SELECT title, event_date, venue, description
         FROM events
         WHERE event_id >= :first_event_id
         ORDER BY event_date, event_id"
    );
    $stmt->bindValue(":first_event_id", 1, PDO::PARAM_INT);
    $stmt->execute();
    $events = $stmt->fetchAll();
} catch (PDOException | RuntimeException $exception) {
    if ($exception instanceof PDOException) {
        error_log("StudentHub event query failed: " . $exception->getMessage());
    }
    $queryError = true;
}

function escapeEventValue($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Student Hub Events</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <main class="registration-container">
        <h1>Events</h1>

        <?php if ($queryError): ?>
            <p>Events could not be loaded. Check the database configuration.</p>
        <?php elseif (count($events) === 0): ?>
            <p>No events are available.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Date</th>
                        <th>Venue</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($events as $event): ?>
                        <tr>
                            <td><?= escapeEventValue($event["title"]) ?></td>
                            <td><?= escapeEventValue($event["event_date"]) ?></td>
                            <td><?= escapeEventValue($event["venue"]) ?></td>
                            <td><?= escapeEventValue($event["description"]) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>
</body>
</html>
