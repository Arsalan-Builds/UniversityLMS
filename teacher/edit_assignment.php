```php
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
   CHECK ASSIGNMENT ID
========================= */

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: dashboard.php");
    exit;
}

$assignment_id = intval($_GET['id']);


/* =========================
   GET ASSIGNMENT
   AND VERIFY TEACHER
========================= */

$stmt = mysqli_prepare($conn, "

    SELECT
        assignments.id,
        assignments.course_id,
        assignments.title,
        assignments.description,
        assignments.due_date,
        courses.course_name

    FROM assignments

    INNER JOIN courses
        ON assignments.course_id = courses.id

    WHERE assignments.id = ?
    AND courses.teacher_id = ?

");

mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $assignment_id,
    $teacher_id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$assignment = mysqli_fetch_assoc($result);


if (!$assignment) {

    die("Assignment not found or you are not authorized to edit it.");

}


$course_id = $assignment['course_id'];


/* =========================
   UPDATE ASSIGNMENT
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $due_date = $_POST['due_date'];


    if (empty($title) || empty($due_date)) {

        $error = "Assignment title and due date are required.";

    } else {

        $stmt = mysqli_prepare($conn, "

            UPDATE assignments

            SET
                title = ?,
                description = ?,
                due_date = ?

            WHERE id = ?

        ");

        mysqli_stmt_bind_param(
            $stmt,
            "sssi",
            $title,
            $description,
            $due_date,
            $assignment_id
        );


        if (mysqli_stmt_execute($stmt)) {

            header(
                "Location: course.php?id=" . $course_id
            );

            exit;

        } else {

            $error = "Failed to update assignment.";

        }

    }

}

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
        Edit Assignment - University LMS
    </title>


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
                href="course.php?id=<?php echo $course_id; ?>"
                class="btn btn-light btn-sm me-2"
            >
                Back to Course
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


    <div class="row justify-content-center">

        <div class="col-md-8">


            <div class="card shadow-sm">


                <div class="card-header bg-dark text-white">

                    <h4 class="mb-0">
                        Edit Assignment
                    </h4>

                </div>


                <div class="card-body">


                    <!-- COURSE -->

                    <div class="alert alert-info">

                        <strong>
                            Course:
                        </strong>

                        <?php
                        echo htmlspecialchars(
                            $assignment['course_name']
                        );
                        ?>

                    </div>


                    <!-- ERROR -->

                    <?php if (isset($error)): ?>

                        <div class="alert alert-danger">

                            <?php
                            echo htmlspecialchars($error);
                            ?>

                        </div>

                    <?php endif; ?>


                    <form method="POST">


                        <!-- TITLE -->

                        <div class="mb-3">

                            <label class="form-label">
                                Assignment Title
                            </label>

                            <input
                                type="text"
                                name="title"
                                class="form-control"
                                value="<?php echo htmlspecialchars($assignment['title']); ?>"
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
                                rows="5"
                            ><?php echo htmlspecialchars($assignment['description'] ?? ''); ?></textarea>

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
                                value="<?php
                                    echo !empty($assignment['due_date'])
                                        ? date(
                                            'Y-m-d\TH:i',
                                            strtotime(
                                                $assignment['due_date']
                                            )
                                        )
                                        : '';
                                ?>"
                                required
                            >

                        </div>


                        <!-- BUTTONS -->

                        <button
                            type="submit"
                            class="btn btn-success"
                        >
                            Update Assignment
                        </button>


                        <a
                            href="course.php?id=<?php echo $course_id; ?>"
                            class="btn btn-secondary"
                        >
                            Cancel
                        </a>


                    </form>


                </div>

            </div>


        </div>

    </div>


</div>


</body>

</html>
```
