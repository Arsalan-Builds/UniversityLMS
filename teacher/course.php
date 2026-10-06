<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

include "../includes/db.php";

/* =========================
   TEACHER LOGIN CHECK
========================= */

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit;
}

$teacher_id = $_SESSION['user_id'];


/* =========================
   CHECK COURSE ID
========================= */

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$course_id = intval($_GET['id']);


/* =========================
   GET COURSE
========================= */

$stmt = mysqli_prepare($conn, "

    SELECT
        courses.id,
        courses.course_code,
        courses.course_name,
        courses.description,
        courses.credit_hours,
        departments.name AS department_name

    FROM courses

    LEFT JOIN departments
        ON courses.department_id = departments.id

    WHERE courses.id = ?
    AND courses.teacher_id = ?

");

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $course_id,
    $teacher_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$course = mysqli_fetch_assoc($result);


if (!$course) {

    die("Course not found or you are not assigned to this course.");

}


/* =========================
   ADD ASSIGNMENT
========================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST['add_assignment'])
) {

    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $due_date = $_POST['due_date'];


    if (empty($title) || empty($due_date)) {

        $error = "Assignment title and due date are required.";

    } else {

        $stmt = mysqli_prepare($conn, "

            INSERT INTO assignments
            (
                course_id,
                title,
                description,
                due_date
            )

            VALUES (?, ?, ?, ?)

        ");

        mysqli_stmt_bind_param(
            $stmt,
            "isss",
            $course_id,
            $title,
            $description,
            $due_date
        );


        if (mysqli_stmt_execute($stmt)) {

            header(
                "Location: course.php?id=" . $course_id
            );

            exit;

        } else {

            $error = "Failed to create assignment.";

        }

    }

}


/* =========================
   DELETE ASSIGNMENT
========================= */

if (isset($_GET['delete_assignment'])) {

    $assignment_id = intval(
        $_GET['delete_assignment']
    );


    $stmt = mysqli_prepare($conn, "

        DELETE FROM assignments

        WHERE id = ?
        AND course_id = ?

    ");


    mysqli_stmt_bind_param(
        $stmt,
        "ii",
        $assignment_id,
        $course_id
    );


    mysqli_stmt_execute($stmt);


    header(
        "Location: course.php?id=" . $course_id
    );

    exit;

}


/* =========================
   GET ASSIGNMENTS
   WITH SUBMISSION COUNT
========================= */

$stmt = mysqli_prepare($conn, "

    SELECT

        assignments.id,
        assignments.title,
        assignments.description,
        assignments.due_date,
        assignments.created_at,

        COUNT(submissions.id) AS submission_count

    FROM assignments

    LEFT JOIN submissions
        ON assignments.id = submissions.assignment_id

    WHERE assignments.course_id = ?

    GROUP BY

        assignments.id,
        assignments.title,
        assignments.description,
        assignments.due_date,
        assignments.created_at

    ORDER BY assignments.due_date ASC

");


mysqli_stmt_bind_param(
    $stmt,
    "i",
    $course_id
);

mysqli_stmt_execute($stmt);

$assignments_result =
    mysqli_stmt_get_result($stmt);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>

        Manage Course -

        <?php
        echo htmlspecialchars(
            $course['course_name']
        );
        ?>

    </title>


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <style>

        .action-buttons {

            display: flex;

            flex-direction: column;

            gap: 8px;

            min-width: 180px;

        }


        .action-buttons .btn {

            width: 100%;

        }


        .assignment-table th,
        .assignment-table td {

            vertical-align: middle;

        }


        .description-cell {

            max-width: 250px;

        }


        .course-actions {

            display: flex;

            gap: 10px;

            flex-wrap: wrap;

        }

    </style>

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
                class="btn btn-light btn-sm me-2"
            >

                Dashboard

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


    <!-- =========================
         COURSE INFORMATION
    ========================= -->

    <div class="card shadow-sm mb-4">


        <div class="card-body">


            <div
                class="d-flex justify-content-between align-items-start flex-wrap gap-3"
            >


                <div>


                    <h2 class="mb-3">

                        <?php

                        echo htmlspecialchars(
                            $course['course_name']
                        );

                        ?>

                    </h2>


                    <p>

                        <strong>
                            Course Code:
                        </strong>

                        <?php

                        echo htmlspecialchars(
                            $course['course_code']
                        );

                        ?>

                    </p>


                    <p>

                        <strong>
                            Department:
                        </strong>

                        <?php

                        echo htmlspecialchars(
                            $course['department_name']
                            ?? 'Not assigned'
                        );

                        ?>

                    </p>


                    <p>

                        <strong>
                            Credit Hours:
                        </strong>

                        <?php

                        echo htmlspecialchars(
                            $course['credit_hours']
                        );

                        ?>

                    </p>


                    <p class="mb-0">


                        <strong>
                            Description:
                        </strong>


                        <br>


                        <?php

                        echo nl2br(
                            htmlspecialchars(
                                $course['description']
                                ?? 'No description available.'
                            )
                        );

                        ?>


                    </p>


                </div>



                <!-- COURSE ACTIONS -->

                <div class="course-actions">


                    <a
                        href="students.php?course_id=<?php echo $course_id; ?>"
                        class="btn btn-success"
                    >

                        Enrolled Students

                    </a>


                    <a
                        href="materials.php?id=<?php echo $course_id; ?>"
                        class="btn btn-info"
                    >

                        Course Materials

                    </a>


                </div>


            </div>


        </div>


    </div>



    <!-- =========================
         ERROR MESSAGE
    ========================= -->

    <?php if (isset($error)): ?>


        <div class="alert alert-danger">

            <?php

            echo htmlspecialchars(
                $error
            );

            ?>

        </div>


    <?php endif; ?>



    <!-- =========================
         CREATE ASSIGNMENT
    ========================= -->

    <div class="card shadow-sm mb-5">


        <div class="card-header bg-primary text-white">


            <h4 class="mb-0">

                Create Assignment

            </h4>


        </div>



        <div class="card-body">


            <form
                method="POST"
                action=""
            >


                <!-- TITLE -->

                <div class="mb-3">


                    <label class="form-label">

                        Assignment Title

                    </label>


                    <input
                        type="text"
                        name="title"
                        class="form-control"
                        placeholder="Enter assignment title"
                        required
                    >


                </div>



                <!-- DESCRIPTION -->

                <div class="mb-3">


                    <label class="form-label">

                        Description

                    </label>


                    <textarea
                        name="description"
                        class="form-control"
                        rows="4"
                        placeholder="Enter assignment description"
                    ></textarea>


                </div>



                <!-- DUE DATE -->

                <div class="mb-3">


                    <label class="form-label">

                        Due Date

                    </label>


                    <input
                        type="datetime-local"
                        name="due_date"
                        class="form-control"
                        required
                    >


                </div>



                <button
                    type="submit"
                    name="add_assignment"
                    class="btn btn-success"
                >

                    Create Assignment

                </button>


            </form>


        </div>


    </div>



    <!-- =========================
         ASSIGNMENT LIST
    ========================= -->

    <div class="card shadow-sm">


        <div class="card-header bg-dark text-white">


            <h4 class="mb-0">

                Course Assignments

            </h4>


        </div>



        <div class="card-body">


            <?php if (
                mysqli_num_rows(
                    $assignments_result
                ) > 0
            ): ?>


                <div class="table-responsive">


                    <table
                        class="table table-bordered table-hover assignment-table"
                    >


                        <thead class="table-light">


                            <tr>


                                <th>
                                    ID
                                </th>


                                <th>
                                    Title
                                </th>


                                <th>
                                    Description
                                </th>


                                <th>
                                    Due Date
                                </th>


                                <th>
                                    Submissions
                                </th>


                                <th style="width: 190px;">
                                    Actions
                                </th>


                            </tr>


                        </thead>



                        <tbody>


                        <?php while (
                            $assignment =
                            mysqli_fetch_assoc(
                                $assignments_result
                            )
                        ): ?>


                            <tr>


                                <!-- ID -->

                                <td>

                                    <?php

                                    echo $assignment['id'];

                                    ?>

                                </td>



                                <!-- TITLE -->

                                <td>


                                    <strong>

                                        <?php

                                        echo htmlspecialchars(
                                            $assignment['title']
                                        );

                                        ?>

                                    </strong>


                                </td>



                                <!-- DESCRIPTION -->

                                <td class="description-cell">


                                    <?php


                                    $description =
                                        $assignment['description'];


                                    if (
                                        empty($description)
                                    ) {


                                        echo '<span class="text-muted">
                                                No description
                                              </span>';


                                    } else {


                                        echo nl2br(
                                            htmlspecialchars(
                                                $description
                                            )
                                        );


                                    }


                                    ?>


                                </td>



                                <!-- DUE DATE -->

                                <td>


                                    <?php


                                    if (
                                        !empty(
                                            $assignment['due_date']
                                        )
                                    ) {


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


                                </td>



                                <!-- SUBMISSION COUNT -->

                                <td class="text-center">


                                    <span
                                        class="badge bg-success fs-6"
                                    >


                                        <?php

                                        echo $assignment[
                                            'submission_count'
                                        ];

                                        ?>


                                    </span>


                                </td>



                                <!-- ACTIONS -->

                                <td>


                                    <div class="action-buttons">


                                        <!-- VIEW SUBMISSIONS -->

                                        <a
                                            href="submissions.php?assignment_id=<?php echo $assignment['id']; ?>"
                                            class="btn btn-primary"
                                        >

                                            View Submissions

                                        </a>



                                        <!-- EDIT ASSIGNMENT -->

                                        <a
                                            href="edit_assignment.php?id=<?php echo $assignment['id']; ?>"
                                            class="btn btn-warning"
                                        >

                                            Edit Assignment

                                        </a>



                                        <!-- COURSE MATERIALS -->

                                        <a
                                            href="materials.php?id=<?php echo $course_id; ?>"
                                            class="btn btn-info"
                                        >

                                            Course Materials

                                        </a>



                                        <!-- DELETE -->

                                        <a
                                            href="course.php?id=<?php echo $course_id; ?>&delete_assignment=<?php echo $assignment['id']; ?>"
                                            class="btn btn-danger"
                                            onclick="return confirm('Are you sure you want to delete this assignment?');"
                                        >

                                            Delete Assignment

                                        </a>


                                    </div>


                                </td>


                            </tr>


                        <?php endwhile; ?>


                        </tbody>


                    </table>


                </div>


            <?php else: ?>


                <div class="alert alert-info mb-0">

                    No assignments have been created for this course yet.

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

