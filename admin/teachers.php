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
   ADD TEACHER
===================================================== */

if (isset($_POST['add_teacher'])) {

    $full_name = trim($_POST['full_name']);
    $teacher_id = trim($_POST['teacher_id']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $department_id = intval($_POST['department_id']);
    $password = $_POST['password'];


    // Validation

    if (
        empty($full_name) ||
        empty($teacher_id) ||
        empty($email) ||
        empty($password) ||
        $department_id <= 0
    ) {

        $error = "Please fill all required fields.";

    } else {


        // =================================================
        // CHECK DUPLICATE EMAIL
        // =================================================

        $stmt = mysqli_prepare(
            $conn,
            "SELECT id
             FROM users
             WHERE email = ?
             LIMIT 1"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "s",
            $email
        );

        mysqli_stmt_execute($stmt);

        $email_result = mysqli_stmt_get_result($stmt);


        if (mysqli_num_rows($email_result) > 0) {

            $error = "This email is already registered.";

        } else {


            // =================================================
            // CHECK DUPLICATE TEACHER ID
            // =================================================

            $stmt = mysqli_prepare(
                $conn,
                "SELECT id
                 FROM users
                 WHERE teacher_id = ?
                 LIMIT 1"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "s",
                $teacher_id
            );

            mysqli_stmt_execute($stmt);

            $teacher_result = mysqli_stmt_get_result($stmt);


            if (mysqli_num_rows($teacher_result) > 0) {

                $error = "This Teacher ID already exists.";

            } else {


                // =================================================
                // HASH PASSWORD
                // =================================================

                $hashed_password = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );


                // =================================================
                // INSERT TEACHER
                // =================================================

                $stmt = mysqli_prepare(
                    $conn,
                    "INSERT INTO users
                    (
                        full_name,
                        email,
                        phone,
                        address,
                        student_id,
                        teacher_id,
                        department_id,
                        password,
                        role
                    )
                    VALUES (?, ?, ?, ?, NULL, ?, ?, ?, 'teacher')"
                );


                mysqli_stmt_bind_param(
                    $stmt,
                    "sssssis",
                    $full_name,
                    $email,
                    $phone,
                    $address,
                    $teacher_id,
                    $department_id,
                    $hashed_password
                );


                if (mysqli_stmt_execute($stmt)) {

                    $message = "Teacher added successfully.";

                } else {

                    $error = "Failed to add teacher: " . mysqli_error($conn);
                }
            }
        }
    }
}


/* =====================================================
   DELETE TEACHER
===================================================== */

if (isset($_GET['delete'])) {

    $id = intval($_GET['delete']);


    $stmt = mysqli_prepare(
        $conn,
        "DELETE FROM users
         WHERE id = ?
         AND role = 'teacher'"
    );


    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $id
    );


    mysqli_stmt_execute($stmt);


    header("Location: teachers.php");
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
   GET TEACHERS
===================================================== */

$result = mysqli_query(
    $conn,
    "SELECT
        u.id,
        u.full_name,
        u.email,
        u.phone,
        u.address,
        u.teacher_id,
        u.department_id,
        d.name AS department_name,
        u.created_at
     FROM users u
     LEFT JOIN departments d
        ON u.department_id = d.id
     WHERE u.role = 'teacher'
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

    <title>Manage Teachers</title>

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

            <h2>Manage Teachers</h2>

            <p class="text-muted mb-0">
                Add, edit and manage university teachers.
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

            <?php
            echo htmlspecialchars($message);
            ?>

        </div>

    <?php endif; ?>


    <!-- =================================================
         ERROR MESSAGE
    ================================================== -->

    <?php if (!empty($error)): ?>

        <div class="alert alert-danger">

            <?php
            echo htmlspecialchars($error);
            ?>

        </div>

    <?php endif; ?>


    <!-- =================================================
         ADD TEACHER
    ================================================== -->

    <div class="card shadow-sm mb-4">


        <div class="card-header">

            <h4 class="mb-0">
                Add New Teacher
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
                            placeholder="Enter teacher full name"
                            required
                        >

                    </div>


                    <!-- TEACHER ID -->

                    <div class="col-md-6">

                        <label class="form-label">
                            Teacher ID *
                        </label>

                        <input
                            type="text"
                            name="teacher_id"
                            class="form-control"
                            placeholder="e.g. TCH-001"
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
                            placeholder="teacher@example.com"
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
                            placeholder="Enter teacher address"
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


                <!-- BUTTON -->

                <div class="mt-4">

                    <button
                        type="submit"
                        name="add_teacher"
                        class="btn btn-success"
                    >
                        Add Teacher
                    </button>

                </div>


            </form>


        </div>

    </div>


    <!-- =================================================
         TEACHER LIST
    ================================================== -->

    <div class="card shadow-sm">


        <div class="card-header">

            <h4 class="mb-0">
                Registered Teachers
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

                            <th>Teacher ID</th>

                            <th>Name</th>

                            <th>Email</th>

                            <th>Phone</th>

                            <th>Department</th>

                            <th>Actions</th>

                        </tr>


                    </thead>


                    <tbody>


                    <?php if (mysqli_num_rows($result) > 0): ?>


                        <?php while ($teacher = mysqli_fetch_assoc($result)): ?>


                            <tr>


                                <!-- ID -->

                                <td>

                                    <?php
                                    echo $teacher['id'];
                                    ?>

                                </td>


                                <!-- TEACHER ID -->

                                <td>

                                    <?php

                                    echo !empty(
                                        $teacher['teacher_id']
                                    )

                                        ? htmlspecialchars(
                                            $teacher['teacher_id']
                                        )

                                        : "Not set";

                                    ?>

                                </td>


                                <!-- NAME -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $teacher['full_name']
                                    );

                                    ?>

                                </td>


                                <!-- EMAIL -->

                                <td>

                                    <?php

                                    echo htmlspecialchars(
                                        $teacher['email']
                                    );

                                    ?>

                                </td>


                                <!-- PHONE -->

                                <td>

                                    <?php

                                    echo !empty(
                                        $teacher['phone']
                                    )

                                        ? htmlspecialchars(
                                            $teacher['phone']
                                        )

                                        : "Not set";

                                    ?>

                                </td>


                                <!-- DEPARTMENT -->

                                <td>

                                    <?php

                                    echo !empty(
                                        $teacher['department_name']
                                    )

                                        ? htmlspecialchars(
                                            $teacher['department_name']
                                        )

                                        : "Not set";

                                    ?>

                                </td>


                                <!-- ACTIONS -->

                                <td>


                                    <a
                                        href="edit_teacher.php?id=<?php echo $teacher['id']; ?>"
                                        class="btn btn-sm btn-warning"
                                    >
                                        Edit
                                    </a>


                                    <a
                                        href="teachers.php?delete=<?php echo $teacher['id']; ?>"
                                        class="btn btn-sm btn-danger"
                                        onclick="return confirm('Are you sure you want to delete this teacher?');"
                                    >
                                        Delete
                                    </a>


                                </td>


                            </tr>


                        <?php endwhile; ?>


                    <?php else: ?>


                        <tr>

                            <td
                                colspan="7"
                                class="text-center"
                            >

                                No teachers found.

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
