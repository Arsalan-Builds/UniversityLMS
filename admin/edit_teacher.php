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


// =====================================================
// CHECK TEACHER ID
// =====================================================

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: teachers.php");
    exit;
}

$id = intval($_GET['id']);


// =====================================================
// GET TEACHER
// =====================================================

$stmt = mysqli_prepare(
    $conn,
    "SELECT
        id,
        full_name,
        email,
        phone,
        address,
        teacher_id,
        department_id
     FROM users
     WHERE id = ?
     AND role = 'teacher'
     LIMIT 1"
);

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) == 0) {
    die("Teacher not found.");
}

$teacher = mysqli_fetch_assoc($result);

$error = "";


// =====================================================
// UPDATE TEACHER
// =====================================================

if (isset($_POST['update_teacher'])) {

    $full_name = trim($_POST['full_name']);
    $teacher_id = trim($_POST['teacher_id']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $department_id = intval($_POST['department_id']);
    $password = $_POST['password'];


    // =================================================
    // VALIDATION
    // =================================================

    if (
        empty($full_name) ||
        empty($teacher_id) ||
        empty($email) ||
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
             AND id != ?
             LIMIT 1"
        );

        mysqli_stmt_bind_param(
            $stmt,
            "si",
            $email,
            $id
        );

        mysqli_stmt_execute($stmt);

        $email_result = mysqli_stmt_get_result($stmt);


        if (mysqli_num_rows($email_result) > 0) {

            $error = "This email is already used by another account.";

        } else {


            // =================================================
            // CHECK DUPLICATE TEACHER ID
            // =================================================

            $stmt = mysqli_prepare(
                $conn,
                "SELECT id
                 FROM users
                 WHERE teacher_id = ?
                 AND id != ?
                 LIMIT 1"
            );

            mysqli_stmt_bind_param(
                $stmt,
                "si",
                $teacher_id,
                $id
            );

            mysqli_stmt_execute($stmt);

            $teacher_id_result = mysqli_stmt_get_result($stmt);


            if (mysqli_num_rows($teacher_id_result) > 0) {

                $error = "This Teacher ID already belongs to another teacher.";

            } else {


                // =================================================
                // UPDATE WITHOUT PASSWORD
                // =================================================

                if (empty($password)) {

                    $stmt = mysqli_prepare(
                        $conn,
                        "UPDATE users SET
                            full_name = ?,
                            teacher_id = ?,
                            email = ?,
                            phone = ?,
                            address = ?,
                            department_id = ?
                         WHERE id = ?
                         AND role = 'teacher'"
                    );

                    mysqli_stmt_bind_param(
                        $stmt,
                        "ssssssi",
                        $full_name,
                        $teacher_id,
                        $email,
                        $phone,
                        $address,
                        $department_id,
                        $id
                    );


                } else {


                    // =================================================
                    // UPDATE WITH PASSWORD
                    // =================================================

                    $hashed_password = password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );

                    $stmt = mysqli_prepare(
                        $conn,
                        "UPDATE users SET
                            full_name = ?,
                            teacher_id = ?,
                            email = ?,
                            phone = ?,
                            address = ?,
                            department_id = ?,
                            password = ?
                         WHERE id = ?
                         AND role = 'teacher'"
                    );

                    mysqli_stmt_bind_param(
                        $stmt,
                        "ssssisis",
                        $full_name,
                        $teacher_id,
                        $email,
                        $phone,
                        $address,
                        $department_id,
                        $hashed_password,
                        $id
                    );
                }


                // =================================================
                // EXECUTE UPDATE
                // =================================================

                if (mysqli_stmt_execute($stmt)) {

                    header("Location: teachers.php");
                    exit;

                } else {

                    $error = "Update failed: " . mysqli_error($conn);
                }
            }
        }
    }


    // Keep entered values if there is an error

    $teacher['full_name'] = $full_name;
    $teacher['teacher_id'] = $teacher_id;
    $teacher['email'] = $email;
    $teacher['phone'] = $phone;
    $teacher['address'] = $address;
    $teacher['department_id'] = $department_id;
}


// =====================================================
// GET DEPARTMENTS
// =====================================================

$departments = mysqli_query(
    $conn,
    "SELECT id, name
     FROM departments
     ORDER BY name ASC"
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

    <title>Edit Teacher</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body>


<div class="container py-5">


    <!-- =================================================
         HEADER
    ================================================== -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2>Edit Teacher</h2>

            <p class="text-muted mb-0">
                Update teacher information.
            </p>

        </div>


        <a
            href="teachers.php"
            class="btn btn-secondary"
        >
            Back to Teachers
        </a>

    </div>


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
         EDIT FORM
    ================================================== -->

    <div class="card shadow-sm">


        <div class="card-header">

            <h4 class="mb-0">
                Teacher Information
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
                            value="<?php echo htmlspecialchars($teacher['full_name']); ?>"
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
                            value="<?php echo htmlspecialchars($teacher['teacher_id'] ?? ''); ?>"
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
                            value="<?php echo htmlspecialchars($teacher['email']); ?>"
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
                            value="<?php echo htmlspecialchars($teacher['phone'] ?? ''); ?>"
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
                            value="<?php echo htmlspecialchars($teacher['address'] ?? ''); ?>"
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
                                    <?php
                                    if (
                                        intval($teacher['department_id']) ==
                                        intval($department['id'])
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


                    <!-- NEW PASSWORD -->

                    <div class="col-md-6">

                        <label class="form-label">
                            New Password
                        </label>

                        <input
                            type="password"
                            name="password"
                            class="form-control"
                            placeholder="Leave blank to keep current password"
                        >

                        <small class="text-muted">
                            Leave blank if you do not want to change the password.
                        </small>

                    </div>


                </div>


                <!-- BUTTONS -->

                <div class="mt-4">

                    <button
                        type="submit"
                        name="update_teacher"
                        class="btn btn-success"
                    >
                        Update Teacher
                    </button>


                    <a
                        href="teachers.php"
                        class="btn btn-secondary"
                    >
                        Cancel
                    </a>

                </div>


            </form>


        </div>

    </div>


</div>


</body>

</html>
