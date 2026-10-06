<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

include "../includes/db.php";

/* =========================
   TEACHER LOGIN CHECK
========================= */

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "teacher") {
    header("Location: ../login.php");
    exit;
}

$teacher_id = $_SESSION['user_id'];

/* =========================
   ASSIGNMENT ID CHECK
========================= */

if (!isset($_GET['assignment_id']) || !is_numeric($_GET['assignment_id'])) {
    die("Assignment ID is missing.");
}

$assignment_id = intval($_GET['assignment_id']);

/* =========================
   GET ASSIGNMENT
========================= */

$sql = "
    SELECT
        assignments.id,
        assignments.title,
        assignments.description,
        assignments.due_date,

        courses.id AS course_id,
        courses.course_name,
        courses.course_code

    FROM assignments

    INNER JOIN courses
        ON assignments.course_id = courses.id

    WHERE assignments.id = ?
    AND courses.teacher_id = ?
";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Database error: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "ii", $assignment_id, $teacher_id);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {

    die(
        "Assignment not found or this assignment does not belong to your course."
    );
}

$assignment = mysqli_fetch_assoc($result);

/* =========================
   SAVE GRADE
========================= */

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $submission_id = intval($_POST['submission_id'] ?? 0);
    $marks = trim($_POST['marks'] ?? "");
    $feedback = trim($_POST['feedback'] ?? "");

    if ($submission_id <= 0) {

        $error = "Invalid submission.";

    } elseif ($marks === "" || !is_numeric($marks)) {

        $error = "Please enter valid marks.";

    } elseif ($marks < 0 || $marks > 100) {

        $error = "Marks must be between 0 and 100.";

    } else {

        /* Check submission belongs to this assignment */

        $check_sql = "
            SELECT submissions.id

            FROM submissions

            INNER JOIN assignments
                ON submissions.assignment_id = assignments.id

            INNER JOIN courses
                ON assignments.course_id = courses.id

            WHERE submissions.id = ?
            AND submissions.assignment_id = ?
            AND courses.teacher_id = ?
        ";

        $check_stmt = mysqli_prepare($conn, $check_sql);

        mysqli_stmt_bind_param(
            $check_stmt,
            "iii",
            $submission_id,
            $assignment_id,
            $teacher_id
        );

        mysqli_stmt_execute($check_stmt);

        $check_result = mysqli_stmt_get_result($check_stmt);

        if (mysqli_num_rows($check_result) == 0) {

            $error = "Invalid submission.";

        } else {

            /* Check if grade already exists */

            $grade_check_sql = "
                SELECT id
                FROM grades
                WHERE submission_id = ?
            ";

            $grade_check_stmt = mysqli_prepare(
                $conn,
                $grade_check_sql
            );

            mysqli_stmt_bind_param(
                $grade_check_stmt,
                "i",
                $submission_id
            );

            mysqli_stmt_execute($grade_check_stmt);

            $grade_check_result = mysqli_stmt_get_result(
                $grade_check_stmt
            );

            if (mysqli_num_rows($grade_check_result) > 0) {

                $grade = mysqli_fetch_assoc(
                    $grade_check_result
                );

                $update_sql = "
                    UPDATE grades
                    SET marks = ?, feedback = ?, graded_at = NOW()
                    WHERE id = ?
                ";

                $update_stmt = mysqli_prepare(
                    $conn,
                    $update_sql
                );

                mysqli_stmt_bind_param(
                    $update_stmt,
                    "dsi",
                    $marks,
                    $feedback,
                    $grade['id']
                );

                if (mysqli_stmt_execute($update_stmt)) {

                    $message = "Grade updated successfully.";

                } else {

                    $error = "Failed to update grade.";
                }

            } else {

                $insert_sql = "
                    INSERT INTO grades
                    (submission_id, marks, feedback)
                    VALUES (?, ?, ?)
                ";

                $insert_stmt = mysqli_prepare(
                    $conn,
                    $insert_sql
                );

                mysqli_stmt_bind_param(
                    $insert_stmt,
                    "ids",
                    $submission_id,
                    $marks,
                    $feedback
                );

                if (mysqli_stmt_execute($insert_stmt)) {

                    $message = "Grade added successfully.";

                } else {

                    $error = "Failed to save grade.";
                }
            }
        }
    }
}

/* =========================
   GET SUBMISSIONS
========================= */

$sql = "
    SELECT
        submissions.id AS submission_id,
        submissions.file_path,
        submissions.submitted_at,

        users.id AS student_id,
        users.full_name,
        users.student_id AS student_code,
        users.email,

        grades.id AS grade_id,
        grades.marks,
        grades.feedback,
        grades.graded_at

    FROM submissions

    INNER JOIN users
        ON submissions.student_id = users.id

    LEFT JOIN grades
        ON submissions.id = grades.submission_id

    WHERE submissions.assignment_id = ?

    ORDER BY submissions.submitted_at DESC
";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Database error: " . mysqli_error($conn));
}

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $assignment_id
);

mysqli_stmt_execute($stmt);

$submissions = mysqli_stmt_get_result($stmt);

$total_submissions = mysqli_num_rows($submissions);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Student Submissions</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css"
        rel="stylesheet"
    >

    <style>

        body {
            background: #f4f8fa;
            font-family: "Segoe UI", Inter, Arial, sans-serif;
            color: #263238;
        }

        .container-main {
            max-width: 1150px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .page-header {
            background: #ffffff;
            border-radius: 12px;
            padding: 25px 30px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
            border-left: 5px solid #087f7b;
        }

        .page-header h2 {
            margin: 0;
            font-size: 26px;
            font-weight: 700;
            color: #087f7b;
        }

        .page-header p {
            margin: 7px 0 0;
            color: #666;
            font-size: 15px;
        }

        .submission-count {
            background: #087f7b;
            color: white;
            padding: 8px 15px;
            border-radius: 7px;
            font-size: 14px;
            font-weight: 600;
        }

        .assignment-info {
            background: white;
            border-radius: 12px;
            padding: 22px 25px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
        }

        .assignment-info h5 {
            color: #087f7b;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .assignment-description {
            color: #666;
            margin-bottom: 10px;
        }

        .due-date {
            font-size: 14px;
            font-weight: 600;
            color: #e85f00;
        }

        .table-card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.06);
        }

        .table {
            margin-bottom: 0;
            vertical-align: middle;
        }

        .table thead th {
            background: #087f7b;
            color: white;
            border: none;
            padding: 14px 12px;
            font-size: 14px;
            font-weight: 600;
        }

        .table tbody td {
            padding: 14px 12px;
            font-size: 14px;
        }

        .table tbody tr:hover {
            background: #f7fafb;
        }

        .student-name {
            font-weight: 600;
            color: #087f7b;
        }

        .file-btn {
            background: #087f7b;
            color: white;
            border: none;
            padding: 7px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
        }

        .file-btn:hover {
            background: #066966;
            color: white;
        }

        .grade-btn {
            background: #ff7518;
            color: white;
            border: none;
            padding: 7px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
        }

        .grade-btn:hover {
            background: #e85f00;
        }

        .grade-form {
            min-width: 220px;
        }

        .grade-form input,
        .grade-form textarea {
            font-size: 13px;
            margin-bottom: 6px;
        }

        .save-btn {
            background: #087f7b;
            color: white;
            border: none;
            padding: 7px 12px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
        }

        .save-btn:hover {
            background: #066966;
        }

        .back-btn {
            background: #ff7518;
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 7px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }

        .back-btn:hover {
            background: #e85f00;
            color: white;
        }

        .empty-box {
            text-align: center;
            padding: 45px 20px;
            color: #777;
        }

        .empty-box i {
            font-size: 45px;
            color: #087f7b;
            margin-bottom: 12px;
        }

        .alert {
            border-radius: 8px;
        }

    </style>

</head>

<body>

<div class="container-main">

    <!-- PAGE HEADER -->

    <div class="page-header">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

            <div>

                <h2>

                    <i class="bi bi-file-earmark-check-fill"></i>

                    Student Submissions

                </h2>

                <p>

                    <?php
                    echo htmlspecialchars(
                        $assignment['course_code']
                    );
                    ?>

                    -

                    <?php
                    echo htmlspecialchars(
                        $assignment['course_name']
                    );
                    ?>

                </p>

            </div>

            <div>

                <span class="submission-count">

                    <i class="bi bi-file-earmark"></i>

                    <?php echo $total_submissions; ?>

                    Submission<?php echo ($total_submissions != 1) ? 's' : ''; ?>

                </span>

            </div>

        </div>

    </div>


    <!-- ASSIGNMENT INFORMATION -->

    <div class="assignment-info">

        <h5>

            <?php
            echo htmlspecialchars(
                $assignment['title']
            );
            ?>

        </h5>

        <div class="assignment-description">

            <?php
            echo nl2br(
                htmlspecialchars(
                    $assignment['description'] ?? ''
                )
            );
            ?>

        </div>

        <?php if (!empty($assignment['due_date'])): ?>

            <div class="due-date">

                <i class="bi bi-calendar-event"></i>

                Due:

                <?php
                echo date(
                    "d M Y, h:i A",
                    strtotime($assignment['due_date'])
                );
                ?>

            </div>

        <?php endif; ?>

    </div>


    <?php if (!empty($message)): ?>

        <div class="alert alert-success">

            <i class="bi bi-check-circle-fill"></i>

            <?php echo htmlspecialchars($message); ?>

        </div>

    <?php endif; ?>


    <?php if (!empty($error)): ?>

        <div class="alert alert-danger">

            <i class="bi bi-exclamation-triangle-fill"></i>

            <?php echo htmlspecialchars($error); ?>

        </div>

    <?php endif; ?>


    <!-- SUBMISSIONS -->

    <div class="table-card">

        <?php if ($total_submissions > 0): ?>

            <div class="table-responsive">

                <table class="table table-bordered">

                    <thead>

                        <tr>

                            <th>#</th>

                            <th>Student</th>

                            <th>Student ID</th>

                            <th>Email</th>

                            <th>Submitted</th>

                            <th>File</th>

                            <th>Grade</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php

                    $count = 1;

                    while ($submission = mysqli_fetch_assoc($submissions)):

                    ?>

                        <tr>

                            <td>

                                <?php echo $count++; ?>

                            </td>

                            <td class="student-name">

                                <?php
                                echo htmlspecialchars(
                                    $submission['full_name']
                                );
                                ?>

                            </td>

                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $submission['student_code']
                                );
                                ?>

                            </td>

                            <td>

                                <?php
                                echo htmlspecialchars(
                                    $submission['email']
                                );
                                ?>

                            </td>

                            <td>

                                <?php
                                echo date(
                                    "d M Y, h:i A",
                                    strtotime(
                                        $submission['submitted_at']
                                    )
                                );
                                ?>

                            </td>

                            <td>

                                <?php if (!empty($submission['file_path'])): ?>

                                    <a
                                        href="../<?php echo htmlspecialchars($submission['file_path']); ?>"
                                        target="_blank"
                                        class="file-btn"
                                    >

                                        <i class="bi bi-download"></i>

                                        View File

                                    </a>

                                <?php else: ?>

                                    <span class="text-muted">
                                        No file
                                    </span>

                                <?php endif; ?>

                            </td>

                            <td>

                                <form
                                    method="POST"
                                    class="grade-form"
                                >

                                    <input
                                        type="hidden"
                                        name="submission_id"
                                        value="<?php echo $submission['submission_id']; ?>"
                                    >

                                    <input
                                        type="number"
                                        name="marks"
                                        class="form-control"
                                        placeholder="Marks / 100"
                                        min="0"
                                        max="100"
                                        step="0.01"
                                        value="<?php echo htmlspecialchars($submission['marks'] ?? ''); ?>"
                                        required
                                    >

                                    <textarea
                                        name="feedback"
                                        class="form-control"
                                        rows="2"
                                        placeholder="Feedback"
                                    ><?php echo htmlspecialchars($submission['feedback'] ?? ''); ?></textarea>

                                    <button
                                        type="submit"
                                        class="save-btn"
                                    >

                                        <i class="bi bi-check-lg"></i>

                                        <?php
                                        echo !empty($submission['grade_id'])
                                            ? 'Update Grade'
                                            : 'Save Grade';
                                        ?>

                                    </button>

                                </form>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                    </tbody>

                </table>

            </div>

        <?php else: ?>

            <div class="empty-box">

                <i class="bi bi-file-earmark-x"></i>

                <h5>No Submissions Yet</h5>

                <p>
                    No student has submitted this assignment yet.
                </p>

            </div>

        <?php endif; ?>


        <!-- BACK TO DASHBOARD -->

        <a
            href="dashboard.php"
            class="back-btn"
        >

            <i class="bi bi-arrow-left"></i>

            Back to Dashboard

        </a>

    </div>

</div>

</body>

</html>