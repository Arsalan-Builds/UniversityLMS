<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

include "../includes/db.php";

// Only students can access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != "student") {
    header("Location: ../login.php");
    exit;
}

$student_id = $_SESSION['user_id'];


// ==========================================
// GET ASSIGNMENTS + SUBMISSION + GRADE STATUS
// ==========================================

$sql = "
    SELECT
        assignments.id,
        assignments.title,
        assignments.description,
        assignments.due_date,

        courses.course_name,
        courses.course_code,

        submissions.id AS submission_id,
        submissions.file_path,
        submissions.submitted_at,

        grades.marks,
        grades.feedback

    FROM assignments

    INNER JOIN courses
        ON assignments.course_id = courses.id

    INNER JOIN enrollments
        ON courses.id = enrollments.course_id

    LEFT JOIN submissions
        ON assignments.id = submissions.assignment_id
        AND submissions.student_id = ?

    LEFT JOIN grades
        ON submissions.id = grades.submission_id

    WHERE enrollments.student_id = ?

    ORDER BY assignments.due_date ASC
";


$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $student_id,
    $student_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>My Assignments - University LMS</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>


<body>


<!-- ==========================================
     NAVBAR
========================================== -->

<nav class="navbar navbar-dark bg-dark">

    <div class="container">

        <a
            class="navbar-brand"
            href="dashboard.php"
        >
            University LMS
        </a>

        <a
            href="../logout.php"
            class="btn btn-danger"
        >
            Logout
        </a>

    </div>

</nav>



<!-- ==========================================
     MAIN CONTENT
========================================== -->

<div class="container mt-5">


    <div class="d-flex justify-content-between align-items-center mb-4">

        <h2>
            My Assignments
        </h2>

        <a
            href="dashboard.php"
            class="btn btn-secondary"
        >
            Back to Dashboard
        </a>

    </div>



    <!-- ==========================================
         ASSIGNMENTS
    ========================================== -->

    <?php if (mysqli_num_rows($result) > 0): ?>


        <div class="row">


            <?php while ($assignment = mysqli_fetch_assoc($result)): ?>


                <div class="col-md-6 mb-4">


                    <div class="card shadow-sm h-100">


                        <div class="card-body">


                            <!-- ASSIGNMENT TITLE -->

                            <h4 class="card-title">

                                <?php
                                echo htmlspecialchars(
                                    $assignment['title']
                                );
                                ?>

                            </h4>



                            <!-- COURSE -->

                            <h6 class="text-primary">

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

                            </h6>


                            <hr>



                            <!-- DESCRIPTION -->

                            <p>

                                <?php

                                if (!empty($assignment['description'])) {

                                    echo nl2br(
                                        htmlspecialchars(
                                            $assignment['description']
                                        )
                                    );

                                } else {

                                    echo '<span class="text-muted">
                                            No description available.
                                          </span>';

                                }

                                ?>

                            </p>



                            <!-- DUE DATE -->

                            <p class="text-danger">

                                <strong>
                                    Due Date:
                                </strong>

                                <?php

                                if (!empty($assignment['due_date'])) {

                                    echo date(
                                        "d M Y, h:i A",
                                        strtotime(
                                            $assignment['due_date']
                                        )
                                    );

                                } else {

                                    echo "No due date";

                                }

                                ?>

                            </p>



                            <!-- ==========================================
                                 SUBMISSION STATUS
                            ========================================== -->

                            <div class="mb-3">

                                <strong>
                                    Submission Status:
                                </strong>


                                <?php if (!empty($assignment['submission_id'])): ?>

                                    <span class="badge bg-success">
                                        Submitted
                                    </span>

                                <?php else: ?>

                                    <span class="badge bg-warning text-dark">
                                        Not Submitted
                                    </span>

                                <?php endif; ?>

                            </div>



                            <!-- ==========================================
                                 SUBMISSION DATE
                            ========================================== -->

                            <?php if (!empty($assignment['submitted_at'])): ?>

                                <p>

                                    <strong>
                                        Submitted On:
                                    </strong>

                                    <?php

                                    echo date(
                                        "d M Y, h:i A",
                                        strtotime(
                                            $assignment['submitted_at']
                                        )
                                    );

                                    ?>

                                </p>

                            <?php endif; ?>



                            <!-- ==========================================
                                 GRADE
                            ========================================== -->

                            <?php if ($assignment['marks'] !== null): ?>

                                <div class="alert alert-success">

                                    <strong>
                                        Marks:
                                    </strong>

                                    <?php
                                    echo htmlspecialchars(
                                        $assignment['marks']
                                    );
                                    ?>

                                    / 100

                                </div>


                                <!-- FEEDBACK -->

                                <?php if (!empty($assignment['feedback'])): ?>

                                    <div class="alert alert-info">

                                        <strong>
                                            Teacher Feedback:
                                        </strong>

                                        <br>

                                        <?php

                                        echo nl2br(
                                            htmlspecialchars(
                                                $assignment['feedback']
                                            )
                                        );

                                        ?>

                                    </div>

                                <?php endif; ?>


                            <?php elseif (!empty($assignment['submission_id'])): ?>


                                <div class="alert alert-secondary">

                                    <strong>
                                        Grade:
                                    </strong>

                                    Not Graded Yet

                                </div>


                            <?php endif; ?>



                            <!-- ==========================================
                                 SUBMIT / RESUBMIT BUTTON
                            ========================================== -->

                            <?php if (!empty($assignment['submission_id'])): ?>

                                <a
                                    href="submit_assignment.php?id=<?php echo $assignment['id']; ?>"
                                    class="btn btn-outline-primary"
                                >
                                    Resubmit Assignment
                                </a>

                            <?php else: ?>

                                <a
                                    href="submit_assignment.php?id=<?php echo $assignment['id']; ?>"
                                    class="btn btn-primary"
                                >
                                    Submit Assignment
                                </a>

                            <?php endif; ?>


                        </div>

                    </div>

                </div>


            <?php endwhile; ?>


        </div>


    <?php else: ?>


        <!-- NO ASSIGNMENTS -->

        <div class="alert alert-info">

            <h5>
                No Assignments Yet
            </h5>

            <p class="mb-0">

                You currently have no assignments
                for your enrolled courses.

            </p>

        </div>


    <?php endif; ?>


</div>


</body>

</html>

