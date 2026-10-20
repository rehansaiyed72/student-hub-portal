<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$errors = [];
$success = "";

function output($value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["name"] ?? "");
    $email = strtolower(trim($_POST["email"] ?? ""));
    $mobile = trim($_POST["mobile"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirmPassword = $_POST["confirmPassword"] ?? "";
    $course = $_POST["course"] ?? "";
    $year = $_POST["year"] ?? "";
    $gender = $_POST["gender"] ?? "";
    $terms = $_POST["terms"] ?? "";

    // Backend validation
    if ($name === "" || strlen($name) > 400)  {
        $errors[] = "Enter a name of up to 100 characters.";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
        $errors[] = "Enter a valid email address.";
    }

    if (!preg_match('/^[0-9]{10}$/', $mobile)) {
        $errors[] = "Mobile number must contain exactly 10 digits.";
    }

    if (strlen($password) < 8) {
        $errors[] = "Password must contain at least 8 characters.";
    }

    if ($password !== $confirmPassword) {
        $errors[] = "Passwords do not match.";
    }

    if (!in_array($course, ["CE", "IT", "CSE", "AI"], true)) {
        $errors[] = "Select a valid course.";
    }

    if (!in_array($year, ["1", "2", "3", "4"], true)) {
        $errors[] = "Select a valid year.";
    }

    if (!in_array($gender, ["Male", "Female", "Other"], true)) {
        $errors[] = "Select a valid gender.";
    }

    if ($terms !== "accepted") {
        $errors[] = "Accept the Terms and Conditions.";
    }

    if (!$errors) {
        $dbUser = getenv("STUDENTHUB_DB_USER");
        $dbPassword = getenv("STUDENTHUB_DB_PASSWORD");

        if ($dbUser === false || $dbPassword === false) {
            $errors[] = "Database configuration is unavailable.";
        } else {
            try {
                mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

                $conn = new mysqli(
                    "127.0.0.1",
                    $dbUser,
                    $dbPassword,
                    "studenthub"
                );
                $conn->set_charset("utf8mb4");

                // Check duplicate email
                $check = $conn->prepare(
                    "SELECT student_id FROM students WHERE email = ?"
                );
                $check->bind_param("s", $email);
                $check->execute();
                $check->store_result();

                if ($check->num_rows > 0) {
                    $errors[] = "This email is already registered.";
                }

                $check->close();

                if (!$errors) {
                    $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                    $yearNumber = (int)$year;

                    $insert = $conn->prepare(
                        "INSERT INTO students
                        (name, email, mobile, password_hash, course, `year`, gender)
                        VALUES (?, ?, ?, ?, ?, ?, ?)"
                    );

                    $insert->bind_param(
                        "sssssis",
                        $name,
                        $email,
                        $mobile,
                        $passwordHash,
                        $course,
                        $yearNumber,
                        $gender
                    );

                    $insert->execute();
                    $insert->close();
                    $success = "Registration successful! Your account was created.";
                }

                $conn->close();
            } catch (mysqli_sql_exception $exception) {
                error_log("Practical 9 registration database error: " . $exception->getMessage());

                if ((int)$exception->getCode() === 1062) {
                    $errors[] = "This email is already registered.";
                } else {
                    $errors[] = "Registration could not be completed. Check the database configuration.";
                }
            }
        }
    }
} else {
    http_response_code(405);
    $errors[] = "Please submit the registration form.";
}

if ($errors) {
    http_response_code($_SERVER["REQUEST_METHOD"] === "POST" ? 422 : 405);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Practical 9 Registration Result</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <main class="registration-container">
        <?php if ($success !== ""): ?>
            <h1>Registration Successful</h1>
            <p><?= output($success) ?></p>
            <a href="../html/prac9-regestration.html">Register another user</a>
        <?php else: ?>
            <h1>Registration Failed</h1>
            <?php foreach ($errors as $error): ?>
                <p><?= output($error) ?></p>
            <?php endforeach; ?>
            <a href="../html/prac9-regestration.html">Back to Registration</a>
        <?php endif; ?>
    </main>
</body>
</html>
