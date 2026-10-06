<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

include "../includes/db.php";

// Only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "admin") {
    header("Location: ../login.php");
    exit;
}

$message = "";
$error = "";

$student_id = isset($_GET['student_id']) ? intval($_GET['student_id']) : 0;

if ($student_id <= 0) {
    header("Location: students.php");
    exit;
}


/* =====================================================
   GET STUDENT
===================================================== */

$stmt = mysqli_prepare(
    $conn,
    "SELECT id, full_name, student_id, email
     FROM users
     WHERE id = ? AND role = 'student'
     LIMIT 1"
);

mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);

$result_student = mysqli_stmt_get_result($stmt);

$student = mysqli_fetch_assoc($result_student);

if (!$student) {
    header("Location: students.php");
    exit;
}


/* =====================================================
   ENROLL STUDENT
===================================================== */

if (isset($_POST['enroll_student'])) {

    $course_id = intval($_POST['course_id']);

    if ($course_id <= 0) {

        $error = "Please select a course.";

    } else {

        // Check if already enrolled
        $check = mysqli_prepare(
            $conn,
            "SELECT id
             FROM enrollments
             WHERE student_id = ?
             AND course_id = ?
             LIMIT 1"
        );

        mysqli_stmt_bind_param(
            $check,
            "ii",
            $student_id,
            $course_id
        );

        mysqli_stmt_execute($check);

        $check_result = mysqli_stmt_get_result($check);

        if (mysqli_num_rows($check_result) > 0) {

            $error = "This student is already enrolled in this course.";

        } else {

            // Enroll student
            $insert = mysqli_prepare(
                $conn,
                "INSERT INTO enrollments
                (student_id, course_id)
                VALUES (?, ?)"
            );

            mysqli_stmt_bind_param(
                $insert,
                "ii",
                $student_id,
                $course_id
            );

            if (mysqli_stmt_execute($insert)) {

                $message = "Student enrolled successfully.";

            } else {

                $error = "Failed to enroll student: " . mysqli_error($conn);
            }
        }
    }
}


/* =====================================================
   GET COURSES
===================================================== */

$courses = mysqli_query(
    $conn,
    "SELECT
        c.id,
        c.course_code,
        c.course_name,
        d.name AS department_name,
        u.full_name AS teacher_name
     FROM courses c
     LEFT JOIN departments d
        ON c.department_id = d.id
     LEFT JOIN users u
        ON c.teacher_id = u.id
     ORDER BY c.course_name ASC"
);


/* =====================================================
   GET CURRENT ENROLLMENTS
===================================================== */

$enrolled_courses = mysqli_query(
    $conn,
    "SELECT
        e.id,
        c.course_code,
        c.course_name,
        d.name AS department_name,
        u.full_name AS teacher_name
     FROM enrollments e
     INNER JOIN courses c
        ON e.course_id = c.id
     LEFT JOIN departments d
        ON c.department_id = d.id
     LEFT JOIN users u
        ON c.teacher_id = u.id
     WHERE e.student_id = $student_id
     ORDER BY c.course_name ASC"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Enroll Student</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body>

<div class="container py-4">

    <!-- HEADER -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2>Enroll Student</h2>

            <p class="text-muted mb-0">
                Add this student to a course.
            </p>

        </div>

        <a
            href="students.php"
            class="btn btn-secondary"
        >
            Back to Students
        </a>

    </div>


    <!-- STUDENT INFORMATION -->

    <div class="card shadow-sm mb-4">

        <div class="card-header">

            <h4 class="mb-0">
                Student Information
            </h4>

        </div>

        <div class="card-body">

            <div class="row">

                <div class="col-md-3">

                    <strong>Student ID</strong>

     <p>
    <?php
    echo htmlspecialchars($student['student_id'] ?? 'Not Assigned');
    ?>
</p>
                </div>

                <div class="col-md-3">

                    <strong>Name</strong>

                    <p>
                        <?php
                        echo htmlspecialchars(
                            $student['full_name']
                        );
                        ?>
                    </p>

                </div>

                <div class="col-md-3">

                    <strong>Email</strong>

                    <p>
                        <?php
                        echo htmlspecialchars(
                            $student['email']
                        );
                        ?>
                    </p>

                </div>

            </div>

        </div>

    </div>


    <!-- MESSAGES -->

    <?php if (!empty($message)): ?>

        <div class="alert alert-success">

            <?php echo htmlspecialchars($message); ?>

        </div>

    <?php endif; ?>


    <?php if (!empty($error)): ?>

        <div class="alert alert-danger">

            <?php echo htmlspecialchars($error); ?>

        </div>

    <?php endif; ?>


    <!-- ENROLL FORM -->

    <div class="card shadow-sm mb-4">

        <div class="card-header">

            <h4 class="mb-0">
                Enroll in Course
            </h4>

        </div>

        <div class="card-body">

            <form method="POST">

                <div class="mb-3">

                    <label class="form-label">
                        Select Course
                    </label>

                    <select
                        name="course_id"
                        class="form-select"
                        required
                    >

                        <option value="">
                            Select a course
                        </option>

                        <?php while ($course = mysqli_fetch_assoc($courses)): ?>

                            <option
                                value="<?php echo $course['id']; ?>"
                            >

                                <?php
                                echo htmlspecialchars(
                                    $course['course_code']
                                );
                                ?>

                                -
                                
                                <?php
                                echo htmlspecialchars(
                                    $course['course_name']
                                );
                                ?>

                                <?php if (!empty($course['teacher_name'])): ?>

                                    -
                                    Teacher:
                                    <?php
                                    echo htmlspecialchars(
                                        $course['teacher_name']
                                    );
                                    ?>

                                <?php endif; ?>

                            </option>

                        <?php endwhile; ?>

                    </select>

                </div>


                <button
                    type="submit"
                    name="enroll_student"
                    class="btn btn-primary"
                >
                    Enroll Student
                </button>

            </form>

        </div>

    </div>


    <!-- CURRENT ENROLLMENTS -->

    <div class="card shadow-sm">

        <div class="card-header">

            <h4 class="mb-0">
                Current Enrollments
            </h4>

        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered table-hover">

                    <thead class="table-dark">

                        <tr>

                            <th>#</th>

                            <th>Course Code</th>

                            <th>Course Name</th>

                            <th>Department</th>

                            <th>Teacher</th>

                        </tr>

                    </thead>

                    <tbody>

                    <?php if (mysqli_num_rows($enrolled_courses) > 0): ?>

                        <?php $count = 1; ?>

                        <?php while ($course = mysqli_fetch_assoc($enrolled_courses)): ?>

                            <tr>

                                <td>
                                    <?php echo $count++; ?>
                                </td>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $course['course_code']
                                    );
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $course['course_name']
                                    );
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    echo !empty($course['department_name'])
                                        ? htmlspecialchars(
                                            $course['department_name']
                                        )
                                        : "Not set";
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    echo !empty($course['teacher_name'])
                                        ? htmlspecialchars(
                                            $course['teacher_name']
                                        )
                                        : "Not assigned";
                                    ?>
                                </td>

                            </tr>

                        <?php endwhile; ?>

                    <?php else: ?>

                        <tr>

                            <td
                                colspan="5"
                                class="text-center"
                            >
                                This student is not enrolled in any course yet.
                            </td>

                        </tr>

                    <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

</body>

</html>