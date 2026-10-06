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
   COURSE ID CHECK
========================= */

if (!isset($_GET['course_id']) || !is_numeric($_GET['course_id'])) {
    die("Invalid course ID.");
}

$course_id = intval($_GET['course_id']);

/* =========================
   CHECK COURSE BELONGS TO TEACHER
========================= */

$sql = "
    SELECT
        courses.id,
        courses.course_code,
        courses.course_name
    FROM courses
    WHERE courses.id = ?
    AND courses.teacher_id = ?
";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Database error: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "ii", $course_id, $teacher_id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$course = mysqli_fetch_assoc($result);

if (!$course) {
    die("Course not found or you do not have permission to access it.");
}

/* =========================
   GET ENROLLED STUDENTS
========================= */

$sql = "
    SELECT
        users.id,
        users.full_name,
        users.student_id,
        users.email,
        users.phone,
        users.semester,
        departments.name AS department_name,
        enrollments.enrolled_at
    FROM enrollments

    INNER JOIN users
        ON enrollments.student_id = users.id

    LEFT JOIN departments
        ON users.department_id = departments.id

    WHERE enrollments.course_id = ?
    AND users.role = 'student'

    ORDER BY users.full_name ASC
";

$stmt = mysqli_prepare($conn, $sql);

if (!$stmt) {
    die("Database error: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $course_id);
mysqli_stmt_execute($stmt);

$students = mysqli_stmt_get_result($stmt);

$total_students = mysqli_num_rows($students);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Enrolled Students</title>

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

        .student-count {
            background: #087f7b;
            color: white;
            padding: 8px 15px;
            border-radius: 7px;
            font-size: 14px;
            font-weight: 600;
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

        .student-id {
            font-weight: 600;
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

    </style>

</head>

<body>

<div class="container-main">

    <!-- PAGE HEADER -->

    <div class="page-header">

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

            <div>

                <h2>
                    <i class="bi bi-people-fill"></i>
                    Enrolled Students
                </h2>

                <p>
                    <?php echo htmlspecialchars($course['course_code']); ?>
                    -
                    <?php echo htmlspecialchars($course['course_name']); ?>
                </p>

            </div>

            <div>

                <span class="student-count">

                    <i class="bi bi-person-fill"></i>

                    <?php echo $total_students; ?>

                    Student<?php echo ($total_students != 1) ? 's' : ''; ?>

                </span>

            </div>

        </div>

    </div>


    <!-- STUDENTS TABLE -->

    <div class="table-card">

        <?php if ($total_students > 0): ?>

            <div class="table-responsive">

                <table class="table table-bordered">

                    <thead>

                        <tr>

                            <th>#</th>

                            <th>Student Name</th>

                            <th>Student ID</th>

                            <th>Email</th>

                            <th>Phone</th>

                            <th>Semester</th>

                            <th>Department</th>

                            <th>Enrolled Date</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php

                    $count = 1;

                    while ($student = mysqli_fetch_assoc($students)):

                    ?>

                        <tr>

                            <td>
                                <?php echo $count++; ?>
                            </td>

                            <td class="student-name">

                                <?php
                                echo htmlspecialchars($student['full_name']);
                                ?>

                            </td>

                            <td class="student-id">

                                <?php
                                echo htmlspecialchars($student['student_id']);
                                ?>

                            </td>

                            <td>

                                <?php
                                echo htmlspecialchars($student['email']);
                                ?>

                            </td>

                            <td>

                                <?php
                                echo htmlspecialchars($student['phone'] ?? '-');
                                ?>

                            </td>

                            <td>

                                <?php
                                echo htmlspecialchars($student['semester'] ?? '-');
                                ?>

                            </td>

                            <td>

                                <?php
                                echo htmlspecialchars($student['department_name'] ?? '-');
                                ?>

                            </td>

                            <td>

                                <?php
                                echo date(
                                    "d M Y",
                                    strtotime($student['enrolled_at'])
                                );
                                ?>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                    </tbody>

                </table>

            </div>

        <?php else: ?>

            <div class="empty-box">

                <i class="bi bi-people"></i>

                <h5>No Students Enrolled</h5>

                <p>
                    There are currently no students enrolled in this course.
                </p>

            </div>

        <?php endif; ?>


        <!-- BACK TO DASHBOARD -->

        <a href="dashboard.php" class="back-btn">

            <i class="bi bi-arrow-left"></i>

            Back to Dashboard

        </a>

    </div>

</div>

</body>

</html>