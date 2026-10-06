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

// Check student ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: students.php");
    exit;
}

$id = intval($_GET['id']);

// Get student data
$stmt = mysqli_prepare($conn, "
    SELECT
        id,
        full_name,
        email,
        phone,
        address,
        student_id,
        semester,
        department_id
    FROM users
    WHERE id = ? AND role = 'student'
");

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);
$student = mysqli_fetch_assoc($result);

if (!$student) {
    die("Student not found.");
}

$message = "";
$error = "";

// Update student
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $full_name = trim($_POST['full_name']);
    $student_id = trim($_POST['student_id']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $department_id = intval($_POST['department_id']);
    $semester = intval($_POST['semester']);
    $password = $_POST['password'];

    // Basic validation
    if (
        empty($full_name) ||
        empty($student_id) ||
        empty($email)
    ) {

        $error = "Full Name, Student ID and Email are required.";

    } else {

        // Check duplicate email
        $check_email = mysqli_prepare($conn, "
            SELECT id
            FROM users
            WHERE email = ? AND id != ?
        ");

        mysqli_stmt_bind_param(
            $check_email,
            "si",
            $email,
            $id
        );

        mysqli_stmt_execute($check_email);
        $email_result = mysqli_stmt_get_result($check_email);

        // Check duplicate student ID
        $check_student = mysqli_prepare($conn, "
            SELECT id
            FROM users
            WHERE student_id = ? AND id != ?
        ");

        mysqli_stmt_bind_param(
            $check_student,
            "si",
            $student_id,
            $id
        );

        mysqli_stmt_execute($check_student);
        $student_result = mysqli_stmt_get_result($check_student);

        if (mysqli_num_rows($email_result) > 0) {

            $error = "This email is already being used by another user.";

        } elseif (mysqli_num_rows($student_result) > 0) {

            $error = "This Student ID is already being used.";

        } else {

            // If password is empty, keep old password
            if (empty($password)) {

                $update = mysqli_prepare($conn, "
                    UPDATE users
                    SET
                        full_name = ?,
                        student_id = ?,
                        email = ?,
                        phone = ?,
                        address = ?,
                        department_id = ?,
                        semester = ?
                    WHERE id = ? AND role = 'student'
                ");

                mysqli_stmt_bind_param(
                    $update,
                    "sssssiii",
                    $full_name,
                    $student_id,
                    $email,
                    $phone,
                    $address,
                    $department_id,
                    $semester,
                    $id
                );

            } else {

                // Hash new password
                $hashed_password = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );

                $update = mysqli_prepare($conn, "
                    UPDATE users
                    SET
                        full_name = ?,
                        student_id = ?,
                        email = ?,
                        phone = ?,
                        address = ?,
                        department_id = ?,
                        semester = ?,
                        password = ?
                    WHERE id = ? AND role = 'student'
                ");

                mysqli_stmt_bind_param(
                    $update,
                    "sssssiisi",
                    $full_name,
                    $student_id,
                    $email,
                    $phone,
                    $address,
                    $department_id,
                    $semester,
                    $hashed_password,
                    $id
                );
            }

            if (mysqli_stmt_execute($update)) {

                header("Location: students.php?updated=1");
                exit;

            } else {

                $error = "Failed to update student: " . mysqli_error($conn);
            }
        }
    }

    // Keep entered values if there is an error
    $student['full_name'] = $full_name;
    $student['student_id'] = $student_id;
    $student['email'] = $email;
    $student['phone'] = $phone;
    $student['address'] = $address;
    $student['department_id'] = $department_id;
    $student['semester'] = $semester;
}

// Get departments
$departments = mysqli_query(
    $conn,
    "SELECT id, name FROM departments ORDER BY name ASC"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Student</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body>

<div class="container mt-5">

    <div class="row justify-content-center">

        <div class="col-md-8">

            <div class="card shadow">

                <div class="card-header bg-primary text-white">

                    <h3 class="mb-0">
                        Edit Student
                    </h3>

                </div>

                <div class="card-body">

                    <?php if (!empty($error)): ?>

                        <div class="alert alert-danger">
                            <?php echo htmlspecialchars($error); ?>
                        </div>

                    <?php endif; ?>


                    <form method="POST">


                        <!-- Full Name -->

                        <div class="mb-3">

                            <label class="form-label">
                                Full Name
                            </label>

                            <input
                                type="text"
                                name="full_name"
                                class="form-control"
                                value="<?php echo htmlspecialchars($student['full_name']); ?>"
                                required
                            >

                        </div>


                        <!-- Student ID -->

                        <div class="mb-3">

                            <label class="form-label">
                                Student ID
                            </label>

                            <input
                                type="text"
                                name="student_id"
                                class="form-control"
                                value="<?php echo htmlspecialchars($student['student_id']); ?>"
                                required
                            >

                        </div>


                        <!-- Email -->

                        <div class="mb-3">

                            <label class="form-label">
                                Email
                            </label>

                            <input
                                type="email"
                                name="email"
                                class="form-control"
                                value="<?php echo htmlspecialchars($student['email']); ?>"
                                required
                            >

                        </div>


                        <!-- Phone -->

                        <div class="mb-3">

                            <label class="form-label">
                                Phone
                            </label>

                            <input
                                type="text"
                                name="phone"
                                class="form-control"
                                value="<?php echo htmlspecialchars($student['phone'] ?? ''); ?>"
                            >

                        </div>


                        <!-- Address -->

                        <div class="mb-3">

                            <label class="form-label">
                                Address
                            </label>

                            <textarea
                                name="address"
                                class="form-control"
                                rows="3"
                            ><?php echo htmlspecialchars($student['address'] ?? ''); ?></textarea>

                        </div>


                        <!-- Department -->

                        <div class="mb-3">

                            <label class="form-label">
                                Department / Program
                            </label>

                            <select
                                name="department_id"
                                class="form-select"
                            >

                                <option value="0">
                                    Select Department
                                </option>

                                <?php while ($department = mysqli_fetch_assoc($departments)): ?>

                                    <option
                                        value="<?php echo $department['id']; ?>"
                                        <?php
                                        if (
                                            intval($student['department_id']) ==
                                            intval($department['id'])
                                        ) {
                                            echo "selected";
                                        }
                                        ?>
                                    >

                                        <?php echo htmlspecialchars($department['name']); ?>

                                    </option>

                                <?php endwhile; ?>

                            </select>

                        </div>


                        <!-- Semester -->

                        <div class="mb-3">

                            <label class="form-label">
                                Semester
                            </label>

                            <select
                                name="semester"
                                class="form-select"
                            >

                                <option value="0">
                                    Select Semester
                                </option>

                                <?php for ($i = 1; $i <= 8; $i++): ?>

                                    <option
                                        value="<?php echo $i; ?>"
                                        <?php
                                        if (intval($student['semester']) == $i) {
                                            echo "selected";
                                        }
                                        ?>
                                    >

                                        Semester <?php echo $i; ?>

                                    </option>

                                <?php endfor; ?>

                            </select>

                        </div>


                        <!-- Password -->

                        <div class="mb-3">

                            <label class="form-label">
                                New Password
                            </label>

                            <div class="input-group">

                                <input
                                    type="password"
                                    name="password"
                                    id="studentPassword"
                                    class="form-control"
                                    placeholder="Enter new password"
                                >

                                <button
                                    type="button"
                                    class="btn btn-outline-secondary"
                                    onclick="togglePassword()"
                                >
                                    👁️
                                </button>

                            </div>

                            <small class="text-muted">
                                Leave this empty if you do not want to change the password.
                            </small>

                        </div>


                        <!-- Buttons -->

                        <div class="d-flex gap-2">

                            <button
                                type="submit"
                                class="btn btn-primary"
                            >
                                Update Student
                            </button>

                            <a
                                href="students.php"
                                class="btn btn-secondary"
                            >
                                Cancel
                            </a>

                        </div>


                    </form>

                </div>

            </div>

        </div>

    </div>

</div>


<script>

function togglePassword() {

    const password = document.getElementById("studentPassword");

    if (password.type === "password") {

        password.type = "text";

    } else {

        password.type = "password";

    }

}

</script>

</body>

</html>

