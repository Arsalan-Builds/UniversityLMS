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


// ===============================
// ADD COURSE
// ===============================

if (isset($_POST['add_course'])) {

    $department_id = intval($_POST['department_id']);
    $teacher_id = !empty($_POST['teacher_id']) ? intval($_POST['teacher_id']) : null;

    $course_code = trim($_POST['course_code']);
    $course_name = trim($_POST['course_name']);
    $description = trim($_POST['description']);
    $credit_hours = intval($_POST['credit_hours']);

    if (
        empty($course_code) ||
        empty($course_name) ||
        $department_id <= 0
    ) {

        $message = "Please fill all required fields.";

    } else {

        // Check duplicate course code
        $check = mysqli_prepare(
            $conn,
            "SELECT id FROM courses WHERE course_code = ?"
        );

        mysqli_stmt_bind_param($check, "s", $course_code);
        mysqli_stmt_execute($check);
        mysqli_stmt_store_result($check);

        if (mysqli_stmt_num_rows($check) > 0) {

            $message = "Course code already exists.";

        } else {

            $stmt = mysqli_prepare(
                $conn,
                "INSERT INTO courses
                (department_id, teacher_id, course_code, course_name, description, credit_hours)
                VALUES (?, ?, ?, ?, ?, ?)"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "iisssi",
                $department_id,
                $teacher_id,
                $course_code,
                $course_name,
                $description,
                $credit_hours
            );

            if (mysqli_stmt_execute($stmt)) {

                header("Location: courses.php");
                exit;

            } else {

                $message = "Error adding course.";
            }
        }
    }
}


// ===============================
// DELETE COURSE
// ===============================

if (isset($_GET['delete'])) {

    $id = intval($_GET['delete']);

    $stmt = mysqli_prepare(
        $conn,
        "DELETE FROM courses WHERE id = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    header("Location: courses.php");
    exit;
}


// ===============================
// GET DEPARTMENTS
// ===============================

$departments = mysqli_query(
    $conn,
    "SELECT id, name FROM departments ORDER BY name ASC"
);


// ===============================
// GET TEACHERS
// ===============================

$teachers = mysqli_query(
    $conn,
    "SELECT id, full_name
     FROM users
     WHERE role = 'teacher'
     ORDER BY full_name ASC"
);


// ===============================
// GET COURSES
// ===============================

$courses = mysqli_query(
    $conn,
    "SELECT
        courses.id,
        courses.course_code,
        courses.course_name,
        courses.description,
        courses.credit_hours,
        departments.name AS department_name,
        users.full_name AS teacher_name

     FROM courses

     INNER JOIN departments
        ON courses.department_id = departments.id

     LEFT JOIN users
        ON courses.teacher_id = users.id

     ORDER BY courses.id DESC"
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

    <title>Courses - University LMS</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body class="bg-light">


<!-- NAVBAR -->

<nav class="navbar navbar-dark bg-dark">

    <div class="container">

        <a
            class="navbar-brand"
            href="dashboard.php"
        >
            University LMS - Admin
        </a>

        <a
            href="../logout.php"
            class="btn btn-light"
        >
            Logout
        </a>

    </div>

</nav>


<div class="container mt-5">


    <!-- HEADER -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <h2>Course Management</h2>

        <a
            href="dashboard.php"
            class="btn btn-secondary"
        >
            Back to Dashboard
        </a>

    </div>


    <!-- MESSAGE -->

    <?php if (!empty($message)): ?>

        <div class="alert alert-warning">

            <?php echo htmlspecialchars($message); ?>

        </div>

    <?php endif; ?>


    <!-- ADD COURSE -->

    <div class="card shadow mb-5">

        <div class="card-header bg-primary text-white">

            <h4 class="mb-0">
                Add New Course
            </h4>

        </div>


        <div class="card-body">

            <form method="POST">

                <div class="row">


                    <!-- COURSE CODE -->

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Course Code *
                        </label>

                        <input
                            type="text"
                            name="course_code"
                            class="form-control"
                            placeholder="Example: CS-101"
                            required
                        >

                    </div>


                    <!-- COURSE NAME -->

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Course Name *
                        </label>

                        <input
                            type="text"
                            name="course_name"
                            class="form-control"
                            placeholder="Example: Introduction to Programming"
                            required
                        >

                    </div>


                    <!-- DEPARTMENT -->

                    <div class="col-md-6 mb-3">

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

                            <?php while ($department = mysqli_fetch_assoc($departments)): ?>

                                <option
                                    value="<?php echo $department['id']; ?>"
                                >

                                    <?php echo htmlspecialchars($department['name']); ?>

                                </option>

                            <?php endwhile; ?>

                        </select>

                    </div>


                    <!-- TEACHER -->

                    <div class="col-md-6 mb-3">

                        <label class="form-label">
                            Assign Teacher
                        </label>

                        <select
                            name="teacher_id"
                            class="form-select"
                        >

                            <option value="">
                                No Teacher Assigned
                            </option>

                            <?php while ($teacher = mysqli_fetch_assoc($teachers)): ?>

                                <option
                                    value="<?php echo $teacher['id']; ?>"
                                >

                                    <?php echo htmlspecialchars($teacher['full_name']); ?>

                                </option>

                            <?php endwhile; ?>

                        </select>

                    </div>


                    <!-- CREDIT HOURS -->

                    <div class="col-md-4 mb-3">

                        <label class="form-label">
                            Credit Hours
                        </label>

                        <input
                            type="number"
                            name="credit_hours"
                            class="form-control"
                            value="3"
                            min="1"
                            max="6"
                        >

                    </div>


                    <!-- DESCRIPTION -->

                    <div class="col-md-8 mb-3">

                        <label class="form-label">
                            Description
                        </label>

                        <textarea
                            name="description"
                            class="form-control"
                            rows="3"
                            placeholder="Course description"
                        ></textarea>

                    </div>

                </div>


                <button
                    type="submit"
                    name="add_course"
                    class="btn btn-primary"
                >
                    Add Course
                </button>

            </form>

        </div>

    </div>


    <!-- COURSE LIST -->

    <div class="card shadow">

        <div class="card-header bg-dark text-white">

            <h4 class="mb-0">
                All Courses
            </h4>

        </div>


        <div class="card-body">

            <div class="table-responsive">

                <table class="table table-bordered table-hover">

                    <thead class="table-dark">

                        <tr>

                            <th>ID</th>

                            <th>Code</th>

                            <th>Course Name</th>

                            <th>Department</th>

                            <th>Teacher</th>

                            <th>Credit Hours</th>

                            <th>Action</th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php while ($course = mysqli_fetch_assoc($courses)): ?>

                        <tr>

                            <td>
                                <?php echo $course['id']; ?>
                            </td>


                            <td>
                                <?php echo htmlspecialchars($course['course_code']); ?>
                            </td>


                            <td>
                                <?php echo htmlspecialchars($course['course_name']); ?>
                            </td>


                            <td>
                                <?php echo htmlspecialchars($course['department_name']); ?>
                            </td>


                            <td>

                                <?php

                                echo $course['teacher_name']
                                    ? htmlspecialchars($course['teacher_name'])
                                    : "Not Assigned";

                                ?>

                            </td>


                            <td>
                                <?php echo $course['credit_hours']; ?>
                            </td>


                            <!-- ACTION BUTTONS -->

                            <td>

                                <!-- EDIT -->

                                <a
                                    href="edit_course.php?id=<?php echo $course['id']; ?>"
                                    class="btn btn-sm btn-warning"
                                >
                                    Edit
                                </a>


                                <!-- DELETE -->

                                <a
                                    href="courses.php?delete=<?php echo $course['id']; ?>"
                                    class="btn btn-sm btn-danger"
                                    onclick="return confirm('Are you sure you want to delete this course?');"
                                >
                                    Delete
                                </a>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

</div>

</body>

</html>

