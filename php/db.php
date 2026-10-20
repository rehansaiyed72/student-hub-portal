<?php

$dbUser = getenv("STUDENTHUB_DB_USER");
$dbPassword = getenv("STUDENTHUB_DB_PASSWORD");

if ($dbUser === false || trim($dbUser) === "" || $dbPassword === false) {
    error_log(
        "StudentHub database connection failed: "
        . "STUDENTHUB_DB_USER and STUDENTHUB_DB_PASSWORD must be configured."
    );
    throw new RuntimeException("Database connection is unavailable.");
}

$dsn = "mysql:host=127.0.0.1;dbname=studenthub;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $dbUser, $dbPassword, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $exception) {
    error_log("StudentHub database connection failed: " . $exception->getMessage());
    throw new RuntimeException("Database connection is unavailable.");
}
