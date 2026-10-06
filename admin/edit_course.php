```php
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

// Check course ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: courses.php");
    exit;
}

$id = intval($_GET['id']);

$error = "";


// ======================================
// GET COURSE
// ======================================

$sql = "
    SELECT *
    FROM courses
    WHERE id = ?
";

$stmt = mysqli_prepare($conn, $sql);

mysqli_stmt_bind_param(
    $stmt,
    "i",
    $id
);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$course = mysqli_fetch_assoc($result);

if (!$course) {
    die("Course not found.");
}


// ======================================
// GET DEPARTMENTS
// ======================================

$departments = mysqli_query(
    $conn,
    "SELECT id, name
     FROM departments
     ORDER BY name ASC"
);


// ======================================
// GET TEACHERS
// ======================================

$teachers = mysqli_query(
    $conn,
    "SELECT id, full_name, teacher_id
     FROM users
     WHERE role = 'teacher'
     ORDER BY full_name ASC"
);


// ======================================
// UPDATE COURSE
// ======================================

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $course_code = trim($_POST['course_code']);
    $course_name = trim($_POST['course_name']);
    $department_id = intval($_POST['department_id']);

    // Teacher can be empty
    $teacher_id = !empty($_POST['teacher_id'])
        ? intval($_POST['teacher_id'])
        : 0;

    $description = trim($_POST['description']);
    $credit_hours = intval($_POST['credit_hours']);


    // Check required fields

    if (
        empty($course_code) ||
        empty($course_name) ||
        $department_id <= 0 ||
        $credit_hours <= 0
    ) {

        $error = "Please fill all required fields.";

    } else {


        // ======================================
        // CHECK DUPLICATE COURSE CODE
        // ======================================

        $check_sql = "
            SELECT id
            FROM courses
            WHERE course_code = ?
            AND id != ?
        ";

        $check_stmt = mysqli_prepare(
            $conn,
            $check_sql
        );

        mysqli_stmt_bind_param(
            $check_stmt,
            "si",
            $course_code,
            $id
        );

        mysqli_stmt_execute($check_stmt);

        $check_result = mysqli_stmt_get_result(
            $check_stmt
        );


        if (mysqli_num_rows($check_result) > 0) {

            $error = "This course code already exists.";

        } else {


            // ======================================
            // UPDATE COURSE
            // ======================================

            $update_sql = "
                UPDATE courses
                SET
                    department_id = ?,
                    teacher_id = NULLIF(?, 0),
                    course_code = ?,
                    course_name = ?,
                    description = ?,
                    credit_hours = ?
                WHERE id = ?
            ";

            $update_stmt = mysqli_prepare(
                $conn,
                $update_sql
            );


            mysqli_stmt_bind_param(
                $update_stmt,
                "iisssii",
                $department_id,
                $teacher_id,
                $course_code,
                $course_name,
                $description,
                $credit_hours,
                $id
            );


            if (mysqli_stmt_execute($update_stmt)) {

                header("Location: courses.php");
                exit;

            } else {

                $error = "Failed to update course: " .
                         mysqli_error($conn);
            }
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
        content="width=device-width, initial-scale=1.0"
    >

    <title>Edit Course - University LMS</title>


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>


<body class="bg-light">


<div class="container mt-5">


    <div class="card shadow">


        <!-- HEADER -->

        <div class="card-header bg-primary text-white">

            <h3 class="mb-0">
                Edit Course
            </h3>

        </div>


        <!-- BODY -->

        <div class="card-body">


            <!-- ERROR -->

            <?php if (!empty($error)): ?>

                <div class="alert alert-danger">

                    <?php
                    echo htmlspecialchars($error);
                    ?>

                </div>

            <?php endif; ?>


            <!-- FORM -->

            <form method="POST">


                <!-- COURSE CODE -->

                <div class="mb-3">

                    <label class="form-label">
                        Course Code *
                    </label>

                    <input
                        type="text"
                        name="course_code"
                        class="form-control"
                        value="<?php
                            echo htmlspecialchars(
                                $course['course_code']
                            );
                        ?>"
                        required
                    >

                </div>


                <!-- COURSE NAME -->

                <div class="mb-3">

                    <label class="form-label">
                        Course Name *
                    </label>

                    <input
                        type="text"
                        name="course_name"
                        class="form-control"
                        value="<?php
                            echo htmlspecialchars(
                                $course['course_name']
                            );
                        ?>"
                        required
                    >

                </div>


                <!-- DEPARTMENT -->

                <div class="mb-3">

                    <label class="form-label">
                        Department *
                    </label>


                    <select
                        name="department_id"
                        class="form-select"
                        required
                    >

                        <option value="">
                            Select Department
                        </option>


                        <?php while (
                            $department =
                            mysqli_fetch_assoc($departments)
                        ): ?>

                            <option
                                value="<?php
                                    echo $department['id'];
                                ?>"
                                <?php

                                if (
                                    $department['id']
                                    ==
                                    $course['department_id']
                                ) {
                                    echo "selected";
                                }

                                ?>
                            >

                                <?php
                                echo htmlspecialchars(
                                    $department['name']
                                );
                                ?>

                            </option>

                        <?php endwhile; ?>

                    </select>

                </div>


                <!-- TEACHER -->

                <div class="mb-3">

                    <label class="form-label">
                        Teacher
                    </label>


                    <select
                        name="teacher_id"
                        class="form-select"
                    >

                        <option value="0">
                            No Teacher Assigned
                        </option>


                        <?php while (
                            $teacher =
                            mysqli_fetch_assoc($teachers)
                        ): ?>

                            <option
                                value="<?php
                                    echo $teacher['id'];
                                ?>"

                                <?php

                                if (
                                    !empty(
                                        $course['teacher_id']
                                    )
                                    &&
                                    $teacher['id']
                                    ==
                                    $course['teacher_id']
                                ) {
                                    echo "selected";
                                }

                                ?>
                            >

                                <?php

                                echo htmlspecialchars(
                                    $teacher['full_name']
                                );


                                if (
                                    !empty(
                                        $teacher['teacher_id']
                                    )
                                ) {

                                    echo " (" .
                                        htmlspecialchars(
                                            $teacher['teacher_id']
                                        )
                                        . ")";

                                }

                                ?>

                            </option>

                        <?php endwhile; ?>

                    </select>

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
                    ><?php

                    echo htmlspecialchars(
                        $course['description']
                    );

                    ?></textarea>

                </div>


                <!-- CREDIT HOURS -->

                <div class="mb-3">

                    <label class="form-label">
                        Credit Hours *
                    </label>


                    <input
                        type="number"
                        name="credit_hours"
                        class="form-control"
                        min="1"
                        max="6"
                        value="<?php
                            echo htmlspecialchars(
                                $course['credit_hours']
                            );
                        ?>"
                        required
                    >

                </div>


                <!-- BUTTONS -->

                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Update Course
                </button>


                <a
                    href="courses.php"
                    class="btn btn-secondary"
                >
                    Cancel
                </a>


            </form>

        </div>

    </div>

</div>


</body>

</html>
