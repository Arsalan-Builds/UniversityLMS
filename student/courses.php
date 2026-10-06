<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

include "../includes/db.php";

/* ===============================
   STUDENT LOGIN CHECK
=============================== */

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== "student") {
    header("Location: ../login.php");
    exit;
}

$student_id = $_SESSION['user_id'];


/* ===============================
   GET ONLY ENROLLED COURSES
=============================== */

$sql = "
    SELECT
        courses.id,
        courses.course_code,
        courses.course_name,
        courses.description,
        courses.credit_hours,
        departments.name AS department_name,
        users.full_name AS teacher_name,
        enrollments.enrolled_at

    FROM enrollments

    INNER JOIN courses
        ON enrollments.course_id = courses.id

    LEFT JOIN departments
        ON courses.department_id = departments.id

    LEFT JOIN users
        ON courses.teacher_id = users.id

    WHERE enrollments.student_id = ?

    ORDER BY courses.course_name ASC
";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
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

    <title>My Courses - University LMS</title>


    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- Bootstrap Icons -->

    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
    >

</head>


<body class="bg-light">


<!-- ===============================
     NAVBAR
=============================== -->

<nav class="navbar navbar-dark bg-dark">

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
                class="btn btn-secondary me-2"
            >
                Dashboard
            </a>


            <a
                href="../logout.php"
                class="btn btn-danger"
            >
                Logout
            </a>

        </div>

    </div>

</nav>



<!-- ===============================
     MAIN CONTENT
=============================== -->

<div class="container py-4">


    <!-- ===============================
         PAGE HEADER
    =============================== -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2 class="mb-1">
                My Courses
            </h2>

            <p class="text-muted mb-0">
                Courses assigned to you by the administrator
            </p>

        </div>


        <!-- BACK BUTTON -->

        <a
            href="dashboard.php"
            class="btn btn-secondary"
        >

            <i class="bi bi-arrow-left"></i>

            Back to Dashboard

        </a>

    </div>



    <!-- ===============================
         COURSES
    =============================== -->

    <div class="row">


        <?php if (mysqli_num_rows($result) > 0): ?>


            <?php while ($course = mysqli_fetch_assoc($result)): ?>


                <div class="col-md-6 col-lg-4 mb-4">


                    <div class="card shadow-sm h-100">


                        <div class="card-body">


                            <!-- COURSE NAME -->

                            <h5 class="card-title">

                                <?php

                                echo htmlspecialchars(
                                    $course['course_name']
                                );

                                ?>

                            </h5>



                            <!-- COURSE CODE -->

                            <p class="text-primary mb-2">

                                <strong>

                                    <?php

                                    echo htmlspecialchars(
                                        $course['course_code']
                                    );

                                    ?>

                                </strong>

                            </p>



                            <!-- DEPARTMENT -->

                            <p class="mb-2">

                                <strong>
                                    Department:
                                </strong>

                                <?php

                                echo !empty($course['department_name'])
                                    ? htmlspecialchars(
                                        $course['department_name']
                                    )
                                    : "Not assigned";

                                ?>

                            </p>



                            <!-- TEACHER -->

                            <p class="mb-2">

                                <strong>
                                    Teacher:
                                </strong>

                                <?php

                                echo !empty($course['teacher_name'])
                                    ? htmlspecialchars(
                                        $course['teacher_name']
                                    )
                                    : "Not assigned";

                                ?>

                            </p>



                            <!-- CREDIT HOURS -->

                            <p class="mb-2">

                                <strong>
                                    Credit Hours:
                                </strong>

                                <?php

                                echo htmlspecialchars(
                                    $course['credit_hours']
                                );

                                ?>

                            </p>



                            <!-- DESCRIPTION -->

                            <p class="text-muted">

                                <?php

                                echo !empty($course['description'])
                                    ? htmlspecialchars(
                                        $course['description']
                                    )
                                    : "No description available.";

                                ?>

                            </p>



                            <!-- ENROLLED STATUS -->

                            <div class="mb-3">

                                <span class="badge bg-success">

                                    Enrolled

                                </span>

                            </div>



                            <!-- COURSE MATERIALS -->

                            <a
                                href="materials.php?course_id=<?php echo $course['id']; ?>"
                                class="btn btn-info w-100 mb-2"
                            >

                                Course Materials

                            </a>



                            <!-- VIEW ASSIGNMENTS -->

                            <a
                                href="assignments.php?course_id=<?php echo $course['id']; ?>"
                                class="btn btn-primary w-100"
                            >

                                View Assignments

                            </a>


                        </div>


                    </div>


                </div>


            <?php endwhile; ?>


        <?php else: ?>


            <div class="col-12">

                <div class="alert alert-info">

                    <h5 class="alert-heading">
                        No Courses Assigned
                    </h5>

                    <p class="mb-0">

                        You are not enrolled in any course yet.

                        Please contact the administrator to enroll
                        you in your courses.

                    </p>

                </div>

            </div>


        <?php endif; ?>


    </div>


</div>



<!-- ===============================
     BOOTSTRAP JS
=============================== -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


</body>

</html>