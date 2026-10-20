<?php

$allowedCourses = ["CE", "IT", "CSE", "AI"];
$course = $_GET["course"] ?? "CE";
$invalidCourse = !is_string($course)
    || !in_array($course, $allowedCourses, true);
$students = [];
$queryError = false;

if ($invalidCourse) {
    http_response_code(400);
} else {
    try {
        require_once __DIR__ . "/db.php";

        $stmt = $pdo->prepare(
            "SELECT student_id, name, email, course
             FROM students
             WHERE course = :course
             ORDER BY student_id"
        );
        $stmt->bindValue(":course", $course, PDO::PARAM_STR);
        $stmt->execute();
        $students = $stmt->fetchAll();
    } catch (PDOException | RuntimeException $exception) {
        if ($exception instanceof PDOException) {
            error_log("StudentHub student query failed: " . $exception->getMessage());
        }
        $queryError = true;
    }
}

function escapeStudentValue($value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Students by Course</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <main class="registration-container">
        <h1>Students by Course</h1>
        <form method="get">
            <label for="course">Course:</label>
            <select id="course" name="course">
                <?php foreach ($allowedCourses as $allowedCourse): ?>
                    <option value="<?= escapeStudentValue($allowedCourse) ?>"
                        <?= !$invalidCourse && $course === $allowedCourse ? "selected" : "" ?>>
                        <?= escapeStudentValue($allowedCourse) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit">Search</button>
        </form>

        <?php if ($invalidCourse): ?>
            <p>Please select one of the allowed course codes.</p>
        <?php elseif ($queryError): ?>
            <p>Student records could not be loaded. Check the database configuration.</p>
        <?php elseif (count($students) === 0): ?>
            <p>No students were found for this course.</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Student ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Course</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?= escapeStudentValue($student["student_id"]) ?></td>
                            <td><?= escapeStudentValue($student["name"]) ?></td>
                            <td><?= escapeStudentValue($student["email"]) ?></td>
                            <td><?= escapeStudentValue($student["course"]) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </main>
</body>
</html>
