
<?php

session_start();

include "../includes/db.php";

/* =========================
   STUDENT LOGIN CHECK
========================= */

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit;
}

$student_id = $_SESSION['user_id'];


/* =========================
   GET STUDENT GRADES
========================= */

$stmt = mysqli_prepare($conn, "

    SELECT
        assignments.title AS assignment_title,
        courses.course_name,
        courses.course_code,
        submissions.submitted_at,
        grades.marks,
        grades.feedback,
        grades.graded_at

    FROM submissions

    INNER JOIN assignments
        ON submissions.assignment_id = assignments.id

    INNER JOIN courses
        ON assignments.course_id = courses.id

    LEFT JOIN grades
        ON submissions.id = grades.submission_id

    WHERE submissions.student_id = ?

    ORDER BY submissions.submitted_at DESC

");

mysqli_stmt_bind_param($stmt, "i", $student_id);

mysqli_stmt_execute($stmt);

$grades_result = mysqli_stmt_get_result($stmt);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>My Grades - University LMS</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>


<body class="bg-light">


<!-- =========================
     NAVBAR
========================= -->

<nav class="navbar navbar-dark bg-primary">

    <div class="container">

        <a
            class="navbar-brand"
            href="dashboard.php"
        >
            University LMS
        </a>

        <div>

        <a
    href="dashboard.php"
    class="btn btn-light btn-sm"
>
    <i class="bi bi-arrow-left"></i>
    Back to Dashboard
</a>
            <a
                href="../logout.php"
                class="btn btn-danger btn-sm"
            >
                Logout
            </a>

        </div>

    </div>

</nav>


<!-- =========================
     MAIN CONTENT
========================= -->

<div class="container py-5">


    <div class="card shadow-sm">

        <div class="card-header bg-dark text-white">

            <h3 class="mb-0">
                My Grades
            </h3>

        </div>


        <div class="card-body">


            <?php if (mysqli_num_rows($grades_result) > 0): ?>


                <div class="table-responsive">

                    <table class="table table-bordered table-hover align-middle">


                        <thead class="table-light">

                            <tr>

                                <th>Assignment</th>

                                <th>Course</th>

                                <th>Submitted</th>

                                <th>Marks</th>

                                <th>Feedback</th>

                                <th>Status</th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php while ($grade = mysqli_fetch_assoc($grades_result)): ?>


                            <tr>


                                <!-- ASSIGNMENT -->

                                <td>

                                    <strong>

                                        <?php
                                        echo htmlspecialchars(
                                            $grade['assignment_title']
                                        );
                                        ?>

                                    </strong>

                                </td>


                                <!-- COURSE -->

                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $grade['course_code']
                                    );
                                    ?>

                                    <br>

                                    <small class="text-muted">

                                        <?php
                                        echo htmlspecialchars(
                                            $grade['course_name']
                                        );
                                        ?>

                                    </small>

                                </td>


                                <!-- SUBMITTED -->

                                <td>

                                    <?php

                                    echo date(
                                        "d M Y, h:i A",
                                        strtotime(
                                            $grade['submitted_at']
                                        )
                                    );

                                    ?>

                                </td>


                                <!-- MARKS -->

                                <td>

                                    <?php if ($grade['marks'] !== null): ?>

                                        <span
                                            class="badge bg-success fs-6"
                                        >

                                            <?php
                                            echo htmlspecialchars(
                                                $grade['marks']
                                            );
                                            ?>

                                            / 100

                                        </span>

                                    <?php else: ?>

                                        <span
                                            class="badge bg-warning text-dark"
                                        >

                                            Not Graded

                                        </span>

                                    <?php endif; ?>

                                </td>


                                <!-- FEEDBACK -->

                                <td>

                                    <?php

                                    if (
                                        !empty(
                                            $grade['feedback']
                                        )
                                    ) {

                                        echo nl2br(
                                            htmlspecialchars(
                                                $grade['feedback']
                                            )
                                        );

                                    } else {

                                        echo "<span class='text-muted'>No feedback</span>";

                                    }

                                    ?>

                                </td>


                                <!-- STATUS -->

                                <td>

                                    <?php if ($grade['marks'] !== null): ?>

                                        <span
                                            class="badge bg-success"
                                        >

                                            Graded

                                        </span>

                                    <?php else: ?>

                                        <span
                                            class="badge bg-secondary"
                                        >

                                            Pending

                                        </span>

                                    <?php endif; ?>

                                </td>


                            </tr>


                        <?php endwhile; ?>


                        </tbody>

                    </table>

                </div>


            <?php else: ?>


                <div class="alert alert-info">

                    You have not submitted any assignments yet.

                </div>


            <?php endif; ?>


        </div>

    </div>


</div>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


</body>

</html>

