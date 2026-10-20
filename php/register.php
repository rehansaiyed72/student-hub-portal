<?php

$errors = [];

function safeOutput($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");
}

function csvSafe($value) {
    $value = (string)$value;

    // Prevent spreadsheet formula injection in user-controlled fields.
    if (preg_match('/^[\s]*[=+\-@]/', $value)) {
        return "'" . $value;
    }

    return $value;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    $errors[] = "Please submit the registration form.";
} else {
    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $mobile = trim($_POST["mobile"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirmPassword = $_POST["confirmPassword"] ?? "";
    $course = $_POST["course"] ?? "";
    $year = $_POST["year"] ?? "";
    $gender = $_POST["gender"] ?? "";
    $terms = $_POST["terms"] ?? "";

    // Server-side validation
    if ($name === "" || strlen($name) > 100) {
        $errors[] = "Enter a name (maximum 100 characters).";
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
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
        $errors[] = "Please select a valid course.";
    }

    if (!in_array($year, ["1", "2", "3", "4"], true)) {
        $errors[] = "Please select a valid year.";
    }

    if (!in_array($gender, ["Male", "Female", "Other"], true)) {
        $errors[] = "Please select a gender.";
    }

    if ($terms !== "accepted") {
        $errors[] = "Please accept the Terms and Conditions.";
    }

    // Save valid registrations to CSV
    if (empty($errors)) {
        $directory = dirname(__DIR__) . "/data";

        if (!is_dir($directory) &&
            !mkdir($directory, 0755, true) &&
            !is_dir($directory)) {
            $errors[] = "Could not create the data directory.";
        } else {
            $file = $directory . "/registrations.csv";
            $handle = fopen($file, "c+");

            if ($handle === false) {
                $errors[] = "Could not open the CSV file.";
            } elseif (!flock($handle, LOCK_EX)) {
                fclose($handle);
                $errors[] = "Could not lock the CSV file.";
            } else {
                fseek($handle, 0, SEEK_END);
                $isEmpty = ftell($handle) === 0;
                $saved = true;

                if ($isEmpty) {
                    $saved = fputcsv($handle, [
                        "Name", "Email", "Mobile", "Password Hash",
                        "Course", "Year", "Gender", "Registered At"
                    ], ",", '"', "") !== false;
                }

                if ($saved) {
                    $passwordHash = password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );

                    $saved = fputcsv($handle, [
                        csvSafe($name),
                        csvSafe($email),
                        csvSafe($mobile),
                        $passwordHash,
                        csvSafe($course),
                        csvSafe($year),
                        csvSafe($gender),
                        date("Y-m-d H:i:s")
                    ], ",", '"', "") !== false;
                }

                if ($saved) {
                    fflush($handle);
                }

                flock($handle, LOCK_UN);
                fclose($handle);

                if ($saved) {
                    // Redirect to the existing dashboard.
                    header("Location: ../html/dashboard.html?registered=1");
                    exit;
                }

                $errors[] = "Could not save the registration.";
            }
        }
    }
}

if (!empty($errors)) {
    http_response_code(
        $_SERVER["REQUEST_METHOD"] === "POST" ? 422 : 405
    );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registration Error</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="registration-container">
        <h2>Registration Failed</h2>

        <?php foreach ($errors as $error): ?>
            <p><?= safeOutput($error) ?></p>
        <?php endforeach; ?>

        <a href="../html/register.html">Back to Registration</a>
    </div>
</body>
</html>
