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


/* =====================================================
   ADD STUDENT
===================================================== */

if (isset($_POST['add_student'])) {

    $full_name = trim($_POST['full_name']);
    $student_id = trim($_POST['student_id']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $department_id = intval($_POST['department_id']);
    $semester = intval($_POST['semester']);
    $password = $_POST['password'];

    if (
        empty($full_name) ||
        empty($student_id) ||
        empty($email) ||
        empty($password) ||
        $department_id <= 0 ||
        $semester <= 0
    ) {

        $error = "Please fill all required fields.";

    } else {

        // Check duplicate email
        $stmt = mysqli_prepare(
            $conn,
            "SELECT id FROM users WHERE email = ? LIMIT 1"
        );

        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);

        $check_email = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($check_email) > 0) {

            $error = "This email is already registered.";

        } else {

            // Check duplicate Student ID
            $stmt = mysqli_prepare(
                $conn,
                "SELECT id FROM users WHERE student_id = ? LIMIT 1"
            );

            mysqli_stmt_bind_param($stmt, "s", $student_id);
            mysqli_stmt_execute($stmt);

            $check_student_id = mysqli_stmt_get_result($stmt);

            if (mysqli_num_rows($check_student_id) > 0) {

                $error = "This Student ID already exists.";

            } else {

                // Hash password
                $hashed_password = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                // Insert student
                $stmt = mysqli_prepare(
                    $conn,
                    "INSERT INTO users
                    (
                        full_name,
                        email,
                        phone,
                        address,
                        student_id,
                        semester,
                        department_id,
                        password,
                        role
                    )
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'student')"
                );

                mysqli_stmt_bind_param(
                    $stmt,
                    "sssssiis",
                    $full_name,
                    $email,
                    $phone,
                    $address,
                    $student_id,
                    $semester,
                    $department_id,
                    $hashed_password
                );

                if (mysqli_stmt_execute($stmt)) {

                    $message = "Student added successfully.";

                } else {

                    $error = "Failed to add student: " . mysqli_error($conn);
                }
            }
        }
    }
}


/* =====================================================
   DELETE STUDENT
===================================================== */

if (isset($_GET['delete'])) {

    $id = intval($_GET['delete']);

    $stmt = mysqli_prepare(
        $conn,
        "DELETE FROM users
         WHERE id = ? AND role = 'student'"
    );

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    header("Location: students.php");
    exit;
}


/* =====================================================
   GET DEPARTMENTS
===================================================== */

$departments = mysqli_query(
    $conn,
    "SELECT id, name
     FROM departments
     ORDER BY name ASC"
);


/* =====================================================
   GET STUDENTS
===================================================== */

$result = mysqli_query(
    $conn,
    "SELECT
        u.id,
        u.full_name,
        u.email,
        u.phone,
        u.address,
        u.student_id,
        u.semester,
        u.department_id,
        d.name AS department_name,
        u.created_at
     FROM users u
     LEFT JOIN departments d
        ON u.department_id = d.id
     WHERE u.role = 'student'
     ORDER BY u.id DESC"
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

    <title>Manage Students</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body>

<div class="container-fluid py-4">


    <!-- =================================================
         HEADER
    ================================================== -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2>Manage Students</h2>

            <p class="text-muted mb-0">
                Add, edit, enroll and manage university students.
            </p>

        </div>

        <a
            href="dashboard.php"
            class="btn btn-secondary"
        >
            Back to Dashboard
        </a>

    </div>


    <!-- =================================================
         SUCCESS MESSAGE
    ================================================== -->

    <?php if (!empty($message)): ?>

        <div class="alert alert-success">

            <?php echo htmlspecialchars($message); ?>

        </div>

    <?php endif; ?>


    <!-- =================================================
         ERROR MESSAGE
    ================================================== -->

    <?php if (!empty($error)): ?>

        <div class="alert alert-danger">

            <?php echo htmlspecialchars($error); ?>

        </div>

    <?php endif; ?>


    <!-- =================================================
         ADD STUDENT FORM
    ================================================== -->

    <div class="card shadow-sm mb-4">

        <div class="card-header">

            <h4 class="mb-0">
                Add New Student
            </h4>

        </div>

        <div class="card-body">

            <form method="POST">

                <div class="row g-3">


                    <!-- FULL NAME -->

                    <div class="col-md-6">

                        <label class="form-label">
                            Full Name *
                        </label>

                        <input
                            type="text"
                            name="full_name"
                            class="form-control"
                            placeholder="Enter full name"
                            required
                        >

                    </div>


                    <!-- STUDENT ID -->

                    <div class="col-md-6">

                        <label class="form-label">
                            Student ID *
                        </label>

                        <input
                            type="text"
                            name="student_id"
                            class="form-control"
                            placeholder="e.g. SE-2024-015"
                            required
                        >

                    </div>


                    <!-- EMAIL -->

                    <div class="col-md-6">

                        <label class="form-label">
                            Email *
                        </label>

                        <input
                            type="email"
                            name="email"
                            class="form-control"
                            placeholder="student@example.com"
                            required
                        >

                    </div>


                    <!-- PHONE -->

                    <div class="col-md-6">

                        <label class="form-label">
                            Phone
                        </label>

                        <input
                            type="text"
                            name="phone"
                            class="form-control"
                            placeholder="03XX-XXXXXXX"
                        >

                    </div>


                    <!-- ADDRESS -->

                    <div class="col-md-12">

                        <label class="form-label">
                            Address
                        </label>

                        <input
                            type="text"
                            name="address"
                            class="form-control"
                            placeholder="Enter address"
                        >

                    </div>


                    <!-- DEPARTMENT -->

                    <div class="col-md-6">

                        <label class="form-label">
                            Department / Program *
                        </label>

                        <select
                            name="department_id"
                            class="form-select"
                            required
                        >

                            <option value="">
                                Select Department / Program
                            </option>

                            <?php while ($department = mysqli_fetch_assoc($departments)): ?>

                                <option
                                    value="<?php echo $department['id']; ?>"
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


                    <!-- SEMESTER -->

                    <div class="col-md-6">

                        <label class="form-label">
                            Semester *
                        </label>

                        <select
                            name="semester"
                            class="form-select"
                            required
                        >

                            <option value="">
                                Select Semester
                            </option>

                            <?php for ($i = 1; $i <= 8; $i++): ?>

                                <option value="<?php echo $i; ?>">

                                    Semester <?php echo $i; ?>

                                </option>

                            <?php endfor; ?>

                        </select>

                    </div>


                    <!-- PASSWORD -->

                    <div class="col-md-6">

                        <label class="form-label">
                            Login Password *
                        </label>

                        <input
                            type="password"
                            name="password"
                            class="form-control"
                            placeholder="Create login password"
                            required
                        >

                    </div>

                </div>


                <!-- ADD BUTTON -->

                <div class="mt-4">

                    <button
                        type="submit"
                        name="add_student"
                        class="btn btn-primary"
                    >
                        Add Student
                    </button>

                </div>

            </form>

        </div>

    </div>


    <!-- =================================================
         STUDENT LIST
    ================================================== -->

    <div class="card shadow-sm">

        <div class="card-header">

            <h4 class="mb-0">
                Registered Students
            </h4>

        </div>

        <div class="card-body">

            <div class="table-responsive">

                <table
                    class="table table-bordered table-hover align-middle"
                >

                    <thead class="table-dark">

                        <tr>

                            <th>#</th>

                            <th>Student ID</th>

                            <th>Name</th>

                            <th>Email</th>

                            <th>Phone</th>

                            <th>Department / Program</th>

                            <th>Semester</th>

                            <th>Actions</th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php if (mysqli_num_rows($result) > 0): ?>

                        <?php while ($student = mysqli_fetch_assoc($result)): ?>

                            <tr>


                                <!-- ID -->

                                <td>

                                    <?php
                                    echo $student['id'];
                                    ?>

                                </td>


                                <!-- STUDENT ID -->

                                <td>

                                    <?php

                                    echo !empty($student['student_id'])

                                        ? htmlspecialchars(
                                            $student['student_id']
                                        )

                                        : "Not set";

                                    ?>

                                </td>


                                <!-- NAME -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $student['full_name']
                                    );

                                    ?>

                                </td>


                                <!-- EMAIL -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $student['email']
                                    );

                                    ?>

                                </td>


                                <!-- PHONE -->

                                <td>

                                    <?php

                                    echo !empty($student['phone'])

                                        ? htmlspecialchars(
                                            $student['phone']
                                        )

                                        : "Not set";

                                    ?>

                                </td>


                                <!-- DEPARTMENT -->

                                <td>

                                    <?php

                                    echo !empty(
                                        $student['department_name']
                                    )

                                        ? htmlspecialchars(
                                            $student['department_name']
                                        )

                                        : "Not set";

                                    ?>

                                </td>


                                <!-- SEMESTER -->

                                <td>

                                    <?php

                                    echo !empty(
                                        $student['semester']
                                    )

                                        ? "Semester " .
                                          $student['semester']

                                        : "Not set";

                                    ?>

                                </td>


                                <!-- ACTIONS -->

                                <td>

                                    <!-- EDIT -->

                                    <a
                                        href="edit_student.php?id=<?php echo $student['id']; ?>"
                                        class="btn btn-sm btn-warning mb-1"
                                    >
                                        Edit
                                    </a>


                                    <!-- ENROLL -->

                                    <a
                                        href="enroll_student.php?student_id=<?php echo $student['id']; ?>"
                                        class="btn btn-sm btn-primary mb-1"
                                    >
                                        Enroll
                                    </a>


                                    <!-- DELETE -->

                                    <a
                                        href="students.php?delete=<?php echo $student['id']; ?>"
                                        class="btn btn-sm btn-danger mb-1"
                                        onclick="return confirm('Are you sure you want to delete this student?');"
                                    >
                                        Delete
                                    </a>

                                </td>

                            </tr>

                        <?php endwhile; ?>


                    <?php else: ?>

                        <tr>

                            <td
                                colspan="8"
                                class="text-center"
                            >

                                No students found.

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